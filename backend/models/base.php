<?php
	// the Item class seems pointless, but it's really so that we don't have to
	// have 5,000 database connections at once unless we need them
	class Item {
		var $_parentModel = null;
		var $_parentInstance = false;
		function __call($method, $arguments)
		{
			//map this to the parent model
			$p = $this->_make_parent();
			$r = call_user_func_array(array($p, $method), $arguments);
			foreach($p as $key => $value) {
				$this->$key = $value;
			}
			return $r;
		}
		function __get($var) {
			$parent = new $this->_parentModel(array(), true);
			$type = $parent->$var['type'];
			if($type == "foreignkey") {
				$p = $this->_make_parent();
				return $p->get($this->$var);
			}
			elseif($type == "array") {
				if(!is_array($this->$var)) $val = json_decode($this->$var, true);
				if(is_array($val)) return $val;
				else return array();
			}
			else {
				return $this->$var;
			}
		}
		function _make_parent()
		{
			if(!$this->_parentInstance)
			{
				$parent = $this->_parentModel;
				$this->_parentInstance = new $parent;
			}
			foreach($this as $prop => $val)
			{
				$this->_parentInstance->$prop = $val;
			}
			return $this->_parentInstance;
		}
		function save()
		{
			$p = $this->_make_parent();
			$p->save();
			if(!is_numeric($this->id)) {
				$this->id = $p->_link->lastInsertID($p->_get_tablename());
			}
		}
	}
	
	class Base
	{
		var $id = array(
			'type' => "integer",
			'autoincrement' => 1,
			'notnull' => 1,
			'unsigned' => 1,
			'default' => 0
		);
		var $_parentItem = null;
		var $_result = false;
		var $_link;
		var $_modified = false;
		var $_schema = false;
		function __construct($values=array(), $preserveSchema=false) {
			if(!$preserveSchema) {
				foreach($this as $key => $value) {
					if($key{0} != "_") $this->$key = ($this->$key['type'] == "array" ? array() : null);
				}
				foreach($values as $key => $value) {
						$this->$key = $value;
				}
			} else $this->_schema = true;
		}
		
		function _connect() {
			if(!$this->_link) {
				$this->_link = MDB2::factory($GLOBALS['db']['database']);
				if(PEAR::isError($this->_link)){
					internal_error("db_connect_err");
				}
			}
		}
		
		function _query($sql, $values=array()) {
			$this->_connect();
		    $this->_result = array();
		    if(sizeof($values) > 0) {
		        $statement = $this->_link->prepare($sql, TRUE, MDB2_PREPARE_RESULT);
		        $resultset = $statement->execute($values);
		        $statement->free();
		    }
		    else {
		        $resultset= $this->_link->query($sql);
		    }
		    if(PEAR::isError($resultset)) {
		        internal_error("db_query_err");
		    }
		
		    while($row = $resultset->fetchRow(MDB2_FETCHMODE_ASSOC)) {
		        $this->_result[] = $row;
		    }
			return $this->_result;
		}
		
		function __deconstruct()
		{
			if($this->_result) $this->_result->free();
			$this->_link = null;
		}
		
		function __set($var, $value) {
			if($this->_modified == false) $this->_modified = true;
			$this->$var = $value;
		}
		
		function __get($var) {
			if($this->_modified == true)
			{
				$me = get_class($this);
				$parent = new $me(array(), true);
				$type = $parent->$var['type'];
				if($type == "foreignkey") {
					$class = $parent->$var['model'];
					$class = new $class();
					return $class->get($this->$var);
				}
				elseif($type == "array") {
					if(!is_array($this->$var)) return json_decode($this->$var, true);
				}
				elseif(isset($this->$var)) {
					return $this->$var;
				}
			}
			else {
				return $this->$var;
			}
		}
		
		function save()
		{
			$table = $this->_get_tablename();
			if(is_numeric($this->id)) { $sql = "UPDATE ${table} SET "; }
			else { $sql = "INSERT INTO ${table} SET "; }
			$arr = array();
			$me = get_class($this);
			$parent = new $me(array(), true);
			foreach($this as $key => $value)
			{
				if($key{0} != "_" && $key != "id")
				{
					$info = $parent->$key;
					if(!isset($info['type'])) {
						if(stristr($info['dbtype'], "text") !== false) {
							$info['type'] = "string";
						}
						if(stristr($info['dbtype'], "int") !== false) {
							$info['type'] = "integer";
						}
					}
					else {
						if($info['type'] == "array") {
							if(is_null($value)) $value = array();
							$value = json_encode($value);
						}
						
						if($info['type'] == "foreignkey") {
							$value = ($value->id ? $value->id : null);
						}
					}
					
					if(is_int($value) || is_null($value))
					{
						@array_push($arr, $this->_escape($key) . "=" . (is_null($value) ? "null" : $value));
					}
					else
					{
						//when all else fails, make it a string
						@array_push($arr, $this->_escape($key) . "=\"" . $this->_escape($value) ."\"");
					}
				}
			}
			$sql .= implode(', ',$arr);
			$id = $this->id;
			if(is_numeric($this->id)) { $sql .= " WHERE `ID`=${id} LIMIT 1"; }
			$this->_query($sql);
			if(!is_numeric($this->id)) {
				$this->id = $this->_link->lastInsertID($this->_get_tablename());
			}
		}
		
		function get($id)
		{
			$tablename = $this->_get_tablename();
            if(!is_numeric($id))
            {
                $id = "'" . $this->_escape($id) . "'"; 
            }
			$this->_query("SELECT * FROM ${tablename} WHERE `ID`=${id} LIMIT 1");
			if($this->_result[0])
			{
				$p = $this->_makeModel($this->_result[0]);
				return $p;
			}
			else
			{
				return false;
			}
		}
		function _escape($str)
		{
			$this->_connect();
			return $this->_link->escape($str);
		}
		function filter($field, $value=false)
		{
			$tablename = $this->_get_tablename();
			if(is_array($field))
			{
				$query = "SELECT * FROM ${tablename} WHERE ";
				$list = array();
				foreach($field as $key => $value)
				{
					array_push($list, $this->_escape($field[$i]) . "=\"" . $this->_escape($value[$i]) . "\"");
				}
				$query .= implode(" AND ", $list);
			}
			else {
				$field = $this->_escape($field);
				//TODO: format value's datatype accordingly
				$value = $this->_escape($value);
				$query = "SELECT * FROM ${tablename} WHERE ${feild}=\"${value}\"";
			}
			$this->_query($query); 
			$list = Array();
			foreach($this->_result as $line)
			{
				array_push($list, $this->_makeModel($line));
				$results = TRUE;
			}
			if(!isset($results)) { return false; }
			else { return $list; }
		}
		function all()
		{
			$tablename = $this->_get_tablename();
			$this->_query("SELECT * FROM ${tablename}");
			$list = Array();
			foreach($this->_result as $line)
			{
				array_push($list, $this->_makeModel($line));
			}
			return $list;
		}		
		function _get_tablename()
		{
			if(isset($this->_tablename))
			{
				$tablename=$this->_tablename;
			}
			else
			{
				$tablename=strtolower(get_class($this));
			}
			$db_prefix = $GLOBALS['db']['prefix'];
			return $db_prefix . $tablename;
		}
		function _makeModel($line)
		{
			$p = new Item;
			$me = get_class($this);
			$parent = new $me(array(), true);
			foreach ($line as $key => $value)
			{ $dec = $parent->$key;
				if($dec["type"] == "array") {
					$value = json_decode($value, true);
				}
				$p->$key = $value;
			}
			$p->_parentModel = $me;
			return $p;
		}
		function truncate() {
			$table = $this->_get_tablename();
			$this->_query("TRUNCATE TABLE `${table}`");
			$this->_query("ALTER TABLE `${table}` AUTO_INCREMENT = 1");
		}
		function make_json($columns=false)
		{
			$list = array();
			$filter = is_array($columns);
			foreach($this as $key => $value)
			{
				if($key{0} != "_")
				{
					$continue = true;
					if($filter)
					{
						if(array_search($key, $columns)===false) $continue = false;
					}
					if($continue)
					{
						$list[$key] = $value;
					}
				}
			}
			return json_encode($list);
		}
		function delete()
		{
			if(is_numeric($this->id))
			{
				$this->cleanup();
				$this->_query("DELETE FROM " . $this->_get_tablename() . " WHERE ID=" . $this->id . " LIMIT 1");
			}
		}
		function cleanup() {
			//this is for cleaning up any associations with other models
			//don't call this directly in your code.
		}
		function _create_table()
		{
			
			$this->_connect();
			$this->_link->mgDropTable($this->_get_tablename());
			$list = array();
			foreach($this as $key => $v)
			{
				if($key{0} != "_" && is_array($v)) {
					if($v['type'] == "foreignkey") {
						$v['type'] = "integer";
					}
					if($v['type'] == "array") {
						$v['type'] = "text";
					}
					$list[$key] = $v;
				}
			}
			$p = $this->_link->mgCreateTable($this->_get_tablename(), $list);
			if (PEAR::isError($p)) {
    			die('Creation of table failed: "'.$p->getMessage().'"');
			}
			$this->_link->mgCreateIndex($this->_get_tablename(), "id_key", array(
				'fields' => array(
					'id' => array()
				)
			));
		}
	}
?>
