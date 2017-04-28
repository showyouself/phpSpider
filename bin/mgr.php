<?php
//zhongziso.ne
require_once("magnet.php");
class Zhongziso extends Magnet{
	//public $url_search = "http://zhongziso.net/main-show-id-";
	//public $url_search = "http://www.zzba.org/main-show-id-";
	public $url_tank = array(
			SOURCE_TYPE_bt70 => "http://www.70bt.com/main-show-id-",
			SOURCE_TYPE_bt60 => "http://www.60bt.com/main-show-id-",
			SOURCE_TYPE_zzba => "http://www.zzba.org/main-show-id-",
			SOURCE_TYPE_cililian => "http://www.cililian.com/main-show-id-",
			);
	public $type = SOURCE_TYPE_zzba;
	public $id = '';

  	public function __construct($type = SOURCE_TYPE_zzba, $id)
	{
		parent::__construct();
		global $config;
		$this->url_search = $this->url_tank[$type];
		$this->type = $type;
		$this->id = $id;
	}

	public function scrawl(&$data = array())
	{
		$this->url_search = $this->url_search.$this->id;
		$reg = array();
		$regRange = array();
		$this->selectReg($reg, $regRange);

		$i = 0;
		do{

			logger("DEBUG", "start scrawl url:".$this->url_search, $this->id, $this->type);
			$hj = new QueryList($this->url_search, $reg, $regRange, 'curl', 'UTF-8');
			if ($hj->html) {
				$data = $this->build_zhongziso($hj->jsonArr, $this->id);
				$data['source_id'] = $this->id;
				$data['source_type'] = $this->type; 
				logger("DEBUG", "build data success!",$this->id, $this->type);
				break;
			}
			$i++;
			logger("DEBUG", "scrawl failed：".print_r($hj, true)."\ntry again times :{$i}", $this->id, $this->type);
			if ($i > 3) {
				logger("ERROR", "scrawl all failed times :{$i}", $this->id, $this->type);
				break;
			} 
		}while(1);

		if (empty($data) OR empty($data['hash_value']) OR empty($data['title'])) { return false; }
		return true;
	}

	public function selectReg(&$reg, &$regRange)
	{
		if ($this->type == SOURCE_TYPE_zzba OR $this->type == SOURCE_TYPE_cililian) {
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
		}else if ($this->type == "old") {
			$regRange = '.inerTop';
			$reg = array(
				'link' => array('.magnetlink', 'text'),
				'create_time' => array('.magnetmore dd:eq(4)','text'),
				'file_size' => array('.magnetmore dd:eq(2)', 'text'),
				'file_count' => array('.magnetmore dd:eq(3)', 'text'),
				'hash_value' => array('.magnetmore dd:eq(0)', 'text'),
				'tags' => array('.magnetmore dd:eq(6) a', 'text'),
				'file_list' => array('option', 'text'),
					);
		}else if ($this->type == SOURCE_TYPE_bt70)	{
				$reg = array(
					'link' => array('', 'html'),
					'create_time' => array('.badge:eq(0)', 'text',),
					'file_size' => array('.badge:eq(1)', 'text'),
					'speed' => array('.badge:eq(2)', 'text'),
					'file_count' => array('.badge:eq(4)', 'text'),
					'hash_value' => array('.badge:eq(6)', 'text'),
					'tags' => array('.otherkey .badge', 'text'),
					'file_list' => array('td', 'text'),
					);
			$regRange = '.container .row .col-md-8';
		}
	}

	public function build_zhongziso($jsonArr, $source_id)
	{
		if ($this->type == SOURCE_TYPE_zzba OR $this->type == SOURCE_TYPE_cililian) {
			return $this->build_zhongziso_new($jsonArr, $source_id);
		}else if ($this->type == "old") {
			return $this->build_zhongziso_old($jsonArr, $source_id);
		}else if ($this->type == SOURCE_TYPE_bt70) {
			return $this->build_bt70($jsonArr, $source_id);
		}
	}
	
	private function build_zhongziso_old($jsonArr, $source_id)
	{
		if (count($jsonArr) < 1 OR empty($jsonArr[0]['link'])) { 
			logger("ERROR", "empty link", $this->id, $this->type);
			return array(); 
		}
		
		$tmp = explode('&' , $jsonArr[0]['link']);
		if (count($tmp) < 2) { 
			logger("ERROR", "decode link failed", $this->id, $this->type);
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
		$this->file_list = $this->resetFileListOld(explode( "\n", $jsonArr[0]['file_list'] ));
		//LAST_MODIFY
		$data = $this->build();
		return $data;

	}

	private function build_zhongziso_new($jsonArr, $source_id)
	{
		if (count($jsonArr) < 3 OR empty($jsonArr[0]['link'])) { 
			logger("ERROR", "empty link ", $this->id, $this->type);
			return array(); 
		}
		$tmp = explode('&' , $jsonArr[0]['link']);
		if (count($tmp) < 2) { 
			logger("ERROR", "decode link failed", $this->id, $this->type);
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
		return $data;
	}

	private function build_bt70($jsonArr, $source_id)
	{
		if (count($jsonArr) < 3 OR empty($jsonArr[0]['link'])) { 
			logger("ERROR", "empty link", $this->id, $this->type);
			return array(); 
		}
		$tmp = explode('<br>' , $jsonArr[0]['link'])[1];
		$tmp = explode('&' , $jsonArr[0]['link']);
		if (count($tmp) < 2) { 
			logger("ERROR", "decode link failed", $this->id, $this->type);
			return array(); 
		}
		$hash = end(explode(":", $tmp[0]));
		$title = str_replace("dn=", "", $tmp[1]);
		$title = str_replace("amp;", '', $title);
		$this->hash_value = $hash;
		$this->title = $title;
		$this->create_time = strtotime( $jsonArr[0]['create_time'] );
		$this->file_size = $this->resetFileSize( $jsonArr[0]['file_size'] );
		$this->file_count = $jsonArr[0]['file_count'];
		$this->tags = explode( "\n", $jsonArr[0]['tags'] );
		$this->file_list = $this->resetFileList(explode( "\n", $jsonArr[1]['file_list'] ));
		//LAST_MODIFY
		$data = $this->build();
		return $data;
	}

	private function resetFileListOld($file_list_raw){
		$total = count($file_list_raw);
		if ($total <  0) { return  array(); }
		$ret = array();
		foreach ($file_list_raw as $k => $v) 
		{
			$v = explode('    ',$v);
			if (count($v) < 2 OR empty($v[0]) OR empty($v[1])) { continue; }
			$tmp = array();
			$tmp['name'] = $this->trimall($v[0]);
			$tmp['size'] = $this->resetFileSize($v[1]);
			$ret[] = $tmp;
			unset($tmp);
		}
		return $ret;
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
		$file_size_raw = $this->trimall($file_size_raw);
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

	private function trimall($str)
	{
		$str = preg_replace('/\/\*.*\*\//','',$str);
		$qian=array(" ","　","\t","\n","\r");$hou=array("","","","","");
		return str_replace($qian,$hou,$str); 
	}
}
?>
