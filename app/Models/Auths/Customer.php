<?php

namespace App\Models\Auths;

use App\Models\Customer as AuthCustomer;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
//use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Customer extends AuthCustomer implements AuthenticatableContract, AuthorizableContract {

    use Authenticatable,Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
//    protected $fillable = [
//        'name', 'email',
//    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
//    protected $hidden = [
//        'password',
//    ];

}
