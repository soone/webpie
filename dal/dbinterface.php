<?php
/**
* @file dbinterface.php
* @name db接口
* @author soone fengyue15@gmail.com
* @version 0.1
* @date 2012-03-22
*/
interface Webpie_Dal_Dbinterface
{
	public function dbSetting($setting);
	public function dbConnect($name);
	public function setCurDbObj($obj);
	public function setCurTable($table);
	public function dbCreate($columns, $values, $multi = false);
	public function dbRead($columns, $options = NULL);
	public function dbUpdate($columns, $values, $where = NULL);
	public function dbDelete($where = NULL);
}
