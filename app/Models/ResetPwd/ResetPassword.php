<?php
/**
 * Created by PhpStorm.
 * User: BlackTV
 * Date: 2018/4/28
 * Time: 12:06
 */
    namespace App\Models\ResetPwd;
    use Illuminate\Database\Eloquent\Model;

    class ResetPassword extends Model{
        protected $table = "reset_password";
        protected $primaryKey = "user_id";
        public $timestamps = false;

        /**
         * @param $id
         * @param $token
         * @param $exp
         * @return bool
         * 插入一条数据
         */
        public function svae($id,$token,$exp){
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
        public function modify($id,$token,$exp){
            $buffer = $this::find($id);
            $buffer->token = $token;
            $buffer->exp = $exp;
            return $buffer->save();
        }

        /**
         * @param $$id
         * @return mixed
         * 根据Id获取找回密码信息
         */
        public function getMessageById($id){
            return $this->find($id);
        }

        /**
         * @param $token
         * @return mixed
         * 根据Token获取找回密码信息
         */
        public function getMeassageByToken($token){
            return $this::where("token",$token)->first();
        }
    }