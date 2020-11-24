<?php

namespace App\Http\Controllers\web\SeatBooking;

use App\Models\Wxuser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class BaseController extends Controller
{
    public $token = null;
    public $wxuser = null;
    public $globalConfig = null;

    public function __construct(Request $request)
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }

        $this->middleware('RequiredToken');
        $this->token = $request->input('token');
        $this->wxuser = Wxuser::getCache($this->token);
        $this->globalConfig = Cache::get('seatGlobalConfig_'.$this->token);
        if(!$this->globalConfig){
            $this->globalConfig = \App\Models\Seat\Config::where('token',$this->token)->first();
            if(!$this->globalConfig) return abort(404);
            Cache::put('seatGlobalConfig_'.$this->token,$this->globalConfig,180);
        }


    }
}
