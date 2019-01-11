<?php
namespace app\index\controller;
use app\index\controller\Base;
use Db;
class Pub extends Base
{
	//修改信息
    public function modify()
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
		$data = Db::table('class')->where("iid=".$iid)->order("sort acs")->column("sort,title");
		return $data;
	}


	//个人信息修改
	public function changeMes(){
		$data = (array)input("post.");
		if(isset($_FILES['icon']['name'])) {
			$file = request()->file("icon");
			$info = $file->move("./author");
			$icon = "/author\\".$info->getSaveName();
		    $data['icon'] = $icon;
		    $_SESSION['think']['User']['icon'] = $data['icon'];
		}
		$data['age'] = (int)$data['age'];
		$id = $_SESSION['think']['User']['id'];
		//dump(session());
		Db::table('user')->where("id=".$id)->update($data);
		$this->redirect("Index/index");
	}

	//安全管理界面
	public function secure(){
		return $this->fetch();
	}

	//验证密码
	public function checkPwd(){
		$pwd = md5(input("post.oldPwd"));
		if($pwd == $_SESSION['think']['User']['pwd']){
			//密码正确
			echo 1;
		} else {
			//密码错误
			echo 0;
		}
	}
	//修改重要信息
	public function changePwd(){
		$data = input('post.');
		$data['pwd'] = md5(input("post.pwd"));
		$id = $_SESSION['think']['User']['id'];
		session("User",null);
		Db::table("user")->where('id='.$id)->update($data);
	}

	//修改图片
	public function changeImg(){

	}
}
