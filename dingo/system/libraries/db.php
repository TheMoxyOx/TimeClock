<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * SQL Query Building Class For Dingo Framework DB Drivers
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class DingoSQL
{
	static function backtick($driver='mysql')
	{
		if($driver == 'mysql')
		{
			return '`';
		}
		elseif($driver == 'pgsql' OR $driver == 'sqlite3')
		{
			return '"';
		}
	}
	
	
	// Build WHERE portion of a query
	// ---------------------------------------------------------------------------
	static function build_where($where_list,$driver)
	{
		$sql = ' WHERE';
		$tick = DingoSQL::backtick($driver);
		
			
		foreach($where_list as $i)
		{
			if($i['type'] == 'column')
			{
				$sql .= " $tick{$i['column']}$tick{$i['operator']}'{$i['value']}'";
			}
			elseif($i['type'] == 'clause')
			{
				$sql .= " {$i['clause']}";
			}
		}
		
		return $sql;
	}
	
	
	// Build columns portion of query
	// EX: SELECT[ *] or SELECT[ `col1`,`col2`]
	// ---------------------------------------------------------------------------
	static function build_columns($columns,$type,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$sql = '';
		$x = 0;
		
		// SELECT queries
		if($type == 'select')
		{
			foreach($columns as $col)
			{
				if($col != '*')
				{
					if($x > 0)
					{
						$sql .= ",$tick$col$tick";
					}
					else
					{
						$sql .= " $tick$col$tick";
					}
					
					$x++;
				}
				else
				{
					$sql .= ' *';
				}
			}
		}
		
		return $sql;
	}
	
	
	// Build CREATE TABLE query
	// ---------------------------------------------------------------------------
	static function build_create_table($name,$columns,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$sql = "CREATE TABLE $tick$name$tick\n(";
		$primary = FALSE;
		$x = 0;
		
		foreach($columns as $name=>$col)
		{
			if($x != 0)
			{
				$sql .= ',';
			}
			else
			{
				$x++;
			}
			
			// If specific length is set
			if(isset($col['length']))
			{
				$sql .= "\n$tick$name$tick {$col['type']}({$col['length']})";
			}
			else
			{
				$sql .= "\n$tick$name$tick {$col['type']}";
			}
			
			// NOT NULL
			if(isset($col['not_null']))
			{
				$sql .= ' NOT NULL';
			}
			
			// AUTO_INCREMENT
			if(isset($col['auto_increment']))
			{
				$sql .= ' AUTO_INCREMENT';
				$primary = $name;
			}
		}
		
		// PRIMARY KEY
		if($primary)
		{
			$sql .= ",\nPRIMARY KEY ($tick$primary$tick)";
		}
		
		$sql .= "\n)";
		
		return $sql;
	}
	
	
	// Build INSERT query
	// ---------------------------------------------------------------------------
	static function build_insert($data,$table,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$cols = '(';
		$vals = '(';
		$x = 0;
	
		foreach($data as $col=>$val)
		{
			if($x > 0)
			{
				$cols .= ",$tick$col$tick";
				$vals .= ",'$val'";
			}
			else
			{
				$cols .= "$tick$col$tick";
				$vals .= "'$val'";
			}
			
			$x++;
		}
		
		$cols .= ')';
		$vals .= ')';
		
		return "INSERT INTO $tick$table$tick $cols VALUES $vals";
	}
	
	
	// Build UPDATE query
	// ---------------------------------------------------------------------------
	static function build_update($query,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$sql = "UPDATE $tick{$query->table->name}$tick SET ";
		$x = 0;
		
		foreach($query->columns as $col=>$val)
		{
			if($x == 0)
			{
				$sql .= "$tick$col$tick='$val'";
				$x++;
			}
			else
			{
				$sql .= ",$tick$col$tick='$val'";
			}
		}
		
		$sql .= DingoSQL::build_where($query->where_list,$driver);
		
		return $sql;
	}
	
	
	// Build DELETE query
	// ---------------------------------------------------------------------------
	static function build_delete($query,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$sql = "DELETE FROM $tick{$query->table->name}$tick";
		
		$sql .= DingoSQL::build_where($query->where_list,$driver);
		
		return $sql;
	}
	
	
	// Build SELECT query
	// ---------------------------------------------------------------------------
	static function build_select($query,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$sql = 'SELECT';
			
		// Columns to select
		$sql .= DingoSQL::build_columns($query->columns,'select',$driver);
		
		$sql .= " FROM $tick{$query->table->name}$tick";
		
		// WHERE
		if(!empty($query->where_list))
		{
			$sql .= DingoSQL::build_where($query->where_list,$driver);
		}
		
		// ORDER BY
		if(!empty($query->order_list))
		{
			$sql .= ' ORDER BY';
			$x = 0;
			
			foreach($query->order_list as $i)
			{
				if($x == 0)
				{
					$sql .= " $tick$i$tick";
					$x++;
				}
				else
				{
					$sql .= " AND $tick$i$tick";
				}
			}
			
			if($query->order == 'DESC' OR $query->order == 'ASC')
			{
				$sql .= " {$query->order}";
			}
		}
		
		// LIMIT
		if($query->_limit !== FALSE)
		{
			$sql .= " LIMIT {$query->_limit}";
		}
		
		// OFFSET
		if($query->_offset !== FALSE)
		{
			$sql .= " OFFSET {$query->_offset}";
		}
		
		//echo "<hr/>\n$sql<hr/>\n";
		//return $this->db->query($sql);
		return $sql;
	}
	
	
	// Build COUNT query
	// ---------------------------------------------------------------------------
	static function build_count($query,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		$sql = "SELECT COUNT(*) AS {$tick}num{$tick} FROM $tick{$query->table->name}$tick";
		
		// WHERE
		if(!empty($query->where_list))
		{
			$sql .= DingoSQL::build_where($query->where_list,$driver);
		}
		
		// ORDER BY
		if(!empty($query->order_list))
		{
			$sql .= ' ORDER BY';
			$x = 0;
			
			foreach($query->order_list as $i)
			{
				if($x == 0)
				{
					$sql .= " $tick$i$tick";
					$x++;
				}
				else
				{
					$sql .= " AND $tick$i$tick";
				}
			}
			
			if($query->order == 'DESC' OR $query->order == 'ASC')
			{
				$sql .= " {$query->order}";
			}
		}
		
		// LIMIT
		if($query->_limit !== FALSE)
		{
			$sql .= " LIMIT {$query->_limit}";
		}
		
		// OFFSET
		if($query->_offset !== FALSE)
		{
			$sql .= " OFFSET {$query->_offset}";
		}
		
		//echo "<hr/>\n$sql<hr/>\n";
		//return $this->db->query($sql);
		return $sql;
	}
	
	
	// Build DISTINCT query
	// ---------------------------------------------------------------------------
	static function build_distinct($cols,$table,$driver)
	{
		$tick = DingoSQL::backtick($driver);
		if($cols[0] == '*')
		{
			return "SELECT DISTINCT * FROM $tick$table$tick";
		}
		else
		{
			return "SELECT DISTINCT $tick".implode("$tick,$tick",$cols)."$tick FROM $tick$table$tick";
		}
	}
}



