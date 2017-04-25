<?php
require_once("config.php");
//创建Server对象，监听 127.0.0.1:9501端口
$serv = new swoole_http_server("127.0.0.1", 9501); 

$serv->set(
		array(
			'task_worker_num' => 20,
			'log_file' => getenv("PWD")."/../log/spider.log",
			'log_level' => 0,
			'daemonize' => 1,
			)
		);

//监听连接进入事件
/*$serv->on('connect', function ($serv, $fd) {  
		    echo "Client: Connect.\n";
			});
*/
//监听数据接收事件
$serv->on('request', function ($data, $resp) use($logger) {
			global $serv;
			$data = $data->get;

			if (empty($data)) { $resp->end("request must get"); return ; }
			$id = $data['id'];

			if (!is_numeric($id)) { $resp->end("must int".$id); return ;}
			$id = trim($id);

			$task_data['id'] = $id;
			$task_data['type'] = SOURCE_TYPE_zzba;
			if (isset($data['type'])) {
				$task_data['type'] = $data['type'];
			}

			$logger("DEBUG", "收来自{$serv->host}:{$serv->port}链接请求，数据为type:{$task_data['type']},id:{$id}");

			$ret = "";
			$status = $serv->stats();
			if (isset($status['tasking_num'])) { $ret .= ";tasking_num:<".$status['tasking_num'].">"; }
			$ret .= "source_id:{$id}is pushed task;";
			$resp->end($ret); 

			$serv->task($task_data);
			unset($serv);
			});

/*//监听连接关闭事件
$serv->on('close', function ($serv, $fd) use($logger) {
			$logger("DEBUG", "服务关闭");
		    echo "Client: Close.\n";
			});
*/
$serv->on('task', function($serv, $task_id, $from_id, $data) use($logger){
		$zzd = new Zhongziso($data['type']); 
		$ret = $zzd->scrawl($data['id']);
		if (!empty($ret) AND !empty($ret['hash_value']) AND !empty($ret['title'])) {
			global $config;
			$curl = new Curl();
			$bak = $curl->rapid($config['sync_url'], 'POST', json_encode($ret));
			$bak = json_decode($bak, true);
			if ($bak['err'] != 0) {
				$logger("ERROR", "同步失败,原因：".$bak['msg']."\n数据：".print_r($ret, true));
			}
			unset($config);
		}
		$serv->finish($ret);
		});

$serv->on('finish', function($serv, $task_id, $data) use($logger){
			if (!empty($data['source_id'])) { echo "source_id:".$data['source_id']; }
			echo ";task_id:" . $task_id . "is end" . PHP_EOL;	
		});

//启动服务器
$logger("DEBUG","服务器启动");
$serv->start(); 
?>
