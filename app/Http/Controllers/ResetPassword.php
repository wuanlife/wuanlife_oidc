<?php
/**
 * Created by PhpStorm.
 * User: BlackTV
 * Date: 2018/4/28
 * Time: 12:06
 */

namespace App\Http\Controllers;

use App\Models\Users\UsersBase;
use App\Models\Users\ResetPassword as ResetPsd;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;

class ResetPassword extends Controller
{
    /**
     * 发送重置密码邮件
     * @param $email
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function sendEmail($email)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make([
                'email' => $email
            ], [
                'email' => 'required|string|E-mail|',
            ]);

            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            $id = $this->getId($email);
            if ($id == -1) {
                return response(['error' => 'The email is non-registered'], 404);
            }
            // 验证用户是否是在短时间内多次请求
            $this->checkRequestAllowed($id);

            // 构造找回密码 url
            $exp = date("Y-m-d H:i:s", time() + env('RESET_PASSWORD_TOKEN_EXP'));
            $token = $this->getToken($email);
            $view_url = route('reset_password');
            $url = "{$view_url}?&id={$id}&token={$token}";

            // 在数据库中保存 token
            $saveFlag = $this->saveResetToken($id, $token, $exp);
            if (!$saveFlag) {
                return response(['error' => 'Failed to add relevant data'], 400);
            }

            // 发送邮件
            $this->send($email, $url);
            DB::commit();
            return response([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            DB::rollback();
            return response(['error' => 'Failed to send email: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 通过用户邮箱获取用户 id
     * @param $email
     * @return int|mixed,如果查询到返回Id，否则返回-1
     */
    private function getId($email)
    {
        $userBase = new UsersBase();
        $array = $userBase->getUserIdByEmail($email);
        if ($array == null) {
            return -1;
        }
        return $array->id;
    }

    /**
     * 检测用户是否在短时间内多次请求重置密码
     * @param $id
     * @throws \Exception
     */
    private function checkRequestAllowed($id)
    {
        $res = ResetPsd::where('user_id', $id)->first();

        if (!empty($res) && strtotime($res->created_at) + 60 > time()) {
            throw new \Exception('Requesting it more often');
        }

    }

    /**
     * 生成随机 token
     * @param $email
     * @return string
     */
    private function getToken($email)
    {
        $array = [
            "a",
            "b",
            "c",
            "d",
            "e",
            "f",
            "g",
            "h",
            "i",
            "j",
            "k",
            "l",
            "m",
            "n",
            "o",
            "p",
            "q",
            "r",
            "s",
            "t",
            "u",
            "v",
            "w",
            "x",
            "y",
            "z",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "0"
        ];
        $string = $email . env('APP_KEY');//盐值在env文件里定义常量
        for ($i = 0; $i < 4; $i++) {
            $string = $string . $array[mt_rand(0, count($array) - 1)];
        }
        return md5($string);
    }

    /**
     * 将找回密码信息 储存/更新 到数据库中
     * @param $id
     * @param $token
     * @param $exp
     * @return bool|mixed
     */
    private function saveResetToken($id, $token, $exp)
    {
        $resetPassword = new ResetPsd();
        if ($resetPassword->getMessageById($id) != null) {
            return $resetPassword->modify($id, $token, $exp);   // 存在，更新数据
        } else {
            return $resetPassword->saveToken($id, $token, $exp); // 不存在，插入数据
        }
    }

    /**
     * 向用户邮箱发送邮件的方法
     * @param $email
     * @param $url
     * @return \Illuminate\Contracts\Routing\ResponseFactory|int|Response
     */
    private function send($email, $url)
    {
        //MailboxTemplate视图只是测试用视图，发送者邮箱、名称、标题皆在env文件里设置常量
        $flag = Mail::send(env('MAIL_TEMPLATE'), ['url' => $url], function ($message) use ($email) {
            $message->to($email)->subject(env('MAIL_TITLE'));
        });

        //$flag的值为FALSE||null的时候发送成功
        if ($flag) {
            return -1;//发送失败
        }
        return 1;//发送成功
    }

    /**
     * 重置密码
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function resetPassword($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'psd_token' => 'required|alpha_num|filled',
                'new_psd' => 'required|alpha_num|filled',
            ]);

            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            // 验证 token 是否存在于数据库中
            $token = $request->input('psd_token');
            $this->tokenVerification($id, $token);

            $password = $request->input("new_psd");

            $usersBase = new UsersBase();
            $resetPassword = new ResetPsd();
            $buffer = $resetPassword->getMessageById($id);

            if (empty($buffer) || $buffer->token !== $token) {
                return response(['error' => 'Illegal request', 403]);
            }
            if ($usersBase->resetPasswordById($id, md5($password))) {
                return response([], Response::HTTP_NO_CONTENT);
            } else {
                return response(['error' => 'Failed to reset password'], 400);
            }

        } catch (\Exception $e) {
            return response(['error' => 'Failed to reset password: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 用于验证 token 是否存在并未过期
     * @param $id
     * @param null $token
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function tokenVerification($id, $token = null)
    {
        try {
            if (empty($token)) {
                $validator = Validator::make(request()->all(), [
                    'token' => 'required|alpha_num|filled',
                ]);
                if ($validator->fails()) {
                    return response(['error' => $validator->errors()->first()], 422);
                }

                $token = request()->input("token");
            }
            $resetPassword = new ResetPsd();
            $buffer = $resetPassword->tokenExists($token, $id);
            if (empty($buffer)) {
                return response(['error' => 'Illegal request,token is not exists'], 401);
            }
            if (strtotime($buffer->exp) < time()) {
                return response(['error' => 'Request expired'], 400);
            }
            return response([], Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to authentication token: ' . $e->getMessage()], 400);
        }
    }

    /**
     * 修改密码
     * @param $id
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function modifyPassword($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|alpha_num|filled',
                'new_password' => 'required|alpha_num|filled',
            ]);
            if ($validator->fails()) {
                return response(['error' => $validator->errors()->first()], 422);
            }

            $password = $request->input("password");
            $new_password = $request->input("new_password");
            $id_token = $request->get('id-token');
            if ($id_token->uid != $id) {
                return response(['error' => 'Illegal request', 403]);
            }

            $user = new UsersBase();
            // 修改密码
            if ($user->modifyPasswordById($id, md5($password), md5($new_password))) {
                return response([], Response::HTTP_NO_CONTENT);//修改成功
            } else {
                return response(['error' => 'Failed to change password'], 400);
            }
        } catch (\Exception $e) {
            return response(['error' => 'Failed to authentication token: ' . $e->getMessage()], 400);
        }
    }

}



