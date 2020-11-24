<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

/**
 * Class Replycontent
 * @package App\Models\Wechat
 */
class Replycontent extends Model
{
    use Searchable;

    /**
     * @var string
     */
    protected $table = 'admin_wechat_reply_content';

    /**
     * 检索数据数量
     * @var string
     */
    public $searchLimit = 50;

    /**
     * 检索需要分词字段
     * @var string
     */
    public $analyzeField = 'keyword';

    /**
     * 索引名称
     * @return string
     */
    public function searchableAs()
    {
        return 'wechat_keyword';
    }

    /**
     * 插入|查询|更新 字段
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'keyword' => $this->keyword,
            'token' => $this->token
        ];
    }

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getImageAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

}
