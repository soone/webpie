<?php
require_once 'request.php';
class MysqlTest extends PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		new webpie;
		$this->db = new Webpie_Dal_Mysql;
		$setting = array('host' => '127.0.0.1', 'user' => 'root', 'pass' => 123456, 'db' => 'test');
		$this->dbObjName = $this->db->dbSetting($setting);
		$this->dbObj = $this->db->dbConnect($this->dbObjName);
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
		$this->assertEquals(1, $this->db->dbCreate('id, name', array(3, 'soone')));
		$this->assertEquals(2, $this->db->dbCreate('id, name', array(array(3, 'soone'), array(4, 'adou')), 1));
	}

	public function testDbRead()
	{
		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut'), array('id' => 2, 'name' => 'Ulf')), $this->db->dbRead('*'));
		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut'), array('id' => 2, 'name' => 'Ulf')), $this->db->dbRead('id, name'));
		$this->assertEquals(array(array('id' => 1), array('id' => 2)), $this->db->dbRead('id'));
		$this->assertEquals(array(array('name' => 'Hartmut')), $this->db->dbRead('name', array('where' => array('id = ?', array(1)))));
		$this->assertEquals(array(array('id' => 1), array('id' => 2)), $this->db->dbRead('id', array('where' => array('id <> ?', array('')))));
		$this->assertEquals(array(array('id' => 1), array('id' => 2)), $this->db->dbRead('id', array('where' => array('name <> ?', array('soone')))));
		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut')), $this->db->dbRead('*', array('limit' => '0, 1')));
		$this->assertEquals(array(array('id' => 2, 'name' => 'Ulf'), array('id' => 1, 'name' => 'Hartmut')), $this->db->dbRead('*', array('order' => 'id desc')));
		$this->assertEquals(array(array('id' => 2, 'name' => 'Hartmut+1'), array('id' => 3, 'name' => 'Ulf+1')), $this->db->dbRead('*', array('callback' => function($id, $name){return array('id' => $id+1, 'name' => $name . '+1');})));
		$this->assertEquals(array(array('id' => 2, 'name' => 'Hartmut+1')), $this->db->dbRead('*', array(
																								'where' => array('id <> ?', array(2)),
																								'limit' => '0, 1',
																								'order' => 'id desc',
																								'callback' => function($id, $name){return array('id' => $id+1, 'name' => $name . '+1');}
																							)));
		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut'), array('id' => 2, 'name' => 'Ulf')), $this->db->dbRead('*', array('where' => array('id IN ?', array(array(1, 2))))));

		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut'), array('id' => 2, 'name' => 'Ulf')), $this->db->dbRead('*', array('where' => array('name like ?', array('%u%')))));

		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut')), $this->db->dbRead('*', array('where' => array('name like ?', array('%u%')), 'group' => array('name Having id = ?', array(1)))));

		$this->assertEquals(array(array('id' => 1, 'name' => 'Hartmut'), array('id' => 2, 'name' => 'Ulf')), $this->db->dbRead('*', array('where' => array('name like ?', array('%u%')), 'group' => array('name'))));
	}

	public function testDbUpdate()
	{
		$this->assertEquals(2, $this->db->dbUpdate('name = ?', array('soone')));
		$this->assertEquals(1, $this->db->dbUpdate('name = ?', array('adou'), array('id = ?', array(1))));
		$this->assertEquals(0, $this->db->dbUpdate('name = ?', array('adou'), array('id = ? AND name = ?', array(1, 'soone'))));
		$this->assertEquals(1, $this->db->dbUpdate('name = ?', array('adou'), array('id IN ?', array(array(1, 2)))));
	}

	/**
	* @dataProvider deletePro
	*
	* @returns   
	*/
	public function testDbDelete($eq, $where, $col)
	{
		$this->assertEquals($eq, $this->db->dbDelete($where, $col));
	}

	public function deletePro()
	{
		return array(
			array(1, 'id <> ?', array(1)),
			array(2, NULL, NULL),
			array(1, 'id = ?', array(1)),
			array(2, 'name <> ?', array('')),
			array(2, 'id IN ?', array(array(1, 2))),
		);
	}

	public function testDbCOU()
	{
		$this->assertEquals(1, $this->db->dbCOU('id, name', array(3, 'soone')));
	}
}
