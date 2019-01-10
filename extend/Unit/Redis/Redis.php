<?php
/**
 * Created by PhpStorm.
 * Power by Mikkle
 * QQ:776329498
 * Date: 2017/7/14
 * Time: 15:36
 */

namespace Unit\Redis;


/**
 * Class Redis redis操作类，集成了redis常用的操作方法
 * Power by Mikkle
 * @time,2017/7/14
 */

class Redis{
    public $redisObj = null;//redis实例化时静态变量

    static protected $instance;
    protected $sn;
    protected $index;

    public function __construct($options=[]) {
        $host = trim(isset($options["host"]) ? $options["host"] : '127.0.0.1');
        $port = 6379;
        $auth = trim(isset($options["auth"]) ? $options["auth"] :"root" );
        $index = trim(isset($options["index"]) ? $options["index"] : 0 );
        if (!is_integer($index) && $index>16) {
            $index = 0;
        }
        $sn = md5("{$host}{$port}{$auth}{$index}");
        $this->sn = $sn;
        if (!isset($this->redisObj[$this->sn])){
            $this->redisObj[$this->sn]=new \Redis();
            $this->redisObj[$this->sn]->connect($host,$port);
            $this->redisObj[$this->sn]->auth($auth);
            $this->redisObj[$this->sn]->select($index);
        }
        $this->redisObj[$this->sn]->sn=$sn;
        $this->index = $index;
        return ;
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Redis
     */
    public static function instance($options=[])
    {
        return  new Redis($options);
    }




    public function setExpire($key,$time=0){

        if(!$key){
            return false;
        }
        switch (true){
            case ($time==0):
                return $this->redisObj[$this->sn]->expire($key,0);
                break;
            case ($time>time()):
                $this->redisObj[$this->sn]->expireAt($key,$time);
                break;
            default:
                return $this->redisObj[$this->sn]->expire($key,$time);
        }
    }


    /*------------------------------------start 1.string结构----------------------------------------------------*/
    /**
     * 增，设置值  构建一个字符串
     * @param string $key KEY名称
     * @param string $value  设置值
     * @param int $timeOut 时间  0表示无过期时间
     * @return true【总是返回true】
     */
    public function set($key, $value, $timeOut=0) {
        $setRes =  $this->redisObj[$this->sn]->set($key, $value);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($key, $timeOut);
        return $setRes;
    }

    /**
     * 查，获取 某键对应的值，不存在返回false
     * @param $key ,键值
     * @return bool|string ，查询成功返回信息，失败返回false
     */
    public  function get($key){
        $setRes =  $this->redisObj[$this->sn]->get($key);//不存在返回false
        if($setRes === 'false'){
            return false;
        }
        return $setRes;
    }
    /*------------------------------------1.end string结构----------------------------------------------------*/





    /*------------------------------------2.start list结构----------------------------------------------------*/
    /**
     * 增，构建一个列表(先进后去，类似栈)
     * @param String $key KEY名称
     * @param string $value 值
     * @param $timeOut |num  过期时间
     */
    public function lpush($key,$value,$timeOut=0){
//          echo "$key - $value \n";
        $re = $this->redisObj[$this->sn]->LPUSH($key,$value);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($key, $timeOut);
        return $re;
    }

    /**
     * 增，构建一个列表(先进先去，类似队列)
     * @param string $key KEY名称
     * @param string $value 值
     * @param $timeOut |num  过期时间
     */
    public function rpush($key,$value,$timeOut=0){
//          echo "$key - $value \n";
        $re = $this->redisObj[$this->sn]->RPUSH($key,$value);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，获取所有列表数据（从头到尾取）
     * @param string $key KEY名称
     * @param int $head  开始
     * @param int $tail     结束
     */
    public function lranges($key,$head,$tail){
        return $this->redisObj[$this->sn]->lrange($key,$head,$tail);
    }

    /**
     * Power by Mikkle
     * QQ:776329498
     * @param $key
     * @return mixed
     */

    public function rpop($key){
        return $this->redisObj[$this->sn]->rPop($key);
    }
    public function lpop($key){
        return $this->redisObj[$this->sn]->lpop($key);
    }

    /*------------------------------------2.end list结构----------------------------------------------------*/






    /*------------------------------------3.start set结构----------------------------------------------------*/

    /**
     * 增，构建一个集合(无序集合)
     * @param string $key 集合Y名称
     * @param string|array $value  值
     * @param int $timeOut 时间  0表示无过期时间
     * @return
     */
    public function sadd($key,$value,$timeOut = 0){
        $re = $this->redisObj[$this->sn]->sadd($key,$value);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，取集合对应元素
     * @param string $key 集合名字
     */
    public function smembers($key){
        $re =   $this->redisObj[$this->sn]->exists($key);//存在返回1，不存在返回0
        if(!$re) return false;
        return $this->redisObj[$this->sn]->smembers($key);
    }


    /**
     * 查，取集合中某一个值，判断是否存在
     * @param string $key 集合名字
     * @param string member 对应值
     */
    public function has($key,$member){
        $re =   $this->redisObj[$this->sn]->exists($key);//存在返回1，不存在返回0
        if(!$re) return false;
        return $this->redisObj[$this->sn]->scontains($key,$member);
    }

    /*------------------------------------3.end  set结构----------------------------------------------------*/


    /*------------------------------------4.start sort set结构----------------------------------------------------*/
    /*
     * 增，改，构建一个集合(有序集合),支持批量写入,更新
     * @param string $key 集合名称
     * @param array $score_value key为scoll, value为该权的值
     * @return int 插入操作成功返回插入数量【,更新操作返回0】
     */
    public function zadd($key,$score_value,$timeOut =0){
        if(!is_array($score_value)) return false;
        $a = 0;//存放插入的数量
        foreach($score_value as $score=>$value){
            $re =  $this->redisObj[$this->sn]->zadd($key,$score,$value);//当修改时，可以修改，但不返回更新数量
            $re && $a+=1;
            if ($timeOut > 0) $this->redisObj[$this->sn]->expire($key, $timeOut);
        }
        return $a;
    }

    /**
     * 查，有序集合查询，可升序降序,默认从第一条开始，查询一条数据
     * @param $key ,查询的键值
     * @param $min ,从第$min条开始
     * @param $max，查询的条数
     * @param $order ，asc表示升序排序，desc表示降序排序
     * @return array|bool 如果成功，返回查询信息，如果失败返回false
     */
    public function zrange($key,$min = 0 ,$num = 1,$order = 'desc'){
        $re =   $this->redisObj[$this->sn]->exists($key);//存在返回1，不存在返回0
        if(!$re) return false;//不存在键值
        if('desc' == strtolower($order)){
            $re = $this->redisObj[$this->sn]->zrevrange($key,$min ,$min+$num-1);
        }else{
            $re = $this->redisObj[$this->sn]->zrange($key,$min ,$min+$num-1);
        }
        if(!$re) return false;//查询的范围值为空
        return $re;
    }

    /**
     * 返回集合key中，成员member的排名
     * @param $key，键值
     * @param $member，scroll值
     * @param $type ,是顺序查找还是逆序
     * @return bool,键值不存在返回false，存在返回其排名下标
     */
    public function zrank($key,$member,$type = 'desc'){
        $type = strtolower(trim($type));
        if($type == 'desc'){
            $re = $this->redisObj[$this->sn]->zrevrank($key,$member);//其中有序集成员按score值递减(从大到小)顺序排列，返回其排位
        }else{
            $re = $this->redisObj[$this->sn]->zrank($key,$member);//其中有序集成员按score值递增(从小到大)顺序排列，返回其排位
        }
        if(!is_numeric($re)) return false;//不存在键值
        return $re;
    }

    /**
     * 返回名称为key的zset中score >= star且score <= end的所有元素
     * @param $key
     * @param $member
     * @param $star，
     * @param $end,
     * @return array
     */
    public function zrangbyscore($key,$star,$end){
        return $this->redisObj[$this->sn]->ZRANGEBYSCORE($key,$star,$end);
    }

    /**
     * 返回名称为key的zset中元素member的score
     * @param $key
     * @param $member
     * @return string ,返回查询的member值
     */
    function zscore($key,$member){
        return $this->redisObj[$this->sn]->ZSCORE($key,$member);
    }
    /*------------------------------------4.end sort set结构----------------------------------------------------*/




    /*------------------------------------5.hash结构----------------------------------------------------*/
    /**
     * 增，以json格式插入数据到缓存,hash类型
     * @param $redis_key |array , $redis_key['key']数据库的表名称;$redis_key['field'],下标key
     * @param $token,该活动的token，用于区分标识
     * @param $id,该活动的ID，用于区分标识
     * @param $data|array ，要插入的数据,
     * @param $timeOut ，过期时间，默认为0
     * @return $number 插入成功返回1【,更新操作返回0】
     */
    public function hset_json($redis_key,$token,$id,$data,$timeOut = 0){
        $redis_table_name = $redis_key['key'].':'.$token;           //key的名称
        $redis_key_name = $redis_key['field'].':'.$id;              //field的名称，表示第几个活动
        $redis_info = json_encode($data);                           //field的数据value，以json的形式存储
        $re = $this->redisObj[$this->sn] -> hSet($redis_table_name,$redis_key_name,$redis_info);//存入缓存
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($redis_table_name, $timeOut);//设置过期时间
        return $re;
    }

    /**
     * 查，json形式存储的哈希缓存，有值则返回;无值则查询数据库并存入缓存
     * @param $redis,$redis['key'],$redis['field']分别是hash的表名称和键值
     * @param $token,$token为公众号
     * @param $token,$id为活动ID
     * @return bool|array, 成功返回要查询的信息，失败或不存在返回false
     */
    public function hget_json($redis_key,$token,$id){
        $re =   $this->redisObj[$this->sn]->hexists($redis_key['key'].':'.$token,$redis_key['field'].':'.$id);//返回缓存中该hash类型的field是否存在
        if($re){
            $info = $this->redisObj[$this->sn]->hget($redis_key['key'].':'.$token,$redis_key['field'].':'.$id);
            $info = json_decode($info,true);
        }else{
            $info = false;
        }
        return $info;
    }

    public function hset($redis_key,$name,$data,$timeOut=0){
        $re = $this->redisObj[$this->sn]  -> hset($redis_key,$name,$data);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($redis_key, $timeOut);
        return $re;
    }



    /**
     * 增，普通逻辑的插入hash数据类型的值
     * @param $key ,键名
     * @param $data |array 一维数组，要存储的数据
     * @param $timeOut |num  过期时间
     * @return $number 返回OK【更新和插入操作都返回ok】
     */
    public function hmset($key,$data,$timeOut=0){
        $re = $this->redisObj[$this->sn]  -> hmset($key,$data);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($key, $timeOut);
        return $re;
    }

    /**
     * 查，普通的获取值
     * @param $key,表示该hash的下标值
     * @return array 。成功返回查询的数组信息，不存在信息返回false
     */
    public function hval($key){
        $re =   $this->redisObj[$this->sn]->exists($key);//存在返回1，不存在返回0
        if(!$re) return false;
        $vals = $this->redisObj[$this->sn] -> hvals($key);
        $keys = $this->redisObj[$this->sn] -> hkeys($key);
        $re = array_combine($keys,$vals);
        foreach($re as $k=>$v){
            if(!is_null(json_decode($v))){
                $re[$k] = json_decode($v,true);//true表示把json返回成数组
            }
        }
        return $re;
    }

    /**
     *
     * @param $key
     * @param $filed
     * @return bool|string
     */
    public function hget($key,$filed = []){
        if(empty($filed)){
            $re = $this->redisObj[$this->sn]->hgetAll($key);
        }elseif(is_string($filed)){
            $re = $this->redisObj[$this->sn]->hget($key,$filed);
        }elseif(is_array($filed)){

            $re = $this->redisObj[$this->sn]->hMget($key,$filed);
        }
        if(!$re){
            return false;
        }
        return $re;
    }

    public function hsetAll($redis_key,$name,$data,$timeOut=0){
        $re = $this->redisObj[$this->sn]  -> hset($redis_key,$name,$data);
        if ($timeOut > 0) $this->redisObj[$this->sn]->expire($redis_key, $timeOut);
        return $re;
    }

    public function hdel($redis_key,$name){
        $re = $this->redisObj[$this->sn]  -> hdel($redis_key,$name);
        return $re;
    }


    /*------------------------------------end hash结构----------------------------------------------------*/




    /*------------------------------------其他结构----------------------------------------------------*/
    /**
     * 设置自增,自减功能
     * @param $key ，要改变的键值
     * @param int $num ，改变的幅度，默认为1
     * @param string $member ，类型是zset或hash，需要在输入member或filed字段
     * @param string $type，类型，default为普通增减,还有:zset,hash
     * @return bool|int 成功返回自增后的scroll整数，失败返回false
     */
    public function incre($key,$num = 1,$member = '',$type=''){
        $num = intval($num);
        switch(strtolower(trim($type))){
            case "zset":
                $re = $this->redisObj[$this->sn]->zIncrBy($key,$num,$member);//增长权值
                break;
            case "hash":
                $re = $this->redisObj[$this->sn]->hincrby($key,$member,$num);//增长hashmap里的值
                break;
            default:
                if($num > 0){
                    $re = $this->redisObj[$this->sn]->incrby($key,$num);//默认增长
                }else{
                    $re = $this->redisObj[$this->sn]->decrBy($key,-$num);//默认增长
                }
                break;
        }
        if($re) return $re;
        return false;
    }


    /**
     * 清除缓存
     * @param int $type 默认为0，清除当前数据库；1表示清除所有缓存
     */
    function flush($type = 0){
        if($type) {
            $this->redisObj[$this->sn]->flushAll();//清除所有数据库
        }else{
            $this->redisObj[$this->sn]->flushdb();//清除当前数据库
        }
    }

    /**
     * 检验某个键值是否存在
     * @param $keys ，键值
     * @param string $type，类型，默认为常规
     * @param string $field。若为hash类型，输入$field
     * @return bool
     */
    public function exists($keys,$type = '',$field=''){
        switch(strtolower(trim($type))){
            case 'hash':
                $re = $this->redisObj[$this->sn]->hexists($keys,$field);//有返回1，无返回0
                break;
            default:
                $re = $this->redisObj[$this->sn]->exists($keys);
                break;
        }
        return $re;
    }

    /**
     * 删除缓存
     * @param string|array $key，键值
     * @param $type，类型，默认为常规，还有hash,zset
     * @param string $field,hash=>表示$field值，set=>表示value,zset=>表示value值，list类型特殊暂时不加
     * @return int | ，返回删除的个数
     */
    public function delete($key,$type="default",$field = ''){
        switch(strtolower(trim($type))){
            case 'hash':
                $re = $this->redisObj[$this->sn]->hDel($key,$field);//返回删除个数
                break;
            case 'set':
                $re = $this->redisObj[$this->sn]->sRem($key,$field);//返回删除个数
                break;
            case 'zset':
                $re = $this->redisObj[$this->sn]->zDelete($key,$field);//返回删除个数
                break;
            default:
                $re = $this->redisObj[$this->sn]->del($key);//返回删除个数
                break;
        }
        return $re;
    }

    //日志记录
    public function logger($log_content,$position = 'user')
    {
        $max_size = 1000000;   //声明日志的最大尺寸1000K

        $log_dir = './log';//日志存放根目录

        if(!file_exists($log_dir)) mkdir($log_dir,0777);//如果不存在该文件夹，创建

        if($position == 'user'){
            $log_filename = "{$log_dir}/User_redis_log.txt";  //日志名称
        }else{
            $log_filename = "{$log_dir}/Wap_redis_log.txt";  //日志名称
        }

        //如果文件存在并且大于了规定的最大尺寸就删除了
        if(file_exists($log_filename) && (abs(filesize($log_filename)) > $max_size)){
            unlink($log_filename);
        }

        //写入日志，内容前加上时间， 后面加上换行， 以追加的方式写入
        file_put_contents($log_filename, date('Y-m-d_H:i:s')." ".$log_content."\n", FILE_APPEND);
    }


    function __destruct()
    {
      //  $this->redisObj[$this->sn]->close();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        call_user_func_array([$this->redisObj[$this->sn], $method], $args);
    }


}