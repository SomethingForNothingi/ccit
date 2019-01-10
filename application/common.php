<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


//检测数据长度
function checkLen($str,$len=5){
	if (mb_strlen($str) > $len) {
		$str = mb_substr($str, 0,$len);
		$str .= "..";
	}
	return $str;
}

//返回用户session
function userSession($param) {
	return $_SESSION["User"][$param];
}

//校园网验证
function xyw($time){
	if(empty($time)) {
		return "未办理校园网服务";
	}
	if(strtotime($time) >= time()){
		return "校园网剩余".ceil((strtotime($time)-time())/60/60/24).'天';
	}
	if(strtotime($time) < time()) {
		return "校园网已到期";
	}
}

//通过用户id获取用户名字
function getUserById($id,$val = 'nick') {
	return Db::table('User')->where('id='.$id)->value($val);
}


//使用扩展库的redis
function getRedis() {
	$redis = new Unit\Redis\Redis();
	return $redis;
}
//key为文章id，$val为用户id
function checkZ($key,$val) {
	$redis = getRedis();
	$key = "article".$key;
	$has = $redis->has($key,$val);
	if($has >= 1) {
		return "on";
	} else {
		return "off";
	}
}