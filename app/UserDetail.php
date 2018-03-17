<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    protected $table = 'users_detail';
    protected $primaryKey = 'id';
    protected $fillable =
        [
            'sex',
            'birthday'
        ];
    public $timestamps = false;



}
