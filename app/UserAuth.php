<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    protected $table = 'users_auth';
    protected $primaryKey = 'id';

    public function authDetail()
    {
        return $this->hasOne('App\AuthDetail','id','auth');
    }
}
