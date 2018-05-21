<?php

namespace App\Models\Users;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class UsersBase extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'users_base';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'password', 'remember_token',
    ];

    public function avatar()
    {
        return $this->hasOne('App\Models\Users\Avatar', 'user_id', 'id');
    }

    public function userDetail()
    {
        return $this->hasOne('App\Models\Users\UserDetail', 'id', 'id');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function resetPassword(){
        return $this->hasOne('App\Models\Users\ResetPassword',"user_id","id");
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */

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
     * 根据id修改密码
     * @param $id
     * @param $password
     * @param $newPassword
     * @return bool
     */
    public function modifyPasswordById($id,$password,$newPassword){
        $user = $this->find($id);
        //判断原密码是否输入正确
        if($user->password==$password){
            $user->password = $newPassword;
            return $user->save();
        }
        return false;
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
