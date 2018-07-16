<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserDetail extends Model
{
    public $timestamps = false;
    protected $table = 'users_detail';
    protected $primaryKey = 'id';
    protected $fillable =
        [
            'sex',
            'birthday'
        ];


}
