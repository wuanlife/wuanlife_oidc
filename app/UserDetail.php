<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'users_detail';
    protected $primaryKey = 'id';

    public function sexDetail()
    {
        return $this->hasOne('App\SexDetail','id','sex');
    }
}
