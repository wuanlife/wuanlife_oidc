<?php

namespace App\Models\Fruits;

use Illuminate\Database\Eloquent\Model;

class WuanSign extends Model
{
    //
    public $timestamps = false;
    protected $table = 'wuan_sign';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id', 'value'
    ];
}
