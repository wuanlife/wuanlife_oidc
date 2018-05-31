<?php

namespace App\Http\Controllers;

use Qiniu\Auth;

class QiNiuController extends Controller
{
    /**
     * 获取七牛上传凭证
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function getUploadToken()
    {
        try {
            $auth = new Auth(env('QINIU_ACCESS_KEY'), env('QINIU_SECRET_KEY'));
            $token = $auth->uploadToken(env('QINIU_BUCKET_NAME'));
            return response(['upload-token' => $token]);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to get upload token']);
        }
    }
}
