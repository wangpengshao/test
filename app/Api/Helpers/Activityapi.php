<?php

namespace App\Api\Helpers;

use App\Models\Wxuser;
use GuzzleHttp\Client;

trait Activityapi
{

    protected $activityUrl;

    public function initActivityConfig($token)
    {
        $wxuser = Wxuser::getCache($token);
        $this->activityUrl = $wxuser['activity_url'];
    }

    public function activityHttpMagic($token, $param, $url)
    {
        $this->initActivityConfig($token);
        $url = $this->activityHttpUrl($param, $url);
        $http = new Client();
        $response = $http->get($url);
        return json_decode((string)$response->getBody(), true);
    }

    public function activityHttpUrl($data = [], $path)
    {
        return $this->activityUrl . $path . '?' . http_build_query($data);
    }

    public function fullPic($token, $url)
    {
        if (empty($url)) {
            return 'https://wechat-xin.oss-cn-shenzhen.aliyuncs.com/images/nopicture.jpg';
        }
        $this->initActivityConfig($token);
        $isURL = filter_var($url, FILTER_VALIDATE_URL);
        if ($isURL) {
            return $url;
        }
        return $this->activityUrl . $url;
    }


}
