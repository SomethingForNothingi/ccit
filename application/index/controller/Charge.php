<?php
namespace app\index\controller;
use app\index\controller\Base;
use Db;
class Charge extends Base
{
    public function index()
    {
    	//查询余额
    	$wallet = Db::table('wallet')->where('uid='.session('User.act'))->find();
    	$this->assign('wallet',$wallet);
        return $this->fetch();
    }
    //校园卡充值
    public function xyk()
    {
    	$data = (array)input('post.');
    	$wallet = Db::table('wallet')->where('uid='.session('User.act'))->find();
    	if($data['zf'] == 'cb1')
    	{
    		//密码判断
    		if(md5($data['pwd1']) != session("User.pwd"))
    		{
    			exit(json_encode(array('code'=>-1,'msg'=>'密码错误')));
    		} 
    		else if($data['cz'] > $wallet['cb']){		//余额判断
	    		exit(json_encode(array('code'=>-1,'msg'=>'C币余额不足')));
	    	}
	    	else if($data['cz'] <= 0 || !is_numeric((float)$data['cz']))
	    	{
	    		//验证输入是否未数字
	    		exit(json_encode(array('code'=>-1,'msg'=>'请输入正确金额')));
	    	} 
	    	else {
	    		//充值成功，数据交换
	    		$save['cb'] = $wallet['cb'] - $data['cz'];
	    		$save['xyk'] = $wallet['xyk'] + $data['cz'];
	    		Db::table('wallet')->where('uid='.session('User.act'))->update($save);
	    		exit(json_encode(array('code'=>1,'msg'=>'充值成功')));
	    	}
    	}
    }

    //校园网充值
    public function xyw()
    {
    	$data = (array)input('post.');
    	$wallet = Db::table('wallet')->where('uid='.session('User.act'))->find();
    	//充值花费，每个月20￥
    	$cost = (int)$data['czTime'] * 20;
    	if($data['xyw'] == 'cb2')
    	{
    		//密码判断
    		if(md5($data['pwd2']) != session("User.pwd"))
    		{
    			exit(json_encode(array('code'=>-1,'msg'=>'密码错误')));
    		}
    		else if($wallet['cb'] < $cost)
    		{	//余额判断
    			exit(json_encode(array('code'=>-1,'msg'=>'余额不足')));
    		}
    		else
    		{
	    		//充值成功，数据交换
	    		$save['cb'] = $wallet['cb'] - $cost;
	    		//校园网办理,未办理过校园网或者校园网已过期
	    		if($this->netOutTime($wallet['xyw']))
	    		{
	    			$save['xyw'] =  date("Y-m-d",strtotime("+".$data['czTime']." month"));
	    		}
	    		else
	    		{
	    			//校园网未到期
	    			$save['xyw'] =  date("Y-m-d",strtotime("+".$data['czTime']." month",strtotime($wallet['xyw'])));
	    		}
	    		Db::table('wallet')->where('uid='.session('User.act'))->update($save);
	    		exit(json_encode(array('code'=>1,'msg'=>'缴费成功')));
	    	}
    	}
    	if($data['xyw'] == 'xyk2')
    	{
    		//密码判断
    		if(md5($data['pwd2']) != session("User.pwd"))
    		{
    			exit(json_encode(array('code'=>-1,'msg'=>'密码错误')));
    		}
    		else if((int)$wallet['xyk'] < $cost)
    		{	//余额判断
    			exit(json_encode(array('code'=>-1,'msg'=>'余额不足')));
    		}
    		else
    		{

	    		//充值成功，数据交换
	    		$save['xyk'] = $wallet['xyk'] - $cost;
	    		//是否办理了校园网
	    		if($this->netOutTime($wallet['xyw']))
	    		{
	    			//到期或未办理
	    			$save['xyw'] =  date("Y-m-d",strtotime("+".$data['czTime']." month"));
	    		}
	    		else
	    		{
	    			//校园网未到期
	    			$save['xyw'] =  date("Y-m-d",strtotime("+".$data['czTime']." month",strtotime($wallet['xyw'])));
	    		}
	    		Db::table('wallet')->where('uid='.session('User.act'))->update($save);
	    		exit(json_encode(array('code'=>1,'msg'=>'缴费成功')));
	    	}
    	}
    }

    //检测校园网超时
    private function netOutTime($time)
    {
    	//未办理校园网
    	if(empty($time)) {
			return true;
		}
		//未过期
		if(strtotime($time) >= time()){
			return false;
		}
		//已过期
		if(strtotime($time) < time()) {
			return true;
		}
    }

}