/**
 * Query Class For Dingo Framework DB Drivers
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingocode.com/framework
 */

class DingoQuery
{
	public $table;
	public $type;
	public $columns = array();
	public $where_list = array();
	public $order = FALSE;
	public $order_list = array();
	public $desc = FALSE;
	public $_limit = FALSE;
	public $_offset = FALSE;
	
	
	public function __construct($type)
	{
		$this->type = $type;
	}
	
	
	// Column
	// ---------------------------------------------------------------------------
	public function column($col,$val=FALSE)
	{
		if($this->type == 'update')
		{
			$this->columns[] = array('column'=>$col,'value'=>$this->table->db->clean($val));
		}
		else
		{
			$this->columns[] = $col;
		}
		
		return $this;
	}
	
	
	// WHERE Statement
	// ---------------------------------------------------------------------------
	public function where($col,$operator,$val)
	{
		if(
			($operator != '=') AND
			($operator != '!=') AND
			($operator != '<') AND
			($operator != '>') AND
			($operator != '>=') AND
			($operator != '<=')
		){
			trigger_error("Database error: The WHERE operator '$operator' is not recognized.",E_USER_ERROR);
		}
		
		$this->where_list[] = array(
			'type'=>'column',
			'column'=>$col,
			'operator'=>$operator,
			'value'=>$this->table->db->clean($val)
		);
		
		return $this;
	}
	
	
	// AND/OR Clauses
	// ---------------------------------------------------------------------------
	public function clause($c)
	{
		$c = strtoupper($c);
		
		if($c != 'AND' AND $c != 'OR')
		{
			throw new Exception("mysql error: The WHERE clause '$c' is not recognized.");
		}
		
		$this->where_list[] = array('type'=>'clause','clause'=>$c);
		
		return $this;
	}
	
	
	// ORDER BY
	// ---------------------------------------------------------------------------
	public function order_by($col,$order=FALSE)
	{
		$this->order_list[] = $col;
		
		if($order)
		{
			$this->order = $order;
		}
		
		return $this;
	}
	
	
	// Limit
	// ---------------------------------------------------------------------------
	public function limit($limit)
	{
		$this->_limit = $limit;
		return $this;
	}
	
	
	// Offset
	// ---------------------------------------------------------------------------
	public function offset($offset=0)
	{
		$this->_offset = $offset;
		return $this;
	}
	
	
	// Execute
	// ---------------------------------------------------------------------------
	public function execute()
	{
		return $this->table->execute($this);
	}
}



/**
 * DB Library For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */
 
$db_settings = config::get('db');

$driver = $db_settings['driver'];
$driver_loc = SYSTEM."/db_drivers/$driver.php";

if(file_exists($driver_loc))
{
	include($driver_loc);
	
	register::library('db',new $driver(
		$db_settings['host'],
		$db_settings['username'],
		$db_settings['password'],
		$db_settings['database']
	));
}
else
{
	trigger_error("Database driver not found at $driver_loc",E_USER_ERROR);
}