<?php
	namespace app\index\controller;
	use \think\Controller;
	use \think\captcha\Captcha;
	use \think\Db;
	class Login extends Controller
	{
		//渲染页面
		public function index()
		{
			$captcha = new \think\captcha\Captcha();
			return $this->fetch();
		}

		//注册账号
		public function registe()
		{
			//获取学院信息
			$data = Db::table('institute')->order("sort acs")->select();
			$this->assign('insData',$data);
			return $this->fetch();
		}

		//二级联动，根据院校获取专业
		public function getClass()
		{
			$iid = input("post.iid");
			$data = Db::table('class')->where("iid=".$iid)->order("sort acs")->column("id,title");
			return $data;
		}

		//注册检测
		public function checkReg()
		{
			$icon = "";
			//头像检测
			if(count($_FILES)){
				$file = request()->file("icon");
				$info = $file->validate(['ext'=>'jpg,png,gif'])->move("../uploads");
				//图片的完整路径
				if($info){
					$icon = "uploads\\".$info->getSaveName();
				} else {
					$this->error($file->getError());
				}
		    }

		    //密码判断
		    
		    $data = input("post.");
		    $data['icon'] = $icon;
		    $data['pwd'] = md5($data['pwd1']);
		    unset($data['pwd1']);
		    unset($data['pwd2']);
		    dump($data);
		}

		//获取账号数据
		public function getAct()
		{
			$act = trim(input("get.act"))??0;
			$isReg = Db::table("user")->where("act=".$act)->find();

			//存在账号
			if(is_null($isReg))
			{
				if(strlen($act) < 10) {
					exit(json_encode(array("code"=>0,"msg"=>"学号输入错误")));
				}
				exit(json_encode(array("code"=>1,"msg"=>"允许注册")));
			} else {
				exit(json_encode(array("code"=>0,"msg"=>"账号已存在")));
			}
		}

		//登陆检测
		public function check()
		{
			$username = trim(input('post.act'));
			$pwd = trim(input('post.pwd'));
			$verifycode = trim(input('post.verify'));

			if($username == ''){
				exit(json_encode(array('code'=>1,'msg'=>'用户名不能为空')));
			}
			if($pwd == ''){
				exit(json_encode(array('code'=>1,'msg'=>'密码不能为空')));
			}
			if($verifycode == ''){
				exit(json_encode(array('code'=>1,'msg'=>'请输入验证码')));
			}
			// 验证验证码
			if(!captcha_check($verifycode)){
				exit(json_encode(array('code'=>1,'msg'=>'验证码错误')));
			}
		}
	}