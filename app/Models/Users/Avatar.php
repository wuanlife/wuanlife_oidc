<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    public $timestamps = false;
    protected $table = 'avatar_url';
    protected $primaryKey = 'user_id';
    protected $fillable =
        [
            'user_id',
            'url',
            'delete_flg'
        ];
}
