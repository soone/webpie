<?php
class Webpie_Logs
{
	private $fd;

	/**
	* @name __construct 
	*
	* @param $file
	* @param $mode
	*
	* @returns   
	*/
	public function __construct($file, $mode = 'a+')
	{
		if(in_array($mode, array('r', 'r+')) && !is_file($file))
			throw new Webpie_Util_Exception('文件不存在，无法打开');

		$this->fd = fopen($file, $mode);
	}

	/**
	* @name record 
	*
	* @param $msg
	*
	* @returns   
	*/
	public function record($msg)
	{
		$msg = date('Y-m-d H:i:s') . ' ' . $msg;
		if(fwrite($this->fd, $msg) === false)
			throw new Webpie_Util_Exception($msg . '信息写入失败');

		return true;
	}

	public function __destruct()
	{
		if(is_resource($this->fd))
			fclose($this->fd);
	}
}
