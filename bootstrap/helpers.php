<?php


if (! function_exists('materialUrl')) {

    function materialUrl()
    {
        return (session('wxtoken')) ? 'wechat/' . session('wxtoken') : null;
    }
}

if (! function_exists('hidenIdCard')) {

    function hidenIdCard($idCard)
    {
        return preg_replace('/^(.{6})(?:\d+)(.{2})$/', "$1**********$2", $idCard);
    }
}