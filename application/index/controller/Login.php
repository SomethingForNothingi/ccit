<?php
	namespace app\index\controller;
	use \think\Controller;
	
	class Login extends Controller
	{
		//渲染页面
		public function index()
		{
			return $this->fetch();
		}


	}