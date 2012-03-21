<?php
/**
* @file mysql.php
* @name mysql数据操作类
* @author soone fengyue15@gmail.com
* @version 0.1
* @date 2012-03-22
*/
class Webpie_Dal_Mysql implements Webpie_Dal_Dbinterface
{
	public $setting = NULL;
	private $dbObj = NULL;
	private $curDbObj = NULL;
	private $curTable = NULL;
	public function __construct(){}

	public function dbSetting($setting)
	{
		$this->setting = $setting;
		$dbObjName = md5(implode('', $this->setting));
		$this->dbObj[$dbObjName] = NULL;
		return $dbObjName;
	}

	public function dbConnect($name)
	{
		if(!is_object($this->dbObj[$name]))
		{
			$this->dbObj[$name] = new mysqli($this->setting['host'], 
											$this->setting['user'],
											$this->setting['pass'],
											$this->setting['db']);
			
			if($this->dbObj[$name]->connect_error)
				throw new Webpie_Dal_Exception('Db Connect Error(' . $this->dbObj[$name]->connect_errno . '):' . $this->dbObj[$name]->connect_error);
		}

		return $this->dbObj[$name];
	}

	public function setDbObj($obj)
	{
		if(in_array($obj, $this->dbObj))
			$this->curDbObj = $obj;
		else
			throw new Webpie_Dal_Exception('Db Error:You not connect the db');

		return $this;
	}

	public function setCurTable($table)
	{
		$this->curTable = $table;
		return $this;
	}

	public function dbCreate($columns, $values)
	{
		$stmt = $this->curDbObj->prepare('INSERT INTO ' . $this->curTable . '(' . $columns . ')VALUES(' . 
										rtrim(str_repeat('?,', count($values)), ',') . ')');

		if($stmt === false)
			throw new Webpie_Dal_Exception('Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		$params = array_pad(array_values($values), 0, implode('', array_keys($values)));
		if(call_user_func_array(array($stmt, 'bind_param'), $params) === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		$insertId = $stmt->insert_id;
		$stmt->close();
		return insertId;
	}

	public function dbRead($columns, $options = NULL)
	{
		$sql = 'SELECT ' . $columns . ' FROM ' . $this->curTable;
		if(!empty($options['where']))
			$sql .= ' WHERE ' . $options['where'][0];

		if(!empty($options['limit']))
			$sql .= ' LIMIT ' . $options['limit'];

		if(!empty($options['order']))
			$sql .= ' ORDER BY ' . $options['order'];

		$stmt - $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		if(!empty($options['where']))
		{
			$params = array_pad(array_values($options['where'][1]), 0, implode('', array_keys($options['where'][1])));
			if(call_user_func_array(array($stmt, 'bind_param'), $params) === false)
				throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);
		}

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		$col = array();
		$field = array();
		if($columns == '*')
		{
			$metaData = $stmt->result_metadata();
			$fields = $metaData->fetch_fields();
			foreach($fields as $f)
			{
				$field[] = $col[$f->name];
			}
		}
		else
		{
			$fields = explode(',', $columns);
			foreach($fields as $name)
			{
				$field[] = &$col[$name];
			}
		}
		if(call_user_func_array(array($stmt, 'bind_result'), $field) === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		$res = NULL;
		while($stmt->fetch())
		{
			if(!empty($options['callback']) && is_callable($options['callback']))
			{
				$res[] = call_user_func_array($options['callback'], $col);
			}
			else
				$res[] = $col;
		}

		$stmt->close();

		return $res;
	}

	public function dbGroupRead(){}

	public function dbUpdate($columns, $values, $where = NULL)
	{
		$sql = 'UPDATE ' . $this->curTable . ' SET ' . $columns;
		if(!empty($where))
			$sql .= ' WHERE ' . $where;

		$stmt = $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		$params = array_pad(array_values($values), 0, implode('', array_keys($values)));
		if(call_user_func_array(array($stmt, 'bind_param'), $params) === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		$affecteds = $stmt->affected_rows;
		$stmt->close();
		return $affecteds;
	}

	/**
	* @name dbDelete 删除记录操作
	*
	* @param $where
	*
	* @returns   
	*/
	public function dbDelete($where = NULL)
	{
		$sql = 'DELETE FROM ' . $this->curTable;
		if(!empty($where))
			$sql .= ' WHERE ' . $where;
		$stmt = $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Db Error(' . $stmt->errno . '):' . $stmt->error);

		$affecteds = $stmt->affected_rows;
		$stmt->close();
		return $affecteds;
	}

	/**
	* @name __destruct 关闭所有打开的数据库连接
	*
	* @returns   
	*/
	public function __destruct()
	{
		foreach($this->dbObj as $db)
		{
			$db->close();
		}
	}
}
