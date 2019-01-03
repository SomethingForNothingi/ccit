<?php
	namespace app\index\controller;
	use app\index\controller\Common;
	use think\Controller;
	//公共继承的基类,主要用于检测用户是否登陆
	class Base extends Common 
	{
		//构造方法用于验证session是否存在
		public function __construct()
		{
			//不添加会导致继承问题
			parent::__construct();

			if(empty(session("User"))){
				//登陆验证
				$this->redirect("/index.php/index/login/index");
			} else {
				//解决通过ajax提交数据前端页面数据不更新情况
				$act = $_SESSION['think']['User']['act'];
				session("User",null);
				session("User",$this->getUserMes($act));
			}
		}

	}
