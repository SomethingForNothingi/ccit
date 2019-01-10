<?php
/**
 * @author 宋文博
 * 使用redis缓存，减轻服务器压力
 * 从而达到分布式开发效果
 */
namespace app\index\controller;
use app\index\controller\Base;
use Db;
use Unit\Redis\Redis;
class Bbs extends Base
{
    public function index()
    {
    	//未加载前最大的文章数量
    	$limit = 10;
    	//获取文章
    	$article = Db::table('article')->order(['hot desc','commitTime desc'])->limit($limit)->select();
    	//传递到前端
    	$this->assign('article',$article);
    	//获取总数量，计算可更新次数
    	$count = Db::table("article")->count();
    	$page = floor($count / $limit)-1;
    	$this->assign('newsPage',$page);
        $this->assign("uid",session("User.id"));
    	//渲染
        return $this->fetch();
    }

    //流加载新闻
    public function load()
    {
    	$curPage = input("post.curPage");
    	$low = $curPage * 10;
    	$data = Db::table('article')->order(['hot desc','commitTime desc'])->limit($low,10)->select();
        $this->assign("uid",session("User.id"));
        $this->assign('article',$data);
    	//返回最新数据
    	return $this->fetch();
    }

    //热度算法 hackernews
    /**
     * @param $p为点赞数+评论数*1.5
     * @param $t为文章发表时间，单位为天
     * @param 1.8为重力因子
     */
    public function HackerNews($p,$t)
    {
        return ($p-1)/pow(($t+2),1.8);
    }

    //将点赞信息全部存储到redis中，id为Praise_id
    public function praise()
    {
        $redis = new Redis();
        $rev = input('post.');
        $data['pNum'] = Db::table('article')->where('id='.$rev['id'])->value('pNum');
        //取消点赞
        if($rev['z'] == 'on')
        {
            $data['pNum'] -= 1;
            //数据库点赞人数减少
            Db::table('article')->where('id='.$rev['id'])->update($data);
            //使用redis list列表来存储点赞人id，减轻服务器压力，读取加速
            //redis缓存清除用户
            $redis->delete("article".$rev['id'],"set",session("User.id"));
        }
        //点赞
        else
        {
            $data['pNum'] += 1;
            //数据库点赞人数增加
            Db::table('article')->where('id='.$rev['id'])->update($data);
            //进入redis缓存
            $redis->sadd("article".$rev['id'],session("User.id"));
        }
        exit(json_encode(array('pNum'=>$data['pNum'])));
    }

    // public function test()
    // {
    //     $redis = new Redis();
    //     //$redis -> sadd("article1","11");
    //     $this->assign("article","article1");
    //     return $this->fetch();
    // }
}