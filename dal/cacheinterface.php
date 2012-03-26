<?php
interface Webpie_Dal_Cacheinterface
{
	public function cacheSetting($setting);
	public function cacheConnect($name);
	public function cacheCreate();
	public function cacheRead();
	public function cacheUpdate($columns, $values, $where = NULL);
	public function cacheDelete($whereCol = NULL, $colVal = NULL);
	public function setCurCacheObj($obj);
}
