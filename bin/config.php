<?php
$config = array(
		"level" => "DEBUG",
		"log_path" => "/home/ben/work/swoole/spider/log",
		"sync_url" => "http://torrent.zengbingo.com/Home/Api/sync_magnet/sign/9BF4D5BC9A62",
		);

define('SOURCE_TYPE_ZHONGZISO', 1);

require_once("log.php");
require_once("phpQuery/QueryList.php");
require_once("mgr.php");
require_once("curl.php");
?>
