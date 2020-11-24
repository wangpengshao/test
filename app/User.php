<?php

namespace App;

//use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use SMartins\PassportMultiauth\HasMultiAuthApiTokens;

class User extends Authenticatable
{
//    use HasApiTokens, Notifiable;
    use Notifiable, HasMultiAuthApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'rate_limit', 'status'
    ];

    protected $casts = [
        's_token' => 'json',
        'name' => 'json'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token', 'password'
    ];

    public function findForPassport($identifier)
    {
//        return $this->orWhere('email', $identifier)->orWhere('name', $identifier)->first();
        return $this->Where('username', $identifier)->first();
    }

    public function setScopesAttribute($options)
    {
        if (is_array($options)) {
            $this->attributes['scopes'] = implode(',', $options);
        }
    }

}
