<?php
/**
 * Created by PhpStorm.
 * User: BlackTV
 * Date: 2018/4/28
 * Time: 12:06
 */
    namespace App\Models\ResetPwd;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Database\Eloquent\Model;

    class UsersBase extends Model {
        protected $table = "users_base";
        protected $primaryKey = "id";

        /**
         * @return \Illuminate\Database\Eloquent\Relations\HasOne
         * 一对一关联
         */
        public function resetPassword(){
            return $this->hasOne('App\Models\ResetPwd;\ResetPassword',"user_id","id");
        }

        /**
         * @param $email
         * @return \Illuminate\Support\Collection
         * 根据邮箱查询用户信息
         */
        public function getUserIdByEmail($email){
            return $this::where("email",$email)->first();
        }

        /**
         * @param $id
         * @param $passWord
         * @return int
         * 根据id重置密码
         */
        public function resetPasswordById($id,$passWord){
            return DB::transaction(function () use($id,$passWord){
                $user = $this->find($id);
                $user->password = $passWord;
                //修改密码并删除该用户找回密码信息
                if($user->save() && ResetPassword::destroy($id)){
                    return true;
                }
                return false;
            });
        }

        /**
         * @param $id
         * @param $password
         * @param $newPassword
         * @return int
         * 根据id修改密码
         */
        public function modifyPasswordById($id,$password,$newPassword){
            $user = $this->find($id);
            //判断原密码是否输入正确
            if($user->password==$password){
                $user->password = $newPassword;
                return $user->save();
            }
            return -1;
        }
    }