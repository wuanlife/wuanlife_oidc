<?php

namespace App\Models\Fruits;

use Illuminate\Database\Eloquent\Model;

class WuanFruit extends Model
{
    public $timestamps = false;
    protected $table = 'wuan_fruit';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
    ];
}
