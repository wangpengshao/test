<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Models\specialColumn\RedGevemment;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

/**
 * 红色专题资源接口
 * Class RedGevemmentController
 * @package App\Http\Controllers\Api
 */

class RedGevemmentController extends Controller
{
    use ApiResponse;

    private $domain = 'https://resource.interlib.com.cn:182';

    private $key = 'RedGevemment';

    public function __construct(Request $request)
    {
        $this->middleware('checkRedGevemmentToken');
    }

    /**
     * 音视频资源分类
     * @param Request $request
     * @return mixed
     */
    public function allResClass(Request $request)
    {
        if(!$request->filled(['time', 'sign'])){
            return $this->failed('参数缺失');
        }

        $time = $request->input('time');
        $sign = $request->input('sign');

        if(strlen($time)!= 10 || md5($this->key.$time) != $sign) return $this->failed('签名错误');
        if(time()-$time > 300) return $this->failed('签名过期');

        $cacheKey = 'RedGevemment:getAllResClass';
        $res = Cache::remember($cacheKey, 120, function (){
            $url = $this->domain . '/ubook/resource/getAllResClass';
            $client = new Client();
            $data = $client->get($url);
            $data = json_decode($data->getBody(),true);
            $result = [];
            foreach ($data['data'] as $value){
                if($value['resType'] == 'audio'){
                    $result['audio'][] = $value;
                }
                if($value['resType'] == 'video'){
                    $result['video'][] = $value;
                }
            }
            return $result;
        });

        return $this->setStatusCode(200)->success($res);
    }

    /**
     * 音视频资源列表
     * @param Request $request
     * @return mixed
     */
    public function resdataByClass(Request $request)
    {
        if(!$request->filled(['time', 'sign', 'resClass', 'type', 'page', 'pageSize'])){
            return $this->failed('参数缺失');
        }

        $time = $request->input('time');
        $sign = $request->input('sign');
        $resClass = $request->input('resClass');
        $type = $request->input('type');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize');

        if(strlen($time)!= 10 || md5($this->key.$time) != $sign) return $this->failed('签名错误');
        if(time()-$time > 300) return $this->failed('签名过期');

        $cacheKey = 'RedGevemment:resdataByClass:'.$resClass.':'.$page.':'.$pageSize;
        $res = Cache::remember($cacheKey, 120, function () use ($resClass, $page, $pageSize, $type){

            $url = $this->domain . '/ubook/resource/resdataByClass?';
            $imgUrl = $this->domain . '/ubook/upload/image/';
            $audioUrl = $this->domain . '/ubook/upload/audio/';
            $videoUrl = $this->domain . '/ubook/upload/video/';
            $client = new Client();
            $params = http_build_query([
                'resClass' => $resClass,
                'page' => $page,
                'pageSize' => $pageSize
            ]);

            $data = $client->get($url.$params);
            $data = json_decode($data->getBody(),true);
            $result = [];
            foreach ($data['data'] as $value){
                $coverImg = isset($value['coverImg']) ? $imgUrl.$value['coverImg'] : '';
                $value['coverImg'] = $coverImg;
                if($type == 'audio'){
                    $value['audioUlr'] = $audioUrl.$value['audioNo'].'.mp3';
                    $result['lists'][] = $value;
                    continue;
                }
                if($type == 'video'){
                    $value['videoUlr'] = $videoUrl.$value['videoNo'].'.mp4';
                    $result['lists'][] = $value;
                    continue;
                }
            }
            $result['totalNum'] = $data['totalNum'];
            return $result;
        });

        return $this->setStatusCode(200)->success($res);
    }

