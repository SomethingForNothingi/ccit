<?php
	namespace app\index\controller;

	use think\Controller;
	//公共继承的基类,主要用于检测用户是否登陆
	class Base extends Controller 
	{
		//构造方法用于验证session是否存在
		public function __construct()
		{
			//不添加会导致继承问题
			parent::__construct();
			if(empty(session("User"))){
				//登陆验证
				$this->redirect("/index.php/index/login/index");
			}
		}

	}
