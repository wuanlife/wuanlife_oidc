<?php
/**
 * Created by PhpStorm.
 * User: BlackTV
 * Date: 2018/4/28
 * Time: 12:06
 */

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    public $timestamps = false;
    protected $table = "reset_password";
    protected $primaryKey = "user_id";

    /**
     * @param $id
     * @param $token
     * @param $exp
     * @return bool
     * 插入一条数据
     */
    public function saveToken($id, $token, $exp)
    {
        $this->created_at = date('Y-m-d H:i:s',time());
        $this->user_id = $id;
        $this->token = $token;
        $this->exp = $exp;
        return $this->save();
    }

    /**
     * @param $id
     * @param $token
     * @param $exp
     * @return mixed
     * 根据Id修改token与exp
     */
    public function modify($id, $token, $exp)
    {
        $buffer = $this::find($id);
        $buffer->created_at = date('Y-m-d H:i:s',time());
        $buffer->token = $token;
        $buffer->exp = $exp;
        return $buffer->save();
    }

    /**
     * 根据Id获取找回密码信息
     * @param $id
     * @return mixed
     */
    public function getMessageById($id)
    {
        return $this->find($id);
    }

    /**
     * 根据Token获取找回密码信息
     * @param $token
     * @param $id
     * @return mixed
     */
    public function tokenExists($token,$id)
    {
        return $this::where([
            'token'=> $token,
            'user_id' => $id
        ])->first();
    }
}