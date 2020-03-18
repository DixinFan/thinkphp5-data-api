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
    public function doLogin( $userName='',$password='')
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
    public function uploadVideo($UserName='',$VideoTitle=''){
        $data = '';
        $code = -1;
        $message = '';
        $video_file = request()->file('video');
        $image_file = request()->file('image');
        $video_info = $video_file->move('../public/uploads');
        $image_info = $image_file->move('../public/uploads');
        if($video_info && $image_info){
            $data = ['uploaded_video_name' => $video_info->getSaveName(), 'user_email' => $UserName, 'video_title' => $VideoTitle, 'video_poster'=>$image_info->getSaveName()];
            Db::name('uploaded_videos')
                ->data($data)
                ->insert();
            $message = '上传成功'; 
        }else{
            $code = 401;
            $message = '上传失败';
            // $message = $file->getError();
        }
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function playVideo($userName=''){
        $code = -1;
        $message = '';
        $has = Db::table('uploaded_videos')->where('user_email',$userName)->column('uploaded_video_name'); 
        $data = ['count'=>count($has),'video_list'=>$has];
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function playPoster($userName=''){
        $code = -1;
        $message = '';
        $hasTitle = Db::table('uploaded_videos')->where('user_email',$userName)->column('video_title'); 
        $hasPoster = Db::table('uploaded_videos')->where('user_email',$userName)->column('video_poster');
        $data = ['count'=>count($hasTitle),'video_title'=>$hasTitle,'video_poster'=>$hasPoster];
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function testPython(){
        $output = shell_exec("ls");
        return $output;
    }
}