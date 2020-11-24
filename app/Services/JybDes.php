<?php

namespace App\Services;

class JybDes
{
    function hexToBytes($str)
    {
        $chars = str_split($str);
        $length = 8;
        $bytes = array();
        for ($i = 0; $i < $length; $i++) {
            $high = $chars[$i * 2];
            $low = $chars[$i * 2 + 1];
            if ($high !== "0") {
                $high = base_convert($high, 16, 10);
                $high = ($high == 0) ? '-1' : $high;
            }

            if ($low !== "0") {
                $low = base_convert($low, 16, 10);
                $low = ($low == 0) ? '-1' : $low;
            }
            $value = ($high << 4) | $low;
            if ($value > 127) {
                $value -= 256;
            }
            $bytes[$i] = $value;
        }
        return $bytes;
    }

    function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }

    function encrypt($input, $key) //加密
    {
        $data = openssl_encrypt($input, 'des-ecb', $this->toStr($this->hexToBytes($key)), OPENSSL_RAW_DATA);
        $data = bin2hex($data);
        return strtoupper($data);
    }

    function decrypt($str, $key) //解密
    {
        return openssl_decrypt(hex2bin($str), 'des-ecb', $this->toStr($this->hexToBytes($key)), OPENSSL_RAW_DATA);
    }


}
