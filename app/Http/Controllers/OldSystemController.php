<?php

namespace App\Http\Controllers;


use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OldSystemController extends Controller
{

    public function store(Request $request)
    {
        $referer = $request->headers->get('origin');
        $validation = str_contains($referer, 'u.interlib.cn');

        if ($validation === false) {
            throw new AuthorizationException('非法访问！');
        }

        $inputName = 'input-mp3';
        $request->validate([
            $inputName => 'required|mimes:mpga'
        ]);
        if ($request->hasFile($inputName)) {
            $storage = Storage::disk('oss');
            $path = 'yizhitu/newAudio/' . date('Y-m-d');
            $url = $storage->putFile($path, $request->file($inputName));
            $fullUrl = $storage->url($url);
            return ['uploaded' => $fullUrl];
        }

    }
}
