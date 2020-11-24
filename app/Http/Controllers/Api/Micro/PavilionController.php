<?php

namespace App\Http\Controllers\Api\Micro;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wxuser;
use App\Services\OpacSoap;
use App\Services\OpenlibService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;


/**
 * 图书馆相关 Api
 * Class PavilionController
 * @package App\Http\Controllers\Api\Micro
 */
class PavilionController extends Controller
{
    use ApiResponse;

    /**
     * 分馆接口 适配器
     * @param Request $request
     * @return mixed
     */
    public function libSecondaryList(Request $request)
    {
        $token = $request->input('token');
        $wxuser = Wxuser::getCache($token);
        switch ($wxuser['auth_type']) {
            case 1:
                //openlib
                $openlibService = OpenlibService::make($token, $wxuser);
                $response = $openlibService->getlibSecondaryList();
                if ($response['success'] == false) {
                    return $this->failed(Arr::get($response, 'messagelist.0.message'), 400);
                }
                return $this->success($response['pagedata'], true);
                break;
            case 2:
                //opac
                $opacSoap = OpacSoap::make(['token' => $token, 'opacurl' => $wxuser['opacurl']], 'webservice/loanWebservice');
                $libResponse = $opacSoap->requestFunction('getLibcodeList');
                if (!Arr::has($libResponse, 'errMes')) {                        //...异常处理
                    $success = [];
                    $libResponse = $libResponse['return'];
                    foreach ($libResponse as $k => $v) {
                        $success[] = [
                            'name' => $v['name'],
                            'simpleName' => $v['name'],
                            'libcode' => $v['libcode'],
                            'address' => ""
                        ];
                    }
                    return $this->success($success, true);
                }
                return $this->failed('opac分馆接口异常 ', 400);
                break;
        }
    }
}
