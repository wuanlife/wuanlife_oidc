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
            $auth = new Auth(env('QINIU-ACCESS-KEY'), env('QINIU-SECRET-KEY'));
            $token = $auth->uploadToken(env('QINIU-BUCKET-NAME'));
            return response(['upload-token' => $token]);
        } catch (\Exception $e) {
            return response(['error' => 'Failed to get upload token']);
        }
    }
}
