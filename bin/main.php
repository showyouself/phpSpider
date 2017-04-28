<?php
require_once("config.php");
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_http_server("127.0.0.1", 9501); 

$serv->set(
		array(
			'task_worker_num' => 10,
			'log_file' => getenv("PWD")."/../log/spider.log",
			'log_level' => 0,
			'daemonize' => 1,
			)
		);

//监听数据接收事件
$serv->on('request', function ($data, $resp){
			global $serv;
			do {
				$task_data = $data->get;
				$ret = "";

				if (empty($task_data['id']) OR empty($task_data['type'])) {
					logger("ERROR","from {$serv->host}:{$serv->port} request,empty id or type");
					break;
				} 

				logger("DEBUG","from {$serv->host}:{$serv->port} request", $task_data['id'], $task_data['type']);

				$status = $serv->stats();
				if (isset($status['tasking_num'])) { 
					$ret .= ";tasking_num:<".$status['tasking_num'].">";
					if ($status['tasking_num'] > 3) { break; }
				}

				$serv->task($task_data);
				$ret .= "source_id:{$task_data['id']}is pushed task;";

			}while(0);
			$resp->end($ret); 

			});

$serv->on('task', function($serv, $task_id, $from_id, $data){

			$zzd = new zhongziso($data['type'], $data['id']); 
			$ret = array();
			do {
				if (!$zzd->scrawl($ret)) { logger("ERROR", "crawl failed...T_T", $data['id'], $data['type']); break; }

				if (!$zzd->sync_post($ret)) { logger("ERROR","sync fail：".print_r($bak, true).";post data:".print_r($ret, true), $data['id'], $data['type']); break;}

				logger("SUCCESS", "post to api success!", $data['id'], $data['type']); 
			}while(0);

			$serv->finish($ret);

		});

$serv->on('finish', function($serv, $task_id, $data){
			logger("DEBUG","task_id:<" . $task_id . ">is end",$data['source_id'], $data['source_type']);	
		});

//启动服务器
logger("DEBUG", "服务器启动");

$serv->start(); 
?>
