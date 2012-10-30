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
		$dbObjName = NULL;
		array_walk_recursive($this->setting, function($s) use (&$dbObjName){$dbObjName .= $s;});
		$dbObjName = md5($dbObjName);
		!is_object($this->dbObj[$dbObjName]) ? $this->dbObj[$dbObjName] = NULL : '';
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
				throw new Webpie_Dal_Exception('Dal Db Connect Error(' . $this->dbObj[$name]->connect_errno . '):' . $this->dbObj[$name]->connect_error);

			$this->dbObj[$name]->set_charset(!empty($this->setting['charset']) ? $this->setting['charset'] : 'utf8');
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
			throw new Webpie_Dal_Exception('Dal Db Error:Can not connect the db');

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
	* @name dbCreate 对数据库插入操作，允许一次插入多条
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
			throw new Webpie_Dal_Exception('Dal Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		$params = array();
		if($multi)
		{
			$firstParams = NULL;
			$vs = array();
			for($x = 0, $y = count($values); $x < $y; $x++)
			{
				$types = array_map(array($this, 'getVarType'), $values[$x]);
				if(in_array(NULL, $types))
					throw new Webpie_Dal_Exception('Dal Db Error:bindParams values error');

				$firstParams .= implode('', $types);
				$vs = array_merge($vs, array_values($values[$x]));
			}
			$params[] = &$firstParams;

			for($i = 0, $j = count($vs); $i < $j; $i++)
			{
				$params[] = &$vs[$i];
			}
		}
		else
			$params = $this->setBindParams($values);

		if(call_user_func_array(array($stmt, 'bind_param'), $params) === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);

		$affecteds = $stmt->affected_rows;
		$stmt->close();
		return $affecteds;
	}
	
	/**
	* @name dbRead 对数据库的数据读取
	*
	* @param $columns
	* @param $options
	*
	* @returns   
	*/
	public function dbRead($columns, $options = NULL)
	{
		$sql = 'SELECT ' . $columns . ' FROM ' . $this->curTable;
		$values = '';
		if(!empty($options['where']) && count($options['where']) == 2)
		{
			$validWhere = $this->setWhere($options['where'][0], $options['where'][1]);
			$sql .= $validWhere[0];
			$values = $validWhere[1];
		}

		if(!empty($options['order']))
			$sql .= ' ORDER BY ' . $options['order'];

		if(!empty($options['limit']))
			$sql .= ' LIMIT ' . $options['limit'];

		$stmt = $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		if(!empty($options['where']) && !empty($values))
		{
			if(call_user_func_array(array($stmt, 'bind_param'), $this->setBindParams($values)) === false)
				throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);
		}

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);

		$col = array();
		$field = array();
		if($columns == '*')
		{
			$metaData = $stmt->result_metadata();
			$fields = $metaData->fetch_fields();
			foreach($fields as $f)
			{
				$field[] = &$col[$f->name];
			}
		}
		else
		{
			$fields = explode(',', $columns);
			foreach($fields as $name)
			{
				$matches = array();
				preg_match('/\sas\s(.*)$/i', $name, $matches);
				if(count($matches) > 1)
					$name = $matches[1];

				$field[] = &$col[trim($name)];
			}
		}
		if(call_user_func_array(array($stmt, 'bind_result'), $field) === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);

		$res = NULL;
		while($stmt->fetch())
		{
			if(!empty($options['callback']) && is_callable($options['callback']))
			{
				$res[] = call_user_func_array($options['callback'], $col);
			}
			else
				$res[] = array_map(function($v){return $v;}, $col);
		}

		$stmt->close();
		return $res;
	}


	/**
	* @name dbUpdate 更新数据库数据
	*
	* @param $columns
	* @param $values
	* @param $where
	*
	* @returns   
	*/
	public function dbUpdate($columns, $values, $where = NULL)
	{
		$sql = 'UPDATE ' . $this->curTable . ' SET ' . $columns;
		if(!empty($where) && count($where) == 2)
		{
			$validWhere = $this->setWhere($where[0], $where[1]);
			$sql .= $validWhere[0];
			$values = array_merge($values, $validWhere[1]);
		}

		$stmt = $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		if(!empty($values))
		{
			if(call_user_func_array(array($stmt, 'bind_param'), $this->setBindParams($values)) === false)
				throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);
		}

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);

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
	public function dbDelete($whereCol = NULL, $colVal = NULL)
	{
		$sql = 'DELETE FROM ' . $this->curTable;
		$values = '';
		if(!empty($whereCol))
		{
			$validWhere = $this->setWhere($whereCol, $colVal);
			$sql .= $validWhere[0];
			$values = $validWhere[1];
		}

		$stmt = $this->curDbObj->prepare($sql);
		if($stmt === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $this->curDbObj->errno . '):' . $this->curDbObj->error);

		if(!empty($whereCol) && !empty($values))
		{
			if(call_user_func_array(array($stmt, 'bind_param'), $this->setBindParams($values)) === false)
				throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);
		}

		if($stmt->execute() === false)
			throw new Webpie_Dal_Exception('Dal Db Error(' . $stmt->errno . '):' . $stmt->error);

		$affecteds = $stmt->affected_rows;
		$stmt->close();
		return $affecteds;
	}

	/**
	* @name setBindParams 用来设置prepare的时候需要bind_param的时候的引用
	*
	* @param $values
	*
	* @returns   
	*/
	private function setBindParams(&$values)
	{
		$params = array(NULL);
		$values = array_values($values);
		for($i = 0, $j = count($values); $i < $j; $i++)
		{
			$type = $this->getVarType($values[$i]);
			if(!$type)
				throw new Webpie_Dal_Exception('Dal Db Error:bindParams values error');
			
			$params[0] .= $type;
			$params[] = &$values[$i];
		}
		
		return $params;
	}

	/**
	* @name getVarType 判断给定变量的类型
	*
	* @param $var
	*
	* @returns   
	*/
	private function getVarType($var)
	{
		if(is_string($var))
			return 's';

		if(is_int($var))
			return 'i';

		if(is_float($var))
			return 'd';

		if(is_resource($var))
			return 'b';

		return NULL;
	}

	public function getCurDbObj()
	{
		return $this->curDbObj;
	}

	public function getCurTable()
	{
		return $this->curTable;
	}

	public function getLastCreateId()
	{
		return $this->curDbObj->insert_id;
	}
	
	public function dbGroupRead(){}

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

	private function setWhere($column, $value = array())
	{
		$where = ' WHERE ';
		if(strpos($column, '?') === FALSE || !$value)
			return array($where . $column, $value);

		$whereArr = explode('?', $column, -1);
		$sizeWhere = count($whereArr);
		$tempWhere = '';
		for($i = 0; $i < $sizeWhere; $i++)
		{
			if(is_array($value[$i]))
			{
				$r = '';
				$dot = '';
				foreach($value[$i] as $ev)
				{
					$r .= $dot . '"' . $this->curDbObj->real_escape_string($ev). '"';
					if(!$dot)
						$dot = ',';
				}
				$tempWhere .=  $whereArr[$i] . '(' . $r . ')';
				unset($value[$i]);
			}
			else
				$tempWhere .= $whereArr[$i] . '?';
		}

		return array($where . $tempWhere, $value);
	}
}
