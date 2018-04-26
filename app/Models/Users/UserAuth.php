<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserAuth extends Model
{
    public $timestamps = false;
    protected $table = 'users_auth';
    protected $primaryKey = 'id';

    public function authDetail()
    {
        return $this->hasOne('App\Models\Users\AuthDetail', 'id', 'auth');
    }
}
