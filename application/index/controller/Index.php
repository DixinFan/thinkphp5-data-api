<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;
use think\Session;

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
            $code = 200;
            $message = '上传成功'; 
        }else{
            $code = 401;
            $message = '上传失败';
            // $message = $file->getError();
        }
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    // public function playVideo($userName=''){
    //     $code = -1;
    //     $message = '';
    //     $has = Db::table('uploaded_videos')->where('user_email',$userName)->column('uploaded_video_name'); 
    //     if($has){
    //         $code = 200;
    //         $message = '播放成功';
    //     }else{
    //         $code = 401;
    //         $message = '播放失败';
    //     }
    //     $data = ['count'=>count($has),'video_list'=>$has];
    //     return ['data'=>$data,'code'=>$code,'message'=>$message];
    // }
    public function listPoster($userName=''){
        $code = -1;
        $message = '';
        $hasTitle = Db::table('uploaded_videos')->where('user_email',$userName)->column('video_title'); 
        $hasPoster = Db::table('uploaded_videos')->where('user_email',$userName)->column('video_poster');
        $hasVideo = Db::table('uploaded_videos')->where('user_email',$userName)->column('uploaded_video_name');
        if($hasTitle){
            $code = 200;
            $message = '请求已上传视频列表成功';
        }else{
            $code = 401;
            $message = '请求已上传视频列表失败';
        }
        $data = ['count'=>count($hasTitle),'VideoTitle'=>$hasTitle,'VideoPoster'=>$hasPoster,'VideoName'=>$hasVideo];
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function uploadRenderedVideo($OriginVideoName='',$ActionByFrame=''){
        $data = '';
        $code = -1;
        $message = '';
        $video_file = request()->file('RenderedVideoFile');
        $json_file = request()->file('Skeleton');
        $video_info = $video_file->move('../public/uploads');
        $json_info = $json_file->move('../public/uploads');
        if($video_info && $json_info){
            $data = ['rendered_video_name' => $video_info->getSaveName(), 'uploaded_video_name' => $OriginVideoName, 'action_by_frame' => $ActionByFrame, 'skeleton_data_file_name'=>$json_info->getSaveName()];
            Db::name('rendered_videos')
                ->data($data)
                ->insert();
            $message = '上传成功'; 
        }else{
            $code = 401;
            $message = '上传失败';
        }
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function listRecognitedPoster($userName=''){
        $code = -1;
        $message = '';
        $hasTitle = Db::name('uploaded_videos')
        ->join('rendered_videos', 'uploaded_videos.uploaded_video_name = rendered_videos.uploaded_video_name')        
        ->where('uploaded_videos.user_email',$userName)
        ->column('uploaded_videos.video_title');
        $hasPoster = Db::name('uploaded_videos')
        ->join('rendered_videos', 'uploaded_videos.uploaded_video_name = rendered_videos.uploaded_video_name')        
        ->where('uploaded_videos.user_email',$userName)
        ->column('uploaded_videos.video_poster');
        $hasVideo = Db::name('uploaded_videos')
        ->join('rendered_videos', 'uploaded_videos.uploaded_video_name = rendered_videos.uploaded_video_name')        
        ->where('uploaded_videos.user_email',$userName)
        ->column('rendered_videos.rendered_video_name');
        $data = ['count'=>count($hasTitle),'VideoTitle'=>$hasTitle,'VideoPoster'=>$hasPoster,'VideoName'=>$hasVideo];
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function actionAnalysis($RenderedVideoName=''){
        $code = -1;
        $message = '';
        $action = Db::name('rendered_videos')
        ->where('rendered_video_name',$RenderedVideoName)
        ->column('action_by_frame');
        $action_list = explode('^',$action[0]);
        $action_analysis = array_count_values($action_list);
        $data = $action_analysis;
        return ['data'=>$data,'code'=>$code,'message'=>$message];        
    }
    public function getSkeleton($RenderedVideoName=''){
        $code = -1;
        $message = '';
        $action = Db::name('rendered_videos')
        ->where('rendered_video_name',$RenderedVideoName)
        ->column('skeleton_data_file_name');
        $skeleton = $action[0];
        $base = '../public/uploads/';
        $fileName = $base . $skeleton;
        $string = file_get_contents($fileName);
        $data = json_decode($string,true);
        return ['data'=>$data,'code'=>$code,'message'=>$message];        
    }
    public function doRegister($UserName='',$Password='')
    {   
        $data = '';
        $code = -1;
        $message = '';
        $data = ['user_email' => $UserName, 'user_pwd' => $Password];
        Db::name('users')
        ->data($data)
        ->insert();
        $code = 200;
        $message = '注册成功';
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    public function doRetrieve($UserName='')
    {   
        $has = db('users')->where('user_email', $UserName)->find();
        $data = '';
        $code = -1;
        $message = '';
    	if(empty($has)){
            $code = 401;
            $message = '用户不存在';
    	}else{
            $password = $has['user_pwd'];
            $email = $UserName;
            $sendmail = 'dixin_fan@163.com'; //发件人邮箱---填写你申请的163邮箱
            $sendmailpswd = "DXHIRCSOGTADWWMO"; //客户端授权密码,而不是邮箱的登录密码，通过设置获得,下面会说到这里如何设置
            $send_name = '基于骨架的动作识别系统';// 设置发件人信息，如邮件格式说明中的发件人，可以写公司名称
            $toemail = $email;//定义收件人的邮箱
            $to_name = $email;//设置收件人信息，如邮件格式说明中的收件人
            Loader::import('PHPMailer',EXTEND_PATH);
            $mail = new \PHPMailer();
            $mail->isSMTP();// 使用SMTP服务
            $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
            $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
            $mail->SMTPAuth = true;// 是否使用身份验证
            $mail->Username = $sendmail;//// 发送方的
            $mail->Password = $sendmailpswd;//客户端授权密码,而不是邮箱的登录密码！
            $mail->SMTPSecure = "ssl";// 使用ssl协议方式
            $mail->Port = 465;// 服务器端口 25 或者465 具体要看邮箱服务器支持
            $mail->setFrom($sendmail, $send_name);// 设置发件人信息，如邮件格式说明中的发件人，
            $mail->addAddress($toemail, $to_name);// 设置收件人信息，如邮件格式说明中的收件人，
            $mail->addReplyTo($sendmail, $send_name);// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
            $mail->Subject = "找回密码";// 邮件标题
            $mail->Body = "<b>您的密码是：$password</b> <br/><br/> 如果非本人操作无需理会！<br/>". date('Y-m-d H:i:s');// 邮件正文
            $mail->AltBody = "您的密码是：".$password.",如果非本人操作无需理会！";
            if (!$mail->send()) { // 发送邮件
                $code = 401;
                $message = $mail->ErrorInfo; // 输出错误信息
            } else {
                $code = 200;
                $message = '发送成功';
            }
        }
        return ['data'=>$data,'code'=>$code,'message'=>$message];
    }
    // //发送邮件
    // public function getEmailCode(){
    //     // $email = $this->request->get('email');//获取收件人邮箱
    //     $email = 'dixinfan@foxmail.com';
    //     //return $email;
    //     $sendmail = 'dixin_fan@163.com'; //发件人邮箱---填写你申请的163邮箱
    //     $sendmailpswd = "DXHIRCSOGTADWWMO"; //客户端授权密码,而不是邮箱的登录密码，通过设置获得,下面会说到这里如何设置
    //     $send_name = '基于骨架的动作识别系统';// 设置发件人信息，如邮件格式说明中的发件人，可以写公司名称
    //     $toemail = $email;//定义收件人的邮箱
    //     $to_name = $email;//设置收件人信息，如邮件格式说明中的收件人
    //     Loader::import('PHPMailer',EXTEND_PATH);
    //     $mail = new \PHPMailer();
    //     // $mail = new PHPMailer();
    //     $mail->isSMTP();// 使用SMTP服务
    //     $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
    //     $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
    //     $mail->SMTPAuth = true;// 是否使用身份验证
    //     $mail->Username = $sendmail;//// 发送方的
    //     $mail->Password = $sendmailpswd;//客户端授权密码,而不是邮箱的登录密码！
    //     $mail->SMTPSecure = "ssl";// 使用ssl协议方式
    //     $mail->Port = 465;// 服务器端口 25 或者465 具体要看邮箱服务器支持
    //     $mail->setFrom($sendmail, $send_name);// 设置发件人信息，如邮件格式说明中的发件人，
    //     $mail->addAddress($toemail, $to_name);// 设置收件人信息，如邮件格式说明中的收件人，
    //     $mail->addReplyTo($sendmail, $send_name);// 设置回复人信息，指的是收件人收到邮件后，如果要回复，回复邮件将发送到的邮箱地址
    //     $mail->Subject = "找回密码";// 邮件标题

    //     $code=rand(100000,999999);
    //     session::set('email_code',$code);
    //     $mail->Body = "<b>【XXXX】您的注册验证码是：$code</b> <br/><br/> 如果非本人操作无需理会！". date('Y-m-d H:i:s');// 邮件正文
    //     $mail->AltBody = "【XXXX】您的注册验证码是：".$code.",如果非本人操作无需理会！";// 这里可以设置也可以隐藏掉,隐藏掉以后我的html的内容不能识别,打开后正常,所以这里我是打开的状态
    //     if (!$mail->send()) { // 发送邮件
    //         echo  $mail->ErrorInfo; // 输出错误信息
    //     } else {
    //         return "发送成功";
    //     }
    //     //DXHIRCSOGTADWWMO
    // }
    public function deleteRenderedVideo($RenderedVideoName=''){
        $data = $RenderedVideoName;
        $code = -1;
        $message = '';
        Db::table('rendered_videos')->delete($RenderedVideoName);
        $code = 200;
        // Db::table('rendered_videos')
        // ->where('rendered_video_name',$RenderedVideoName)
        // ->delete();
        return ['data'=>$data,'code'=>$code,'message'=>$message];        
    }
    public function deleteUploadedVideo($UploadedVideoName=''){
        $data = $UploadedVideoName;
        $code = -1;
        $message = '';
        Db::table('rendered_videos')
        ->where('uploaded_video_name',$UploadedVideoName)
        ->delete();
        Db::table('uploaded_videos')->delete($UploadedVideoName);
        $code = 200;
        return ['data'=>$data,'code'=>$code,'message'=>$message];        
    }
}