<?php
	namespace app\index\controller;
	use app\index\controller\Common;
	use \think\captcha\Captcha;
	use \think\Db;
	class Login extends Common
	{
		//渲染页面
		public function index()
		{
			return $this->fetch();
		}

		//退出
		public function logout()
		{
			session("User",null);
			return $this->fetch('Login/index');
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
			$file = request()->file("icon");
			$info = $file->move("./uploads");
			$icon = "/uploads\\".$info->getSaveName();
		    $data = input("post.");
		    $data['icon'] = $icon;
		    $data['pwd'] = md5($data['pwd1']);
		    unset($data['pwd1']);
		    unset($data['pwd2']);
		    //账号是否存在于数据库
		    $have = Db::table("user")->where("act=".$data['act'])->count();
		    if($have == 0){
		    	//不存在添加
			    Db::table("user")->insert($data);
			    $this->redirect("login/index");
		    } else {
		    	//存在退回注册
		    	$this->redirect("login/registe");
		    }
		}

		//获取账号数据
		public function getAct()
		{
			$act = trim(input("get.act"))??0;
			$isReg = Db::table("user")->where("act=".$act)->find();
			//存在账号
			if(is_null($isReg)){
				if(strlen($act) < 10) {
					exit(json_encode(array("code"=>0,"msg"=>"学号输入错误")));
				} else{
					//注册成功，待添加新功能
					exit(json_encode(array("code"=>1,"msg"=>"允许注册")));
				}
			} else if($act == 0){
				exit(json_encode(array("code"=>0,"msg"=>"请输入学号")));
			}else{
				exit(json_encode(array("code"=>0,"msg"=>"学号已存在")));
			}
		}

		//登陆检测
		public function check()
		{
			//$captcha = new Captcha();
			$act = input('post.act');
			$pwd = md5(input('post.pwd'));
			$verifycode = trim(input('post.verify'));
			//检测账号密码
			$ck = $this->checkAp($act,$pwd);
			if(Db::table("user")->where("act=".$act)->count()==0){
				exit(json_encode(array("code"=>0,"msg"=>"该账号尚未注册")));
			}else if($ck != 1){
				exit(json_encode(array("code"=>0,"msg"=>"密码输入错误")));
			} else if(!captcha_check($verifycode)){		//检测验证码
				exit(json_encode(array("code"=>0,"msg"=>"验证码错误")));
			}else {
				//设置session
				session("User",$this->getUserMes($act));
				exit(json_encode(array("code"=>1,"msg"=>"登陆成功")));
			}
		}
	}