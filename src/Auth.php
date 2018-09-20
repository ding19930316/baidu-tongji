<?php

namespace Qqjt\BaiduTongji;


class Auth
{
    private $config;

    private $headers;

    public $ucid = null;

    public $st = null;

    const LOGIN_URL = 'https://api.baidu.com/sem/common/HolmesLoginService';

    const PUBLIC_KEY = <<<publicKey
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDHn/hfvTLRXViBXTmBhNYEIJeG
GGDkmrYBxCRelriLEYEcrwWrzp0au9nEISpjMlXeEW4+T82bCM22+JUXZpIga5qd
BrPkjU08Ktf5n7Nsd7n9ZeI0YoAKCub3ulVExcxGeS3RVxFai9ozERlavpoTOdUz
EH6YWHP4reFfpMpLzwIDAQAB
-----END PUBLIC KEY-----
publicKey;


    public function __construct($config)
    {
        $this->config = $config;
        $this->headers = [
            'UUID: ' . $this->uuid,
            'account_type: ' . $this->account_type,
            'Content-Type:  data/gzencode and rsa public encrypt;charset=UTF-8'
        ];
    }

    public function __get($name)
    {
        return isset($this->config[$name]) ? $this->config[$name] : null;
    }

    /**
     * @param $data
     * @return null
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $ret = openssl_public_encrypt($data, $encrypted, self::PUBLIC_KEY);
        if ($ret) {
            return $encrypted;
        } else {
            return null;
        }
    }

    /**
     * @param $data
     * @return string
     *
     * generate post data
     */
    private function genPostData($data)
    {
        $gzData = gzencode(json_encode($data), 9);
        for ($index = 0, $enData = ''; $index < strlen($gzData); $index += 117) {
            $gzPackData = substr($gzData, $index, 117);
            $enData .= $this->pubEncrypt($gzPackData);
        }
        return $enData;
    }

    private function post($url, $data)
    {
        $data = $this->genPostData($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            //echo '[error] CURL ERROR: ' . curl_error($curl) . PHP_EOL;
        }
        curl_close($curl);

        $res['code'] = ord($tmpInfo[0]) * 64 + ord($tmpInfo[1]);

        if ($res['code'] === 0) {
            $res['data'] = substr($tmpInfo, 8);
        }

        return $res;
    }

    private function preLogin()
    {
        $preLoginData = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'preLogin',
            'uuid' => $this->uuid,
            'request' => [
                'osVersion' => 'windows',
                'deviceType' => 'pc',
                'clientVersion' => '1.0',
            ],
        ];

        $res = $this->post(self::LOGIN_URL, $preLoginData);

        if ($res['code'] === 0) {
            $retData = gzdecode($res['data']);
            $retArray = json_decode($retData, true);
            if (!isset($retArray['needAuthCode']) || $retArray['needAuthCode'] === true) {
                //echo "[error] preLogin return data format error: {$retData}" . PHP_EOL;
                //echo '--------------------preLogin End--------------------' . PHP_EOL;
                return false;
            } else if ($retArray['needAuthCode'] === false) {
                return true;
            } else {
                //echo "[error] unexpected preLogin return data: {$retData}" . PHP_EOL;
                //echo '--------------------preLogin End--------------------' . PHP_EOL;
                return false;
            }
        } else {
            //echo "[error] preLogin unsuccessfully with return code: {$res['code']}" . PHP_EOL;
            //echo '--------------------preLogin End--------------------' . PHP_EOL;
            return false;
        }

    }


    public function login()
    {
        $this->preLogin();

        $loginData = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'doLogin',
            'uuid' => $this->uuid,
            'request' => [
                'password' => $this->password,
            ],
        ];
        $res = $this->post(self::LOGIN_URL, $loginData);

        if ($res['code'] === 0) {
            $retData = gzdecode($res['data']);
            $retArray = json_decode($retData, true);
            if (!isset($retArray['retcode']) || !isset($retArray['ucid']) || !isset($retArray['st'])) {
                //echo "[error] doLogin return data format error: {$retData}" . PHP_EOL;
                //echo '--------------------doLogin End--------------------' . PHP_EOL;
                return null;
            } else if ($retArray['retcode'] === 0) {
                $this->ucid = $retArray['ucid'];
                $this->st = $retArray['st'];
                return [
                    'ucid' => $retArray['ucid'],
                    'st' => $retArray['st'],
                ];
            } else {
                //echo "[error] doLogin unsuccessfully with retcode: {$retArray['retcode']}" . PHP_EOL;
                //echo '--------------------doLogin End--------------------' . PHP_EOL;
                return null;
            }
        } else {
            //echo "[error] doLogin unsuccessfully with return code: {$res['code']}" . PHP_EOL;
            //echo '--------------------doLogin End--------------------' . PHP_EOL;
            return null;
        }
    }

    public function logout()
    {
        if ($this->ucid == null && $this->st == null)
            return null;
        $logoutData = [
            'username' => $this->username,
            'token' => $this->token,
            'functionName' => 'doLogout',
            'uuid' => $this->uuid,
            'request' => [
                'ucid' => $this->ucid,
                'st' => $this->st,
            ],
        ];
        $res = $this->post(self::LOGIN_URL, $logoutData);

        if ($res['code'] === 0) {
            $retData = gzdecode($res['data']);
            $retArray = json_decode($retData, true);
            if (!isset($retArray['retcode'])) {
                //echo "[error] doLogout return data format error: {$retData}" . PHP_EOL;
                //echo '--------------------doLogout End--------------------' . PHP_EOL;
                return false;
            } else if ($retArray['retcode'] === 0) {
                //echo '[notice] doLogout successfully!' . PHP_EOL;
                //echo '--------------------doLogout End--------------------' . PHP_EOL;
                return true;
            } else {
                //echo "[error] doLogout unsuccessfully with retcode: {$retArray['retcode']}" . PHP_EOL;
                //echo '--------------------doLogout End--------------------' . PHP_EOL;
                return false;
            }
        } else {
            //echo "[error] doLogout unsuccessfully with return code: {$res['code']}" . PHP_EOL;
            //echo '--------------------doLogout End--------------------' . PHP_EOL;
            return false;
        }
    }
}