<?php

namespace App\Models\Wechat;

use Illuminate\Database\Eloquent\Model;

class UserArticlesStore extends Model
{
    protected $table = 'user_articles_store';
    public function Articles()
    {
        return $this->belongsTo(Articles::class);
    }
}
