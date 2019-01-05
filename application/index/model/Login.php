<?php
namespace app\index\model;

use think\Model;

class LoginModel extends Model
{
	protected $pk = 'id';
	protected $table = 'User';
	//登陆检测
	public function checkAp($act,$pwd)
	{
		$where['act'] = $act;
		$where['pwd'] = $pwd;
		return User::where($where)->count();
	}
}