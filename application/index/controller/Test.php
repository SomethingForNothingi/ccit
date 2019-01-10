<?php
/**
 * @author 宋文博
 * 
 */
namespace app\index\controller;
use app\index\controller\Base;
use Db;
use Unit\Redis\Redis;
//use \think\Facade\Cache;
//use think\cache\driver\Redis;
class Test extends Base
{
    public function index()
    {

    }
    public function test()
    {
    	$redis = new Redis();
    	$redis->sadd("article1","1");
    	dump($redis->smembers("article1"));
    }
}