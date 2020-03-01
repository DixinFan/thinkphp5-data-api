<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index()
    {
        $data = Db::name('users')->find();
        return ['data'=>$data,'code'=>1,'message'=>'操作完成'];
    }
    public function doLogin($userName='',$password='')
    {   
        $has = db('users')->where('user_email', $userName)->find();
        $data = '';
        $code = -1;
        $message = '';
    	if(empty($has)){
            $code = 401;
            $message = '用户不存在';
    	}else{
            // if($has['user_pwd'] != md5($password)){
            if($has['user_pwd'] != $password){
                $code = 401;
                $message = '用户密码错误';
            }else{
                $code = 200;
                $message = '登录成功';
            }
        }
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function uploadVideo($userName=''){
        $data = '';
        $code = -1;
        $message = '';
        $file = request()->file('video');
        $info = $file->move( '../uploads');
        if($info){
            $code = 200;
            $data = ['uploaded_video_name' => $info->getFilename(), 'user_email' => $userName];
            Db::name('uploaded_videos')
                ->data($data)
                ->insert();
            $message = '上传成功';
        }else{
            $code = 401;
            $message = $file->getError();
        }
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
}