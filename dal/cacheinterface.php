<?php
interface Webpie_Dal_Cacheinterface
{
	public function cacheSetting($setting);
	public function cacheConnect($name);
	public function setCurCacheObj($obj);
}
