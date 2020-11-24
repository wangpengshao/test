<?php

namespace App\Admin\Controllers\Wechat;

use App\Api\Helpers\ApiResponse;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\Menu;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Replycontent;
use App\Models\Wxuser;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DataMigrateController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $token = $request->session()->get('wxtoken');
        $wxuser = Wxuser::getCache($token);
        $bindNumber = Reader::where('token', $token)->count();
        $textNumber = Replycontent::where('token', $token)->where('type', 0)->count();
        $imgNumber = Replycontent::where('token', $token)->where('type', 1)->count();
        $diyMenusNumber = Menu::where('token', $token)->count();
        $indexMenusNumber = IndexMenu::where('token', $token)->count();
        $current = [
            'bindNumber' => $bindNumber,
            'textNumber' => $textNumber,
            'imgNumber' => $imgNumber,
            'diyMenusNumber' => $diyMenusNumber,
            'indexMenusNumber' => $indexMenusNumber
        ];
//        dd($current);

        return Admin::content(function (Content $content) use ($wxuser, $current) {
            $content->header('数据迁移');
            $content->description('...');
            $content->body(view('admin.diy.dataM', [
                'wxuser' => $wxuser,
                'checkUrl' => 'https://u.interlib.cn/index.php?g=Mysql&m=UweiMigrate&a=check&sign=K3jaPSDPoYrjLP3&token=',
                'current' => $current,
                'upUrl' => route('data.migration.up')
            ]));

        });
    }

    public function update(Request $request)
    {
        $token = $request->session()->get('wxtoken');
        $yToken = $request->input('yToken');
        $type = $request->input('type');
        if (!$request->filled(['yToken', 'type'])) {
            return $this->failed('非法访问');
        }
        switch ($type) {
            case 'bindReader':
                $this->migrate($token, $yToken);
                $number = DB::table('admin_wechat_reader')->where('token', $token)->count();
                return $this->success(['message' => '迁移完成', 'number' => $number, 'classType' => '.bindTr'], true);
                break;

            case 'text':
                $this->requestTextReply($token, $yToken);
                $number = Replycontent::where('token',$token)->where('type',0)->count();
                return $this->success(['message' => '迁移完成', 'number' => $number, 'classType' => '.textTr'], true);
                break;

            case  'img':
                $this->requestImgReply($token, $yToken);
                $number = Replycontent::where('token',$token)->where('type',1)->count();
                return $this->success(['message' => '迁移完成', 'number' => $number, 'classType' => '.imgTr'], true);
                break;

            case 'diyMenus':
                $this->requestDiyMenus($token, $yToken);
                $number = Menu::where('token', $token)->count();
                return $this->success(['message' => '迁移完成', 'number' => $number, 'classType' => '.diyMenusTr'], true);
                break;

            case 'indexMenus':
                $this->requestIndexMenus($token, $yToken);
                $number = IndexMenu::where('token', $token)->count();
                return $this->success(['message' => '迁移完成', 'number' => $number, 'classType' => '.indexMenusTr'], true);
                break;
            default:
        }
    }

    protected function dataGroup(array $dataArr, string $keyStr): array
    {
        $newArr = [];
        foreach ($dataArr as $k => $val) {
            $newArr[$val[$keyStr]][] = $val;
        }
        return $newArr;
    }

    public function requestReader($token, $yToken, $page, $rdidArray)
    {
        $salt = '#489@3!66';
        $url = 'https://u.interlib.cn?';
        $time = time();
        $page = $page;
        $rows = 1000;
        $params = http_build_query([
            'g' => 'Mysql',
            'm' => 'Apidata',
            'a' => 'getReaderByToken',
            'token' => $yToken,
            'time' => time(),
            'rows' => $rows,
            'sign' => md5($yToken . '_' . $salt . '_' . $time . '_' . $page . '_' . $rows),
            'page' => $page,
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        $list = $response['list'];
        $un = $this->dataGroup($list, 'rdid');
        $news = [];
        foreach ($un as $k => $v) {
            if (!in_array($v[0]['rdid'], $rdidArray)) {
                $news[] = [
                    'token' => $token,
                    'openid' => $v[0]['openid'],
                    'rdid' => $v[0]['rdid'],
                    'password' => encrypt('111111'),
                    'created_at' => $v[0]['createDate'],
                    'updated_at' => date('Y-m-d H:i:s'),
                    'is_bind' => 1,
                    'name' => $v[0]['name'],
                ];
            }

        }
        DB::table('admin_wechat_reader')->insert($news);
//        $status = DB::table('admin_wechat_reader_bk')->insert($news);
        $listLength = count($list);
        return $listLength;
    }

    public function migrate($token, $yToken)
    {
        $rdidArray = DB::table('admin_wechat_reader')->where('token', $token)->pluck('rdid')->toArray();

        $Wxuser = Wxuser::getCache($token);
        if (empty($Wxuser)) abort(500, 'invalid token');
        $page = 1;
        do {
            $listLength = $this->requestReader($token, $yToken, $page, $rdidArray);
            $page++;
        } while ($listLength == 1000);
    }

    protected function requestDiyMenus($token, $yToken)
    {
        $salt = '#489@3!66';
        $url = 'https://u.interlib.cn?';
        $time = time();
        $params = http_build_query([
            'g' => 'Mysql',
            'm' => 'Apidata',
            'a' => 'getDiyMenusByToken',
            'token' => $yToken,
            'time' => time(),
            'sign' => md5($yToken . '_' . $salt . '_' . $time),
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        $list = $response['list'];
        $child = [];
        $pid = null;
        foreach ($list as $k => $v){
            $conversion = $this->menuTypeConversion($v);
            $temp = [
                'order' => $v['sort'],
                'title' => $v['title'],
                'type' => $conversion['type'],
                'data' => $conversion['data'],
                'token' => $token,
                'status' => $v['is_show'] ? 1 : 0,
            ];
            if($v['pid'] == 0){
                $temp['parent_id'] = 0;
                $pid[$v['id']] = Menu::insertGetId($temp);
                unset($temp);
            }else{
                $temp['parent_id'] = $pid[$v['pid']];
                $child[] = $temp;
            }
        }
        Menu::insert($child);
    }

    protected function requestTextReply($token, $yToken)
    {
        $salt = '#489@3!66';
        $url = 'https://u.interlib.cn?';
        $time = time();
        $params = http_build_query([
            'g' => 'Mysql',
            'm' => 'Apidata',
            'a' => 'getTextReplyByToken',
            'token' => $yToken,
            'time' => time(),
            'sign' => md5($yToken . '_' . $salt . '_' . $time),
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        $list = $response['list'];

        foreach ($list as $k => $v){
            $model = new Replycontent;
            $model->type        = 0;
            $model->user_id     = 0;
            $model->keyword     = $v['keyword'];
            $model->token       = $token;
            $model->content     = $v['text'];
            $model->matchtype   = $v['precisions'];
            $model->views       = $v['click'];
            $model->title       = '我是标题';
            $model->save();
            unset($list[$k],$k,$v);
        }
    }

    protected function requestImgReply($token, $yToken)
    {
        $salt = '#489@3!66';
        $url = 'https://u.interlib.cn?';
        $time = time();
        $params = http_build_query([
            'g' => 'Mysql',
            'm' => 'Apidata',
            'a' => 'getImgReplyByToken',
            'token' => $yToken,
            'time' => time(),
            'sign' => md5($yToken . '_' . $salt . '_' . $time),
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        $list = $response['list'];

        foreach ($list as $k => $v){
            $model = new Replycontent;
            $model->type        = 1;
            $model->user_id     = 0;
            $model->keyword     = $v['keyword'];
            $model->token       = $token;
            $model->content     = $v['info'];
            $model->matchtype   = $v['precisions'];
            $model->image       = $v['pic'];
            $model->views       = $v['click'];
            $model->url         = $v['url'];
            $model->title       = $v['title'] ? $v['title'] : '我是标题';
            $model->save();
            unset($list[$k],$k,$v);
        }
    }

    protected function requestIndexMenus($token, $yToken)
    {
        $salt = '#489@3!66';
        $url = 'https://u.interlib.cn?';
        $time = time();
        $params = http_build_query([
            'g' => 'Mysql',
            'm' => 'Apidata',
            'a' => 'getClassifyByToken',
            'token' => $yToken,
            'time' => time(),
            'sign' => md5($yToken . '_' . $salt . '_' . $time),
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        $list = $response['list'];

        foreach ($list as $k => $v){
            $model = new IndexMenu;
            $model->caption = $v['name'];
            $model->status  = $v['status'];
            $model->token   = $token;
            $model->icon    = $v['img'];
            $model->url     = $v['url'];
            $model->save();
            unset($list[$k],$k,$v);
        }

    }

    private function menuTypeConversion($data){
        if($data['keyword'] != ''){
            $res['type'] = 1; // 关键字回复
            $res['data'] = $data['keyword'];
        }elseif($data['url'] != ''){
            $res['type'] = 2; // url链接
            $res['data'] = $data['url'];
        }elseif($data['wxsys'] != ''){
            $res['type'] = 3; // 扫码菜单
            $res['data'] = $data['title'];
        }elseif($data['appid'] != ''){
            $res['type'] = 4; //小程序
            $res['data'] = $data['appid'] . '\r' . $data['mini_url'] . '\r' . $data['pagepath'];
        }else{
            $res['type'] = 2;
            $res['data'] = '';
        }
        return $res;
    }
}
