<?php
	namespace app\index\controller;

	use \think\Controller;
	//公共继承的基类
	class Base extends Controller 
	{
		//构造方法用于验证session是否存在
		public function __construct()
		{
			echo "此处检验session";
		}

		public function index()
		{
			
		}
	}
