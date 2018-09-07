<?php

namespace Qqjt\BaiduTongji;


class Report
{
    /**
     * @var string
     */
    private $token;

    private $accountType;

    private $username;

    private $uuid;

    private $ucid;

    private $st;

    private $headers;

    const API_URL = 'https://api.baidu.com/json/tongji/v1/ReportService';

    /**
     * Report constructor.
     * @param $accountType
     * @param $username
     * @param $token
     * @param $uuid
     * @param $ucid
     * @param $st
     */
    public function __construct($accountType, $username, $token, $uuid, $ucid, $st)
    {
        $this->accountType = $accountType;
        $this->username = $username;
        $this->token = $token;
        $this->ucid = $ucid;
        $this->uuid = $uuid;
        $this->st = $st;
        $this->headers = [
            'UUID: ' . $uuid,
            'USERID: ' . $ucid,
            'Content-Type:  data/json;charset=UTF-8'
        ];
    }

    private function genPostData($data)
    {
        return json_encode($data);
    }

    private function post($url, $data)
    {
        $data = $this->genPostData($data);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        //curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        //curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $tmpRet = curl_exec($curl);
        if (curl_errno($curl)) {
            echo '[error] CURL ERROR: ' . curl_error($curl) . PHP_EOL;
        }
        curl_close($curl);
        $tmpArray = json_decode($tmpRet, true);
        if (isset($tmpArray['header']) && isset($tmpArray['body'])) {
            return [
                'header' => $tmpArray['header'],
                'body' => $tmpArray['body'],
                'raw' => $tmpRet,
            ];
        } else {
            echo "[error] SERVICE ERROR: {$tmpRet}" . PHP_EOL;
        }
    }

    public function getSiteList()
    {
        $data = [
            'header' => [
                'username' => $this->username,
                'password' => $this->st,
                'token' => $this->token,
                'account_type' => $this->accountType,
            ],
            'body' => null,
        ];

        return $this->post(self::API_URL.'/getSiteList', $data);
    }

    /**
     * @param array $parameters
     * @return array
     */
    public function getData($parameters)
    {
        $data = [
            'header' => array(
                'username' => $this->username,
                'password' => $this->st,
                'token' => $this->token,
                'account_type' => $this->accountType,
            ),
            'body' => $parameters,
        ];
        return $this->post(self::API_URL.'/getData', $data);
    }
}