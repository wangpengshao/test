<?php

namespace App\Admin\Extensions;

use App\Models\Wechat\Wechatapp;
use Illuminate\Support\Facades\Storage;

class MemberCard
{
    private static $instance = null;

    protected $app = null;

    private function __construct()
    {
    }

    /**
     * @param string $token
     * @return MemberCardService|null
     */
    public static function make(string $token)
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        self::$instance = new static();
        return self::$instance->setConfig($token);
    }

    /**
     * @param $token
     * @return $this
     */
    public function setConfig($token)
    {
        $this->app = Wechatapp::initialize($token);
        return $this;
    }

    /**
     * 获取卡券颜色
     * @return mixed
     */
    public function getColors()
    {
        return $this->app->card->colors();
    }

    /**
     * 创建卡券
     * @param $type
     * @param $data
     * @return mixed
     */
    public function create($type, $data)
    {
        $attributes = [
            'base_info' =>[
                "brand_name"=> $data['brand_name'],
                "logo_url"=>$this->uploadImg($data['logo_url']),
				"code_type"=> $data['code_type'],
				"title"=> $data['title'],
				"color"=> $data['color'],
				"notice"=> $data['notice'],
				"description"=> $data['description'],
				"date_info"=> [
                    "type"=> $data['date_info']['type']
				 ],
				"sku"=> [
                    "quantity"=> intval($data['sku']['quantity'])
				],
                'get_limit'=> intval($data['get_limit']),
                "use_custom_code"=> false,
				"can_give_friend"=> false,
            ],
            "supply_bonus"=> false,
			"supply_balance"=> false,
            "prerogative"=> $data['prerogative'],
            'auto_activate' => $data['get_limit'] == 1 ? true : false,
        ];

        //背景图片
        if($data['card_cover']==1 && !empty($data['background_pic_url'])){
            $attributes['background_pic_url'] = $this->uploadImg($data['background_pic_url']);
        }
        //客服电话
        if(!empty($data['service_phone'])){
            $attributes['date_info']['service_phone'] = $data['service_phone'];
        }
        //激活外链，auto_activate为true时，激活外链失效
        if(!empty($data['activate_url'])){
            $attributes['activate_url'] = $data['activate_url'];
        }
        //激活外链——小程序
        if(!empty($data['activate_app_brand_user_name']) && !empty($data['activate_app_brand_pass'])){
            $attributes['activate_app_brand_user_name'] = $data['activate_app_brand_user_name'];
            $attributes['activate_app_brand_pass'] = $data['activate_app_brand_pass'];
        }
        //有效时间
        if(!empty($data['date_info']['begin_timestamp']) && !empty($data['date_info']['end_timestamp'])){
            $attributes['base_info']['date_info']['begin_timestamp'] = strtotime($data['date_info']['begin_timestamp']);
            $attributes['base_info']['date_info']['end_timestamp'] = strtotime($data['date_info']['end_timestamp'])+86400;
        }
        //居中按钮名称
        if(!empty($data['center_title'])){
            $attributes['base_info']['center_title'] = $data['center_title'];
        }
        //居中按钮外链
        if(!empty($data['center_url'])){
            $attributes['base_info']['center_url'] = $data['center_url'];
        }
        //居中按钮外链——小程序
        if(!empty($data['center_app_brand_user_name']) && !empty($data['center_app_brand_pass'])){
            $attributes['base_info']['center_app_brand_user_name'] = $data['center_app_brand_user_name'];
            $attributes['base_info']['center_app_brand_pass'] = $data['center_app_brand_pass'];
        }
        //自定义入口一名称
        if(!empty($data['custom_url_name'])){
            $attributes['base_info']['custom_url_name'] = $data['custom_url_name'];
        }
        //自定义入口一右侧的提示语
        if(!empty($data['custom_url_sub_title'])){
            $attributes['base_info']['custom_url_sub_title'] = $data['custom_url_sub_title'];
        }
        //自定义入口一外链
        if(!empty($data['custom_url'])){
            $attributes['base_info']['custom_url'] = $data['custom_url'];
        }
        //自定义入口一外链——小程序
        if(!empty($data['custom_app_brand_user_name']) && !empty($data['custom_app_brand_pass'])){
            $attributes['base_info']['custom_app_brand_user_name'] = $data['custom_app_brand_user_name'];
            $attributes['base_info']['custom_app_brand_pass'] = $data['custom_app_brand_pass'];
        }
        //自定义入口二名称
        if(!empty($data['promotion_url_name'])){
            $attributes['base_info']['promotion_url_name'] = $data['promotion_url_name'];
        }
        //自定义入口二右侧的提示语
        if(!empty($data['promotion_url_sub_title'])){
            $attributes['base_info']['promotion_url_sub_title'] = $data['promotion_url_sub_title'];
        }
        //自定义入口二外链
        if(!empty($data['promotion_url'])){
            $attributes['base_info']['promotion_url'] = $data['promotion_url'];
        }
        //自定义入口二外链——小程序
        if(!empty($data['promotion_app_brand_user_name']) && !empty($data['promotion_app_brand_pass'])){
            $attributes['base_info']['promotion_app_brand_user_name'] = $data['promotion_app_brand_user_name'];
            $attributes['base_info']['promotion_app_brand_pass'] = $data['promotion_app_brand_pass'];
        }

        $result = $this->app->card->create($type, $attributes);

        return $result;
    }

    /**
     * 更新卡券
     * @param $card_id
     * @param $type
     * @param $data
     * @return mixed
     */
    public function update($card_id,$type, $data)
    {
        $attributes = [
            'base_info' =>[
                "code_type"=> $data['code_type'],
                "title"=> $data['title'],
                "color"=> $data['color'],
                "notice"=> $data['notice'],
                "description"=> $data['description'],
                'get_limit'=> intval($data['get_limit']),
                "can_give_friend"=> false,
            ],
            "supply_bonus"=> false,
            "supply_balance"=> false,
            "prerogative"=> $data['prerogative'],
            'auto_activate' => $data['get_limit'] == 1 ? true : false,
        ];
        //Logo图片
        if(!empty($data['logo_url']) && $data['logo_url'] != $data['old_logUrl']){
            $attributes['base_info']['logo_url'] = $this->uploadImg($data['logo_url']);
        }
        //背景图片
        if($data['card_cover']==1 && !empty($data['background_pic_url']) && $data['background_pic_url'] != $data['old_background_pic_url']){
            $attributes['background_pic_url'] = $this->uploadImg($data['background_pic_url']);
        }
        //客服电话
        if(!empty($data['service_phone'])){
            $attributes['date_info']['service_phone'] = $data['service_phone'];
        }
        //激活外链，auto_activate为true时，激活外链失效
        if(!empty($data['activate_url'])){
            $attributes['activate_url'] = $data['activate_url'];
        }
        //激活外链——小程序
        if(!empty($data['activate_app_brand_user_name']) && !empty($data['activate_app_brand_pass'])){
            $attributes['activate_app_brand_user_name'] = $data['activate_app_brand_user_name'];
            $attributes['activate_app_brand_pass'] = $data['activate_app_brand_pass'];
        }
        //有效时间
        if($data['date_info']['type'] == $data['old_date_info']['type'] && !empty($data['date_info']['begin_timestamp']) && !empty($data['date_info']['end_timestamp'])){
            $attributes['base_info']['date_info']['type'] = $data['date_info']['type'] == 'DATE_TYPE_FIX_TIME_RANGE' ? 1 : $data['date_info']['type'];
            $attributes['base_info']['date_info']['begin_timestamp'] = strtotime($data['date_info']['begin_timestamp']);
            $attributes['base_info']['date_info']['end_timestamp'] = strtotime($data['date_info']['end_timestamp'])+86400;
        }
        //居中按钮名称
        if(!empty($data['center_title'])){
            $attributes['base_info']['center_title'] = $data['center_title'];
        }
        //居中按钮外链
        if(!empty($data['center_url'])){
            $attributes['base_info']['center_url'] = $data['center_url'];
        }
        //居中按钮外链——小程序
        if(!empty($data['center_app_brand_user_name']) && !empty($data['center_app_brand_pass'])){
            $attributes['base_info']['center_app_brand_user_name'] = $data['center_app_brand_user_name'];
            $attributes['base_info']['center_app_brand_pass'] = $data['center_app_brand_pass'];
        }
        //自定义入口一名称
        if(!empty($data['custom_url_name'])){
            $attributes['base_info']['custom_url_name'] = $data['custom_url_name'];
        }
        //自定义入口一右侧的提示语
        if(!empty($data['custom_url_sub_title'])){
            $attributes['base_info']['custom_url_sub_title'] = $data['custom_url_sub_title'];
        }
        //自定义入口一外链
        if(!empty($data['custom_url'])){
            $attributes['base_info']['custom_url'] = $data['custom_url'];
        }
        //自定义入口一外链——小程序
        if(!empty($data['custom_app_brand_user_name']) && !empty($data['custom_app_brand_pass'])){
            $attributes['base_info']['custom_app_brand_user_name'] = $data['custom_app_brand_user_name'];
            $attributes['base_info']['custom_app_brand_pass'] = $data['custom_app_brand_pass'];
        }
        //自定义入口二名称
        if(!empty($data['promotion_url_name'])){
            $attributes['base_info']['promotion_url_name'] = $data['promotion_url_name'];
        }
        //自定义入口二右侧的提示语
        if(!empty($data['promotion_url_sub_title'])){
            $attributes['base_info']['promotion_url_sub_title'] = $data['promotion_url_sub_title'];
        }
        //自定义入口二外链
        if(!empty($data['promotion_url'])){
            $attributes['base_info']['promotion_url'] = $data['promotion_url'];
        }
        //自定义入口二外链——小程序
        if(!empty($data['promotion_app_brand_user_name']) && !empty($data['promotion_app_brand_pass'])){
            $attributes['base_info']['promotion_app_brand_user_name'] = $data['promotion_app_brand_user_name'];
            $attributes['base_info']['promotion_app_brand_pass'] = $data['promotion_app_brand_pass'];
        }
        //库存
        if ($data['sku']['quantity'] != $data['old_sku']['quantity']){
            $different = $data['sku']['quantity'] - $data['old_sku']['quantity'];
            $active = $different > 0 ? 'increase' : 'reduce';
            $this->updateModifystock($card_id, abs($different), $active);
        }

        $result = $this->app->card->update($card_id, $type, $attributes);

        return $result;
    }

    /**
     * 修改库存
     * @param $card_id
     * @param $value
     * @param string $active
     * @return mixed
     */
    public function updateModifystock($card_id,$value,$active='increase')
    {
        if($active == 'increase'){
            $res = $this->app->card->increaseStock($card_id, $value); // 增加库存
        }else{
            $res = $this->app->card->reductStock($card_id, $value); // 减少库存
        }
        return $res;
    }

    /**
     * 卡券详情
     * @param $card_id
     * @return mixed
     */
    public function getCardInfo($card_id)
    {
        return $this->app->card->get($card_id);
    }

    /**
     * 卡券二维码
     * @param $card_id
     * @return mixed
     */
    public function qrcode($card_id, $outer_id = 1)
    {
        $cards = [
            'action_name' => 'QR_CARD',
            'expire_seconds' => 1800,
            'action_info' => [
                'card' => [
                    'card_id' => $card_id,
                    'is_unique_code' => false,
                    'outer_id' => $outer_id,    //场景值，当前约定 1表示会员卡
                ],
            ],
        ];
        $ticket = $this->app->card->createQrCode($cards);

        return $ticket;
    }

    /**
     * 删除卡券
     * @param $card_id
     * @return mixed
     */
    public function delete($card_id)
    {
        return $this->app->card->delete($card_id);
    }

    /**
     * 上传素材图片
     * @param $imgUrl
     * @return mixed
     */
    public function uploadImg($imgUrl)
    {
        $path = substr($imgUrl,strpos($imgUrl, 'uploads'));
        $res = $this->app->material->uploadImage($_SERVER['DOCUMENT_ROOT']. '/' .$path);
        return $res['url'];
    }

    /**
     * 卡券批量下发到用户
     * @param  array $card
     * @return mixed
     */
    public function dispense($card)
    {
        if(count($card) == count($card, 1)){
            $param[] = $card;
        }else{
            $param = $card;
        }
        return $this->app->card->jssdk->assign($param);

    }

    /**
     * Code 解码
     * @param $encryptedCode
     * @return mixed\
     */
    public function decryptCode($encryptedCode)
    {
        return $this->app->card->code->decrypt($encryptedCode);
    }
}