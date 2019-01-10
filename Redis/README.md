安装对应版本的redis扩展服务（注意NTS 还是 TS） non Tread Safe X86 x64

修改php配置文件
; php_redis

extension=php_igbinary.dll

extension=php_redis.dll


下载文件  执行.\redis-server .\redis.windows.conf