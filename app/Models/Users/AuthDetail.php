<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class AuthDetail extends Model
{
    protected $table = 'auth_detail';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
