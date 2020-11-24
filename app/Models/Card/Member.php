<?php

namespace App\Models\Card;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Member extends Model
{
    protected $table = 'wechat_membercard';

    protected $dateFormat = 'U';

    protected $casts = [
        'sku' => 'json',
        'date_info' => 'json',
        'custom_cell1' => 'json'
    ];

    public function scopeGetColor($query,$color)
    {
        $colors = [
            'Color010' => '#63b359', 'Color020' => '#2c9f67', 'Color030' => '#509fc9', 'Color040' => '#5885cf',
            'Color050' => '#9062c0', 'Color060' => '#d09a45', 'Color070' => '#e4b138', 'Color080' => '#ee903c',
            'Color081' => '#f08500', 'Color082' => '#a9d92d', 'Color090' => '#dd6549', 'Color100' => '#cc463d',
            'Color101' => '#cf3e36', 'Color102' => '#5E6671', 'Color103' => '#026842'
        ];
        return $colors[$color];
    }

    public function getLogoUrlAttribute($logo_url)
    {
        return Storage::disk('uploads')->url($logo_url);
    }

    public function getBackgroundPicUrlAttribute($background_pic_url)
    {
        return Storage::disk('uploads')->url($background_pic_url);
    }

    public function getSkuAttribute($sku)
    {
        return json_decode($sku,true);
    }
}
