<?php
require '../webpie.php';
class MysqlTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
		$this->db = new Webpie_Dal_Mysql;
		$setting = array('host' => '127.0.0.1', 'user' => 'root', 'pass' => 123456, 'db' => 'test');
		$this->dbObjName = $this->db->dbSetting($setting);
		$this->dbObj = $this->db->dbConnect($this->db->dbSetting($setting));
		$this->dbObj->query('DROP TABLE IF EXISTS friends');
		$this->dbObj->query('CREATE TABLE friends (id int, name varchar(20))');
		$this->dbObj->query('INSERT INTO friends VALUES (1,\'Hartmut\'), (2, \'Ulf\')');
		$this->db->setCurDbObj($this->dbObj);
		$this->db->setCurTable('friends');
	}

	public function testDbSetting()
	{
		$setting = array('host' => '127.0.0.1', 'user' => 'root', 'pass' => 123456, 'db' => 'test');
		$this->assertEquals($this->db->dbSetting($setting), md5(implode('', $setting)));
	}
	
	public function testDbConnect()
	{
		$setting = array('host' => '127.0.0.1', 'user' => 'root', 'pass' => 123456, 'db' => 'test');
		$this->assertInstanceOf('mysqli', $this->db->dbConnect($this->db->dbSetting($setting)));
	}

	public function testSetCurDbObjAndGetCurDbObj()
	{
		$this->assertInstanceOf('Webpie_Dal_Mysql', $this->db->setCurDbObj($this->dbObj));
		$this->assertEquals($this->dbObj, $this->db->getCurDbObj());
	}

	public function testSetCurTableAndGetCurTable()
	{
		$this->assertInstanceOf('Webpie_Dal_Mysql', $this->db->setCurTable('friends'));
		$this->assertEquals('friends', $this->db->getCurTable());
	}

	public function testDbCreate()
	{
		var_dump($this->db->dbCreate('id, name', array('i' => 3, 's' => 'soone')));
		//$this->assertEquals($this->dbObj->insert_id, $this->db->dbCreate('id, name', array('i' => 3, 's' => 'soone')));
	}
}
