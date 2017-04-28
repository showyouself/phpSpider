<?php
class Magnet{
	protected $hash_value = "";
	protected $title = "";
	protected $create_time = 0;
	protected $file_size = 0;
	protected $file_count = 0;
	protected $tags = array();
	protected $file_list = array();

	public function __construct()
	{
		
	}

	public function build()
	{
		if (empty($this->hash_value) OR empty($this->title))
		{ return false; }
		$data = array(
				'hash_value' => strtolower($this->hash_value),
				'title' => $this->title,
				'create_time' => $this->create_time,
				'file_size' => $this->file_size,
				'file_count' => $this->file_count,
				'tags' => $this->tags,
				'file_list' => $this->file_list,
				);
		return $data;
	}

	public function sync_post($ret)
	{
		if (!empty($ret) AND !empty($ret['hash_value']) AND !empty($ret['title'])) {
			global $config;
			$curl = new Curl();
			$bak = $curl->rapid($config['sync_url'], 'POST', json_encode($ret));
			$bak = json_decode($bak, true);
			if ($bak['err'] == 0) {  return true; }
		}
		return false;
	}
}
?>