    /**
     * 电子书分类
     * @param Request $request
     * @return mixed
     */
    public function getAllEbookCategory(Request $request)
    {
        if(!$request->filled(['time', 'sign'])){
            return $this->failed('参数缺失');
        }

        $time = $request->input('time');
        $sign = $request->input('sign');

        if(strlen($time)!= 10 || md5($this->key.$time) != $sign) return $this->failed('签名错误');
        if(time()-$time > 300) return $this->failed('签名过期');

        $cacheKey = 'RedGevemment:getAllEbookCategory';
        $res = Cache::remember($cacheKey, 120, function (){
            $url = $this->domain . '/ubook/resource/getAllEbookCategory';
            $client = new Client();
            $data = $client->get($url);
            $data = json_decode($data->getBody(),true);
            $result = $data['data'];
            /*foreach ($data['data'] as $value){
                $result[$value['categoryNo']]['categoryName'] = $value['categoryName'];
                if(isset($value['subclassNo'])){
                    $result[$value['categoryNo']]['child'][] = $value;
                }
            }*/
            return $result;
        });

        return $this->setStatusCode(200)->success($res);
    }

    /**
     * 电子书资源列表
     * @param Request $request
     * @return mixed
     */
    public function ebookdata(Request $request)
    {
        if(!$request->filled(['time', 'sign', 'page', 'pageSize', 'subclassNo'])){
            return $this->failed('参数缺失');
        }

        $time = $request->input('time');
        $sign = $request->input('sign');
        $page = $request->input('page');
        $pageSize = $request->input('pageSize');
        $subclassNo = $request->input('subclassNo');

        if(strlen($time)!= 10 || md5($this->key.$time) != $sign) return $this->failed('签名错误');
        if(time()-$time > 300) return $this->failed('签名过期');

        $cacheKey = 'RedGevemment:ebookdata:'.$subclassNo.':'.$page.':'.$pageSize;
        $res = Cache::remember($cacheKey, 120, function () use ($page, $pageSize, $subclassNo){

            $url = $this->domain . '/ubook/resource/ebookdata?';
            $imgUrl = $this->domain . '/ubook/upload/image/';
            $sourceUrl = $this->domain . '/ubook/upload/ebook/';

            $client = new Client();
            $params = http_build_query([
                'page' => $page,
                'pageSize' => $pageSize,
                'subclassNo' => $subclassNo
            ]);
            $data = $client->get($url.$params);
            $data = json_decode($data->getBody(),true);
            $result = [];
            foreach ($data['data'] as $value){
                $coverImg = isset($value['coverImg']) ? $imgUrl.$value['coverImg'] : '';
                $value['coverImg'] = $coverImg;
                $value['sourseUrl'] = $sourceUrl.$value['title'].'.pdf';
                $result['lists'][] = $value;

            }

            $result['totalNum'] = $data['totalNum'];
            return $result;
        });


        return $this->setStatusCode(200)->success($res);
    }

    /**
     * 语录列表
     * @param Request $request
     * @return mixed
     */
    public function anaList(Request $request)
    {
        if(!$request->filled(['time', 'sign', 'page', 'pageSize'])){
            return $this->failed('参数缺失');
        }

        $time = $request->input('time');
        $sign = $request->input('sign');
        $pageSize = $request->input('pageSize');
        if(strlen($time)!= 10 || md5($this->key.$time) != $sign) return $this->failed('签名错误');
        if(time()-$time > 300) return $this->failed('签名过期');

        $res = RedGevemment::where('status', 1)->orderBy('sort')->paginate($pageSize);

        return $this->setStatusCode(200)->success($res);
    }

    /**
     * 音视频精品推荐
     * @param Request $request
     * @return mixed
     */
    public function getRecoRes(Request $request)
    {
        if(!$request->filled(['time', 'sign'])){
            return $this->failed('参数缺失');
        }

        $time = $request->input('time');
        $sign = $request->input('sign');

        if(strlen($time)!= 10 || md5($this->key.$time) != $sign) return $this->failed('签名错误');
        if(time()-$time > 300) return $this->failed('签名过期');

        $cacheKey = 'RedGevemment:getRecoRes';
        $res = Cache::remember($cacheKey, 60, function (){
            $url = $this->domain . '/ubook/resource/getRecoRes';
            $client = new Client();
            $data = $client->get($url);
            $data = json_decode($data->getBody(),true);
            $result = $data['data'];
            return $result;
        });

        return $this->setStatusCode(200)->success($res);
    }

    public function getConfig(Request $request)
    {
        $token = $request->input('token');
        $config = RedGevemment::getCache($token);
        return $this->setStatusCode(200)->success($config);
    }
}
