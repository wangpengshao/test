<?php

namespace App\Models\Seat;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class SeatRegion extends Model
{
    use ModelTree,AdminBuilder;
    protected $table = 'seat_region';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setParentColumn('pid');
        $this->setTitleColumn('name');
    }

    public function paginate()
    {
        $result = $this->where('token',session('wxtoken'))->orderBy('sort')->get()->toArray();
        $result = $this->generateTree($result);
        $newResult = [];
        foreach($result as $key => $value){
            if(isset($value['children'])) unset($value['children']);
            $newResult [] = $value;
            if(isset($result[$key]['children'])){
                foreach($result[$key]['children'] as $v){
                    $v['name'] = str_repeat("&nbsp;",6) . $v['name'];
                    $newResult [] = $v;
                    unset($v);
                }
            }
            unset($value,$result['$key']);
        }
        $collect = static::hydrate($newResult);
        $paginator = new LengthAwarePaginator($collect, count($newResult), 50);
        $paginator->setPath(url()->current());
        return $paginator;
    }

    public static function with($relations)
    {
        return new static;
    }

    protected function generateTree($arr)
    {
        $items = array();
        foreach($arr as $value){
            $items[$value['id']] = $value;
        }
        $tree = array();
        foreach($items as $key => $item){
            if(isset($items[$item['pid']])){
                $items[$item['pid']]['children'][] = &$items[$key];
            }else{
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

    public function  scopeRegions()
    {
        $result = $this->where('token',session('wxtoken'))->where('pid',0)->get();
        $return = [0=>'Root'];
        foreach($result as $key => $value){
            $return[$value['id']] = $value['name'];
        }
        return $return;
    }

    public function scopeGetAllowRegions($query, $token)
    {
        return $query->where([
            'token' => $token,
            'status' => 1,
            'booking_switch' =>1
        ]);
    }

}
