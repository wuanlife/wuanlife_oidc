<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Avatar extends Model
{
    protected $table = 'avatar_url';
    protected $primaryKey = 'user_id';
    protected $fillable =
        [
            'user_id',
            'url',
            'delete_flg'
        ];
    public $timestamps = false;
}
