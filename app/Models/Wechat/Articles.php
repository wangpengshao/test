<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
//use Spatie\EloquentSortable\Sortable;
//use Spatie\EloquentSortable\SortableTrait;

class Articles extends Model
{
//    use SortableTrait;

    protected $table = 'admin_wechat_articles';

//    public $sortable = [
//        'order_column_name' => 'order',
//    ];

    public function hasOneCategories()
    {
        return $this->hasOne(ArtCategories::class, 'id', 'category_id');
    }

    /**
     * @param $avatar
     *
     * @return string
     */
    public function getPictureAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }
        return '';
    }

    /**
     * @time   2019/9/5
     * 关联user_articles_store表
     * @author wsp@xxx.com wsp
     */
    public function User_Articles_Store()
    {
        return $this->hasMany(UserArticlesStore::class,'article_id','id');
    }

    /**
     * @time   2019/9/5
     * 参数筛选
     * @author wsp@xxx.com wsp
     */
    public function scopeParamSearch($query, $data)
    {
        if (isset($data['id'])) {
            $query = $query->where('id', $data['id']);
        }
        return $query;
    }

}
