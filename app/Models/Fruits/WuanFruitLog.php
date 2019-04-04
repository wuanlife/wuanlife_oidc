<?php

namespace App\Models\Fruits;

use Illuminate\Database\Eloquent\Model;

class WuanFruitLog extends Model
{
    public $timestamps = false;
    protected $table = 'wuan_fruit_log';
    protected $primaryKey = 'id';
    protected $fillable = [
        'scene',
        'user_id',
        'value',
        'created_at'
    ];
}
