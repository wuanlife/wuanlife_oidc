<?php
/**
 * Created by PhpStorm.
 * User: csc
 * Date: 2018/5/4
 * Time: 19:12
 */

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class WuanScore extends Model
{
    public $timestamps = false;
    protected $table = 'wuan_score';
    protected $primaryKey = 'user_id';

}