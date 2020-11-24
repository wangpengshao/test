<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActContentApi extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
//        dd();
//        dd($this);
//        dd(parent::toArray($request));
//        return ['esd'];
//        dd($this);
//        return parent::toArray($request);
        return [
//            'current_page' => 5,
//            'name' => $this->name,
////            'email' => $this->email,
////            'secret' => $this->when($this->isAdmin(), 'secret-value'),
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ];
//        return [
//            'data' => $this->collection,
//        ];
    }
}
