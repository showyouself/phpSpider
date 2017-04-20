<?php
class App_Log {

	protected $_log_path = "../log/";
	protected $_threshold   = 1;
	protected $_date_fmt    = 'Y-m-d H:i:s';
	protected $_enabled     = TRUE;
	protected $_levels      = array('ERROR' => '1', 'DEBUG' => '2',  'INFO' => '3', 'ALL' => '4');

	public static function inst($path = NULL, $level = NULL)
	{
		static $inst = NULL;
		if (empty($inst)) { $inst = new App_Log($path, $level); }
		return $inst;
	}

	public function __construct($path, $level)
	{
		if (!empty($path)) { $this->_log_path = realpath($path).'/'; }

		if ( ! is_dir($this->_log_path))
		{
			$this->_enabled = FALSE;
		}

		if (is_numeric($level))
		{
			$this->_threshold = $level;
		} else if (is_string($level)) {
			$level = strtoupper($level);
			if (array_key_exists($level, $this->_levels)) {
				$this->_threshold = $this->_levels[$level];
			}
		}
	} 

	public function log($level = 'error', $msg, $php_error = FALSE)
	{
		if ($this->_enabled === FALSE)
		{
			return FALSE;
		}

		$level = strtoupper($level);

		if ( ! isset($this->_levels[$level]) OR ($this->_levels[$level] > $this->_threshold))
		{
			return FALSE;
		}

		$filepath = $this->_log_path.'log-'.date('Y-m-d').'.php';
		$message  = '';

		if ( ! $fp = @fopen($filepath, 'a+'))
		{
			return FALSE;
		}

		$message .= $level.' '.(($level == 'INFO') ? ' -' : '-').' '.date($this->_date_fmt). ' --> '.$msg."\n";

		flock($fp, LOCK_EX);
		fwrite($fp, $message);
		flock($fp, LOCK_UN);
		fclose($fp);

		@chmod($filepath, FILE_WRITE_MODE);
		return TRUE;
	}
}

//创建一个用于使用的对象
$logger = function ($level, $msg) use ($config)
{
	if (empty($logger)) { $logger = new App_Log($config['log_path'], $config['level']);}
	$logger->log($level, $msg);
}
?>
