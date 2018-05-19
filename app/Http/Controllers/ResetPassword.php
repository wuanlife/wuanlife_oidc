<?php
    /**
     * Created by PhpStorm.
     * User: BlackTV
     * Date: 2018/4/28
     * Time: 12:06
     */
    namespace App\Http\Controllers;
    use App\Models\ResetPwd\UsersBase;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Mail;
    use Symfony\Component\HttpFoundation\Response;
    use Validator;

    class ResetPassword extends Controller{
        /**
         * @param Request $request
         * @return int|string
         * 接收用户根据邮箱重置密码的请求,并发送邮件
         */
        public function sendEmail(Request $request){
            $validator = Validator::make($request->all(),[
                'email' => 'required|string|E-mail|',
            ]);

            if($validator->fails()){
                return response(["error" => "表单验证失败"],Response::HTTP_BAD_REQUEST);
            }

            $email = $request->input("email");
            if($email==null){
                return response(["error" => "email不能为空"],Response::HTTP_BAD_REQUEST);
            }

            $id = $this->getId($email);
            if($id==-1){
                return response(["error" => "发送失败，该邮箱尚未注册本网站"],Response::HTTP_BAD_REQUEST);
            }

            //有效时长取决于env里的EXP常量，单位h
            $exp = date("Y-m-d H:i:s",time()+(env("EXP")*60*60));
            $token = $this->getToken($email);
            $url = env("RESET_PASSWORD_URL")."?email=".$email."&id=".$id."&token=".$token;
            $saveFlag = $this->saveResetPassword($id,$token,$exp);

            if($saveFlag==true || $saveFlag==1){
                $sendFlag = $this->send($email,$url);
                if($sendFlag==1){
                    return response("",Response::HTTP_NO_CONTENT);//发送成功
                }else if($sendFlag==-1){
                    return response(["error" => "发送失败try catch未能捕捉的错误"],Response::HTTP_BAD_REQUEST);
                }else{
                    return response($saveFlag,Response::HTTP_BAD_REQUEST);
                }
            }
            return response("邮件尚未发送，保存到找回密码表出错",Response::HTTP_BAD_REQUEST);
        }

        /**
         * @param Request $request
         * @return Response
         * 用于验证token是否过期
         */
        public function tokenVerification(Request $request){
            $validator = Validator::make($request->all(),[
                'token' => 'alpha_num|filled',
            ]);

            if($validator->fails()){
                return response(["error" => "表单验证失败"],Response::HTTP_BAD_REQUEST);
            }

            $token = $request->input("token");
            if($token==null){
                return response(["error" => "token不能为空"],Response::HTTP_BAD_REQUEST);
            }
            $resetPassword = new \App\Models\ResetPwd\ResetPassword();
            $buffer = $resetPassword->getMeassageByToken($token);
            if($buffer!=null && strtotime($buffer->exp)>time()){
                return response("",Response::HTTP_NO_CONTENT);//未过期
            }else{
                return response(["error" => "验证失败"],Response::HTTP_BAD_REQUEST);//已过期
            }
        }

        /**
         * @param $id
         * @param Request $request
         * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
         * 重置密码
         */
        public function resetPassword($id,Request $request){
            $validator = Validator::make($request->all(),[
                'id' => 'numeric|filled',
                'token' => 'alpha_num|filled',
                'passWord' => 'alpha_num|filled',
            ]);

            if($validator->fails()){
                return response(["error" => "表单验证失败"],Response::HTTP_BAD_REQUEST);
            }

            $token = $request->input("token");
            $password = $request->input("PassWord");
            if($id==null || $token==null || $password==null){
                return response(["error" => "参数不能为空"],Response::HTTP_BAD_REQUEST);
            }

            $usersBase = new UsersBase();
            $resetPassword = new \App\Models\ResetPwd\ResetPassword();
            $buffer = $resetPassword->getMessageById($id);

            if($buffer!=null && $buffer->token==$token){
                if($usersBase->resetPasswordById($id,md5($password))==true){
                    return response("",Response::HTTP_NO_CONTENT);//修改成功
                }else{
                    return response(["error" => "重置失败"],Response::HTTP_BAD_REQUEST);
                }
            }
            return response(["error" => "未能修改可能是token错误或该用户不存在"],Response::HTTP_BAD_REQUEST);//修改失败
        }

        /**
         * @param $id
         * @param Request $request
         * @return \Illuminate\Contracts\Routing\ResponseFactory|Response
         * 修改密码
         */
        public function modifyPassword($id,Request $request){
            $validator = Validator::make($request->all(),[
                'id' => 'numeric|filled',
                'password' => 'alpha_num|filled',
                'new_password' => 'alpha_num|filled',
                'id-token' => 'filled'
            ]);
            if($validator->fails()){
                return response(["error" => "表单验证失败"],Response::HTTP_BAD_REQUEST);
            }

            $password = $request->input("password");
            $newPassword = $request->input("new_password");
            $idToken = $request->get("filled");
            if($id==null || $password==null || $newPassword==null || $id=!$idToken->uid){
                return response(["error" => "参数不能为空"],Response::HTTP_BAD_REQUEST);
            }

            $userBase = new UsersBase();
            //修改密码
            if($userBase->modifyPasswordById($id,md5($password),md5($newPassword))>0){
                return response("",Response::HTTP_NO_CONTENT);//修改成功
            }
            return response(["error" => "修改失败"],Response::HTTP_BAD_REQUEST);//修改失败
        }

        /**
         * @param $id
         * @param $token
         * @param $exp
         * @return bool|mixed
         * 将找回密码信息根据情况而写入到找回密码表
         */
        private function saveResetPassword($id,$token,$exp){
            $resetPassword = new \App\Models\ResetPwd\ResetPassword();
            if($resetPassword->getMessageById($id)!=null){
                return $resetPassword->modify($id,$token,$exp);//存在，更新数据
            }else{
                return $resetPassword->svae($id,$token,$exp);//不存在，插入数据
            }
        }

        /**
         * @param $email
         * @return int|mixed,如果查询到返回Id，否则返回-1
         */
        private function getId($email){
            $userBase = new UsersBase();
            $array =  $userBase->getUserIdByEmail($email);
            if($array==null){
                return -1;
            }
            return $array->id;
        }

        /**
         * @param $email
         * @return string
         *生成Token
         */
        private function getToken($email){
            $array = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o",
                "p","q","r","s","t","u","v","w","x","y","z","1","2","3","4","5","6","7",
                "8","9","0"];
            $string = $email.env("SOLT");//盐值在env文件里定义常量
            for($i=0;$i<4;$i++){
                $string = $string.$array[rand(0,count($array)-1)];
            }
            return md5($string);
        }

        /**
         * @param $email
         * @param $url
         * @return int
         * 向用户邮箱发送邮件的方法
         */
        private function send($email,$url){
            try {
                //MailboxTemplate视图只是测试用视图，发送者邮箱、名称、标题皆在env文件里设置常量
                $flag = Mail::send(env('MAIL_TEMPLATE'),['url'=>$url],function($message)use($email){
                    $message ->to($email)->subject(env('EMAIL_TITLE'));
                });
            }catch(Exception $error) {
                return ["error" => "邮件发送失败".$error->getMessage()];
            }

            //$flag的值为FALSE||null的时候发送成功
            if($flag){
                return -1;//发送失败
            }
            return 1;//发送成功
        }
    }



