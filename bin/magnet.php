<?php
class Magnet{
	protected $hash_value = "";
	protected $title = "";
	protected $create_time = 0;
	protected $file_size = 0;
	protected $file_count = 0;
	protected $tags = "";

	public function __construct()
	{
		
	}
	public function build()
	{
		if (empty($this->hash_value) OR empty($this->title) 
				OR empty($this->create_time) OR empty($this->file_size) 
				OR empty($this->file_count) OR empty($this->tags))
		{ return false; }
		$data = array(
				'hash_value' => $this->hash_value,
				'title' => $this->title,
				'create_time' => $this->create_time,
				'file_size' => $this->file_size,
				'file_count' => $this->file_count,
				'tags' => $this->tags,
				);
		return $data;
	}
}
?>
