<?php
	namespace app\index\controller;
	use \think\Controller;
	use \think\Db;
	
	//公共方法类
	class Common extends Controller
	{
		public function __construct(){
			parent::__construct();
		}
		//通过id获取title
		public function getTitleById($table,$id) {
			return Db::table($table)->where("id=".$id)->value("title");
		}

		public function getFieldByField($table,$where,$val) {
			return Db::table($table)->where($where)->value($val);
		}

		//检测账号密码
		public function checkAp($act,$pwd){
			$where['act'] = $act;
			$where['pwd'] = $pwd;
			return Db::table("User")->where($where)->count();
		}

		//获取账号信息
		public function getUserMes($act){
			$data = Db::table("User")->where('act='.$act)->find();
			//添加学校班级信息
			$where['iid'] = $data['iid'];
			$where['id'] = $data['cid'];
			$data['ctitle'] = Db::table("class")->where($where)->value('title');
			$data['ititle'] = Db::table("institute")->where('id='.$data['iid'])->value('title');
			return $data;
		}
	}