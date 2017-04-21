<?php
//zhongziso.ne
require_once("magnet.php");
class Zhongziso extends Magnet{
	public $kw = '';
	public $id_search = 0;
	public $url_search = "http://zhongziso.net/main-show-id-";
  	public function __construct()
	{
		parent::__construct();
		global $config;
		$this->logger = new App_Log($config['log_path'], $config['level']);;
	}

	public function scrawl($id)
	{
		$this->url_search = $this->url_search.$id;
		$reg = array(
				'link' => array('#MagnetLink', 'text'),
				'create_time' => array('.badge:eq(0)', 'text'),
				'file_size' => array('.badge:eq(1)', 'text'),
				'speed' => array('.badge:eq(2)', 'text'),
				'file_count' => array('.badge:eq(4)', 'text'),
				'hash_value' => array('.badge:eq(6)', 'text'),
				'tags' => array('.otherkey .badge', 'text'),
				'file_list' => array('td', 'text'),
				);
		$regRange = '.container .row .col-md-8';
		$i = 0;
		$flag = true;
		$data = array();
		do{
			$hj = new QueryList($this->url_search, $reg, $regRange, 'curl', 'UTF-8');
			if ($hj->html) {
				$this->logger->log("DEBUG", "获取id:{$id}");
				$data = $this->build_zhongziso($hj->jsonArr, $id);
				$this->logger->log("DEBUG", "id:{$id}数据生成成功!");
				break;
			}
			$i++;
			$this->logger->log("DEBUG", "尝试第{$i}次获取{$id}");
			if ($i > 3) {
				$this->logger->log("ERROR","尝获取失,败试{$i}次");
				break;
			} 
		}while(1);
		return $data;
	}

	public function build_zhongziso($jsonArr, $source_id)
	{
		if (count($jsonArr) < 3 OR empty($jsonArr[0]['link'])) { 
			$this->logger->log("ERROR", "link为空");
			return array(); 
		}
		$tmp = explode('&' , $jsonArr[0]['link']);
		if (count($tmp) < 2) { 
			$this->logger->log("ERROR","link解析失败");
			return array(); 
		}
		$hash = end(explode(":", $tmp[0]));
		$title = str_replace("dn=", "", $tmp[1]);
		$this->hash_value = $hash;
		$this->title = $title;
		$this->create_time = strtotime( $jsonArr[0]['create_time'] );
		$this->file_size = $this->resetFileSize( $jsonArr[0]['file_size'] );
		$this->file_count = $jsonArr[0]['file_count'];
		$this->tags = explode( "\n", $jsonArr[0]['tags'] );
		$this->file_list = $this->resetFileList(explode( "\n", $jsonArr[1]['file_list'] ));
		//LAST_MODIFY
		$data = $this->build();
		$data['source_id'] = $source_id;
		$data['source_type'] = SOURCE_TYPE_ZHONGZISO; 
		return $data;
	}


	private function resetFileList($file_list_raw){
		$total = count($file_list_raw);
		if ($total <  2) { return  array(); }
		unset($file_list_raw[ $total -1 ]);
		unset($file_list_raw[ $total -2 ]);
		$ret = array();
		foreach ($file_list_raw as $k => $v) 
		{
			if ( $k%2 == 0 ) {
				$tmp['name'] = $v;
			}else {
				$tmp['size'] = $this->resetFileSize($v);
				$ret[] = $tmp;
				unset($tmp);
			}
		} 
		return $ret;
	}

	private function resetFileSize($file_size_raw)
	{
		if (strstr($file_size_raw, "GB") OR strstr($file_size_raw, "G")) {
			$file_size_int = str_replace("G", "" ,$file_size_raw );
			$file_size_int = str_replace("B", "" ,$file_size_int );
			if (!is_numeric($file_size_int)) { return 0; }
			return $file_size_int * 1048576;
		}else if (strstr($file_size_raw, "MB") OR strstr($file_size_raw, "M")) {
			$file_size_int = str_replace("M", "" ,$file_size_raw );
			$file_size_int = str_replace("M", "" ,$file_size_int );
			if (!is_numeric($file_size_int)) { return 0; }
			return $file_size_int * 1024;
		}
		return $file_size_raw;
	}
}
?>
