<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;


/**
 * 封面工具类
 * Class CoverService
 * @package App\Services
 */
class CoverService
{

    /**
     * 检索封面,单个,多个
     * @param $isbnImg
     * @return array|mixed|string
     */
    public static function search($isbnImg)
    {
        $isbns = $isbnImg;
        if (is_array($isbnImg)) {
            //判断isbn是否有效 转13位
            foreach ($isbnImg as $k => $v) {
                $isbnImg[$k] = self::to13($v);
            }
            $isbnImg = array_filter($isbnImg);      //除空
            //空数组的时候返回空数组
            if (count($isbnImg) == 0) {
                return [];
            }
            $isbnImg = array_unique($isbnImg);      //去重
            $isbns = implode(',', $isbnImg);        //拼接

        } else {
            $isbns = self::to13($isbns);
            if ($isbns == null) {
                return '';
            }
        }

        $params = http_build_query([
            'glc' => '',
            'cmdACT' => 'getImages',
            'type' => 0,
        ]);
        //旧版封面接口
//        $url = 'https://api.interlib.com.cn/interlibopac/websearch/metares?' . $params . '&isbns=' . $isbns;
        //新版封面接口
        $url = 'https://book-resource.dataesb.com/websearch/metares?' . $params . '&isbns=' . $isbns;
        $http = new Client();
        $response = $http->get($url);
        $response = (string)$response->getBody();
        $response = Str::replaceFirst('(', '', $response);
        $response = Str::replaceLast(')', '', $response);
        $response = Arr::get(json_decode($response, true), 'result');

        if (is_string($isbnImg)) {
            return Arr::get($response, '0.coverlink', '');
        }
        $replyData = [];
        if ($response) {
            foreach ($response as $k => $v) {
                foreach ($isbnImg as $key => $val) {
                    if ($v['isbn'] == $val) {
                        $replyData[$key] = $v['coverlink'];
                    }
                }
                unset($key, $val);
            }
            unset($k, $v);
        }
        return $replyData;
    }


    /**
     * 书目详情
     * @param $isbn
     * @return array|mixed
     */
    public static function bookInfo($isbn)
    {
        $isbn = self::to13($isbn);
        if ($isbn) {
            $url = 'https://book-resource.dataesb.com/api/book/isbn/' . $isbn;
            $http = new Client();
            $response = $http->get($url);
            return json_decode((string)$response->getBody(), true);
        }
        return [];
    }


    /**
     * 书目详情lv2
     * @param $isbn
     * @return array|mixed
     */
    public static function bookInfoLv2($isbn)
    {
        $infoIsbn = self::to13($isbn);
        if ($infoIsbn == null) {
            return [];
        }
        $url = 'https://book-resource.dataesb.com/book/searchbook?page=1&size=10&keywords=' . $infoIsbn;
        $http = new Client();
        $response = $http->get($url);
        $response = json_decode((string)$response->getBody(), true);
        if ($response['success'] == false) {
            return [];
        }
        return Arr::get($response, 'data.result.0', []);
    }


    /**
     * isbn 13 位 转 10 位
     * @param $isbn
     * @return mixed|string|null
     */
    protected static function to10($isbn)
    {
        $isbn = str_replace('-', '', $isbn);
        if (!preg_match('/^\d+x?$/i', $isbn)) {
            return null;
        }
        if (strlen($isbn) == 10) {
            return $isbn;
        }
        $sum = 0;
        $num = substr($isbn, 3, 9);
        for ($i = 10, $p = 0; $i > 1; $i--, $p++) {
            $sum += $i * intval($num[$p]);
        }
        $m = $sum % 11;
        $check = 11 - $m;
        if ($check == 10) {
            $check = 'x';
        }
        if ($check == 11) {
            $check = '0';
        }
        return $num . $check;
    }


    /**
     * isbn 10 位 转 13 位
     * @param $isbn
     * @return mixed|string|null
     */
    protected static function to13($isbn)
    {
        $isbn = str_replace('-', '', $isbn);
        //是否是isbn格式
        if (!preg_match('/^\d+x?$/i', $isbn)) {
            return null;
        }
        $strlen = strlen($isbn);
        if ($strlen == 13) {
            return $isbn;
        }
        //是否长度符合10位 无效isbn
        if ($strlen != 10) {
            return null;
        }
        $sum = 0;
        $num = '978' . substr($isbn, 0, 9);
        for ($i = 0; $i < 12; $i++) {
            $n = $num[$i];
            if (($i + 1) % 2 == 0) {
                $sum += $n * 3;
            } else {
                $sum += $n;
            }
        }
        $m = $sum % 10;
        $check = 10 - $m;
        return $num . $check;
    }

}
