<?php

namespace App\Admin\Extensions\Tools;

class PassWord
{
    public static function checkWeak($password, $rank, $mindigit = '')
    {
        // 无传参值直接返回false |  如果密码类型值为1时，即为默认不限制，直接返回false
        if (!$password || !$rank || $rank == 1) {
            return false;
        }
        // 密码最小位数，若不存在则默认为6位
        $min = !empty($mindigit) ? $mindigit : 6;
        // 判断传入的密码位数是否小于设定值
        if (mb_strlen($password) < $min) {
            return true;
        }
        // 弱密码类型选择组
        $options = [
            1 => '/^[0-9]*$/',    // 密码为纯数字
            2 => '/^[a-zA-Z]+$/',   // 纯字母
            3 => '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9a-zA-Z]+$/',    // 数字+字母
            4 => '/^[0-9a-zA-Z]+$/',    // 数字|字母,
            5 => '/^(?=.*\d)(?=.*[a-zA-Z])(?=.*[$@$!%*#?&])[0-9A-Za-z\d$@$!%*#?&]+$/',    // 数字+字母+特殊符号
        ];
        // 根据rank参数匹配弱密码选择组并进行相应的正则匹配,最终返回boolean值。
        $res = false; // 定义匹配结果初始值
        // 因为类型4的情况包含了1，2，3。作特殊处理,匹配4选项
        if ($rank == 5) {
            $res = preg_match($options['4'], $password);
            if ($res) {
                return false;
            }
        }
        // 类型6的强度为最强，只有成功匹配5后，直接返回false，不然都是返回true
        if ($rank == 6) {
            $res = preg_match($options['5'], $password);
            if ($res) {
                return false;
            } else {
                return true;
            }
        }
        // 剩下为普通情况进行正常匹配
        foreach ($options as $k => $v) {
            if ($rank - 1 > $k && $rank != 5) {
                $res = preg_match($v, $password);
                if ($res) {
                    break;  // 找到则终止循环
                }
            }
        }
        // 返回匹配结果
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
}
