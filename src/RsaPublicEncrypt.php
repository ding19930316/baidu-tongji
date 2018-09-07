<?php

namespace Qqjt\BaiduTongji;


class RsaPublicEncrypt
{
    /**
     * @var string
     */
    private $publicKey;

    /**
     * RsaPublicEncrypt constructor.
     * @param $publicKey
     */
    public function __construct($publicKey)
    {
        $this->publicKey = $publicKey;
    }

    /**
     * pub encrypt
     * @param string $data
     * @return string
     */
    public function pubEncrypt($data)
    {
        if (!is_string($data)) {
            return null;
        }
        $ret = openssl_public_encrypt($data, $encrypted, $this->publicKey);
        if ($ret) {
            return $encrypted;
        } else {
            return null;
        }
    }
}

if (!function_exists('gzdecode')) {
    /**
     * gzdecode
     * @param string $data
     * @return string
     */
    function gzdecode($data)
    {
        $flags = ord(substr($data, 3, 1));
        $headerlen = 10;
        if ($flags & 4) {
            $extralen = unpack('v', substr($data, 10, 2));
            $extralen = $extralen[1];
            $headerlen += 2 + $extralen;
        }
        if ($flags & 8) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        }
        if ($flags & 16) {
            $headerlen = strpos($data, chr(0), $headerlen) + 1;
        }
        if ($flags & 2) {
            $headerlen += 2;
        }
        $unpacked = @gzinflate(substr($data, $headerlen));
        if ($unpacked === false) {
            $unpacked = $data;
        }
        return $unpacked;
    }
}