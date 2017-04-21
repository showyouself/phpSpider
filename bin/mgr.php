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
	}
	
	public function scrawl($id)
	{
		$this->url_search = $this->url_search.$id;
		$reg = array();
		$regRange = '';
		$hj = new QueryList($this->url_search, $reg, $regRange, 'curl', 'UTF-8');
		var_dump($hj);
	}
}
?>
