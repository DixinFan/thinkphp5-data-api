<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
// use Swagger\Annotations as SWG;

class Index extends Controller
{
    public function index()
    {
        $data = Db::name('users')->find();
        // $swagger=\Swagger\scan(__DIR__);  
        // $res=$swagger->saveAs('./swagger.json');  
        // $data = ['name'=>'thinkphp','url'=>'thinkphp.cn'];
        //$data = Db::name('think_data')->find();
        // $data = ['name'=>'thinkphp','url'=>'thinkphp.cn'];
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
    	// if($has['user_pwd'] != md5($param['user_pwd'])){
    	// }
        // $data =['userName'=>$userName,'password'=>$password];
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('video');
        // 移动到框架应用根目录/uploads/ 目录下
        $info = $file->move( '../uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            echo $info->getFilename(); 
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
        }
    }
}