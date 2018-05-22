<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 19:12
 */

namespace App\Models\Points;

use Illuminate\Database\Eloquent\Model;

class WuanPoints extends Model
{
    public $timestamps = false;
    protected $table = 'wuan_points';
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'user_id',
    ];
}