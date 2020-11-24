<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

//use Laravel\Scout\Searchable;

class Fanscontent extends Model
{
//    use Searchable;

    protected $table = 'admin_wechat_fans_content';

    protected $fillable = ['type', 'openid', 'token', 'content', 'is_reading', 'mediaId', 'thumbMediaId'];

    //是否开启分词
//    public $esquery = true;
    //索引名称
//    public $diyIndexName = 'wechat_fans_content';

//    public $orders=[123123,32123];

    /**
     * type名称
     *
     * @return string
     */
//    public function searchableAs()
//    {
//        return 'fanssend';
//    }

    /**
     * 可搜索的数据索引
     *
     * @return array
     */
//    public function toSearchableArray()
//    {
//        return [
//            'openid' => $this->openid,
//            'token' => $this->token,
//            'created_at' => strtotime($this->created_at)
//        ];
//    }

    public function hasOneWechatinfo()
    {
        return $this->hasOne(Fans::class, 'openid', 'openid');
    }

}
