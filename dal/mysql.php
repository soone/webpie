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

	/**
	* @name dbSetting 设置数据库连接信息，并生成内部唯一标识，用来防止不断重新连接数据库
	*
	* @param $setting
	*
	* @returns   
	*/
	public function dbSetting($setting)
	{
		$this->setting = $setting;
		$dbObjName = md5(implode('', $this->setting));
		$this->dbObj[$dbObjName] = NULL;
		return $dbObjName;
	}

	/**
	* @name dbConnect 数据库连接，对于已经存在的数据库连接将不再重新连，直接返回连接对象
	*
	* @param $name
	*
	* @returns   
	*/
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

	/**
	* @name setCurDbObj 设置当前数据库对象为可用
	*
	* @param $obj
	*
	* @returns   
	*/
	public function setCurDbObj($obj)
	{
		if(in_array($obj, $this->dbObj))
			$this->curDbObj = $obj;
		else
			throw new Webpie_Dal_Exception('Db Error:You not connect the db');

		return $this;
	}

	/**
	* @name setCurTable 设置当前使用表名
	*
	* @param $table
	*
	* @returns   
	*/
	public function setCurTable($table)
	{
		$this->curTable = $table;
		return $this;
	}

	/**
	* @name dbCreate 对数据库插入操作
	*
	* @param $columns
	* @param $values
	*
	* @returns   
	*/
	public function dbCreate($columns, $values, $multi = false)
	{
		$sql = 'INSERT INTO ' . $this->curTable . '(' . $columns . ')VALUES';
		$joinValue = function($v){return '(' . rtrim(str_repeat('?,', count($v)), ',') . ')';};
		if($multi)
			$sql .= implode(',', array_map($joinValue, $values));
		else
			$sql .= $joinValue($values);

		$stmt = $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		//multi
		$arrKeys = implode('', array_keys($values));
		$params[] = &$arrKeys;
		foreach($values as $v)
		{
			$params[] = &$v;
		}
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
			$params = array_merge(array(implode('', array_keys($options['where'][1]))), array_values($options['where'][1]));
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
				$field[] = &$col[trim($name)];
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

		$params = array_merge(array(implode('', array_keys($values))), array_values($values));
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

	public function getCurDbObj()
	{
		return $this->curDbObj;
	}

	public function getCurTable()
	{
		return $this->curTable;
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
			is_object($db) ? $db->close() : '';
		}
	}
}
