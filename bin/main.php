<?php
require_once("config.php");
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_server("127.0.0.1", 9501); 

//监听连接进入事件
$serv->on('connect', function ($serv, $fd) {  
		    echo "Client: Connect.\n";
			});

//监听数据接收事件
$serv->on('receive', function ($serv, $fd, $from_id, $data) use($logger) {
			$logger("DEBUG", "收到来自:".$serv->host.":".$serv->port.'的链接请求');
		    $serv->send($fd, "Server: ".$data);
			});

//监听连接关闭事件
$serv->on('close', function ($serv, $fd) use($logger) {
			$logger("DEBUG", "服务关闭");
		    echo "Client: Close.\n";
			});

//启动服务器
$logger("DEBUG","服务器启动");
$serv->start(); 
?>
