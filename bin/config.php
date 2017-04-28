<?php
$config = array(
		"level" => "DEBUG",
		"log_path" => "/home/ben/work/swoole/spider/log",
		"sync_url" => "http://torrent.zengbingo.com/Home/Api/sync_magnet/sign/9BF4D5BC9A62",
		);

define('SOURCE_TYPE_ZHONGZISO', 1);
define('SOURCE_TYPE_bt60', 2);
define('SOURCE_TYPE_bt70', 3);
define('SOURCE_TYPE_zzba', 4);
define('SOURCE_TYPE_cililian', 5);

function logger($level, $msg, $id = 0, $type = 0){ echo "[$level][$id][$type] - ".date("Y-m-d H:i:s",time()).' -->'.$msg.PHP_EOL; }

//require_once("log.php");
require_once("phpQuery/QueryList.php");
require_once("mgr.php");
require_once("curl.php");
?>
