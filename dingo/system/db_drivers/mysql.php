<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * MySQL DB Driver Core Class For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class mysql
{
	public $con;
	public $last_result;
	
	private $host;
	private $username;
	private $password;
	private $database;

	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($host,$username,$password,$database)
	{
		$this->host = $host;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		
		$this->con = new mysqli($this->host,$this->username,$this->password,$this->database);
		
		// If an error exists
		if(mysqli_connect_errno())
		{
			dingo_error(E_USER_ERROR,'MySQL Connection Failed, '.mysqli_connect_error());
		}
	}
	
	
	// Select DB
	// ---------------------------------------------------------------------------
	public function select_db($db)
	{
		return $this->con->select_db($db);
	}
	
	
	// Query
	// ---------------------------------------------------------------------------
	public function query($sql)
	{
		$this->last_result = $this->con->query($sql);
		
		// If SQL statement is a SELECT statement
		if(preg_match('/^SELECT/is',$sql))
		{
			// If last result is a valid object
			if(is_object($this->last_result))
			{
				// Loop through results and put them into an array
				$list = array();
				while($row = $this->last_result->fetch_assoc()){$list[] = $row;}
				return $list;
			}
			else
			{
				// Trigger MySQL Warning and return false
				dingo_error(E_USER_WARNING,'MySQL Error, '.$this->con->error);
				return FALSE;
			}
		}
		
		// Otherwise, try and return the result object
		else
		{
			// If last result does not contain errors
			if(!$this->con->errno)
			{
				// return it
				return $this->last_result;
			}
			else
			{
				// Trigger MySQL Warning and return false
				dingo_error(E_USER_WARNING,'MySQL Error, '.$this->con->error);
				return FALSE;
			}
		}
	}
	
	
	// Clean
	// ---------------------------------------------------------------------------
	public function clean($data)
	{
		return $this->con->real_escape_string($data);
	}
	
	
	// Affected Rows
	// ---------------------------------------------------------------------------
	public function affected_rows()
	{
		return $this->con->affected_rows;
	}
	
	
	// Num Rows
	// ---------------------------------------------------------------------------
	public function num_rows()
	{
		return $this->last_result->num_rows;
	}
	
	
	// Create Table
	// ---------------------------------------------------------------------------
	public function create_table($name,$columns)
	{
		return $this->query(DingoSQL::build_create_table($name,$columns));
	}
	
	
	// Select Table
	// ---------------------------------------------------------------------------
	public function table($table)
	{
		$t = new mysql_table($table);
		$t->db = $this;
		$t->name = $table;
		
		return $t;
	}
	
	
	// Truncate Table
	// ---------------------------------------------------------------------------
	public function truncate($table)
	{
		return $this->query("TRUNCATE TABLE `$table`");
	}
	
	
	// Drop Table
	// ---------------------------------------------------------------------------
	public function drop($table)
	{
		return $this->query("DROP TABLE `$table`");
	}
	
	
	// Destruct
	// ---------------------------------------------------------------------------
	public function __destruct()
	{
		$this->con->close();
	}
}



/**
 * MySQL DB Driver Table Class For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingocode.com/framework
 */

class mysql_table
{
	public $db;
	public $table;
	public $name;
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($table)
	{
		$this->table = $table;
	}


	// Select
	// ---------------------------------------------------------------------------
	public function select()
	{
		$query = new DingoQuery('select');
		$query->table = $this;
		$args = func_get_args();
		
		if((func_num_args() == 3) AND (!empty($args[1])) AND in_array($args[1],array('=','!=','<','>','>=','<=')))
		{
			$query->columns[] = '*';
			return $query->where($args[0],$args[1],$args[2])->execute();
		}
		else
		{
			$query->columns = $args;
			return $query;
		}
	}
	
	
	// Count
	// ---------------------------------------------------------------------------
	public function count()
	{
		$query = new DingoQuery('count');
		$query->table = $this;
		return $query;
	}
	
	
	// Select Distinct
	// ---------------------------------------------------------------------------
	public function distinct()
	{
		$cols = func_get_args();
		
		return $this->db->query(DingoSQL::build_distinct($cols,$this->name));
	}
	
	
	// Update
	// ---------------------------------------------------------------------------
	public function update($cols)
	{
		$query = new DingoQuery('update');
		$query->table = $this;
		
		// Clean the data
		foreach($cols as $col=>$val)
		{
			$query->columns[$col] = $this->db->clean($val);
		}
		
		return $query;
	}
	
	
	// Delete
	// ---------------------------------------------------------------------------
	public function delete()
	{
		$query = new DingoQuery('delete');
		$query->table = $this;
		
		if(func_num_args() == 3)
		{
			$args = func_get_args();
			return $query->where($args[0],$args[1],$args[2])->execute();
		}
		else
		{
			return $query;
		}
	}
	
	
	// Insert
	// ---------------------------------------------------------------------------
	public function insert($data,$query=TRUE)
	{
		if(!is_array($data))
		{
			trigger_error('MySQL Error: Incorrect data type passed to insert function. You must supply an associative array',E_USER_ERROR);
		}
		else
		{
			// Clean data before inserting
			$clean = array();
			$select = $this->select('*');
			$x=0;
			
			foreach($data as $key=>$val)
			{
				$clean[$key] = $this->db->clean($val);
				
				if($x > 0)
				{
					$select->clause('AND')
					       ->where($key,'=',$val);
				}
				else
				{
					$select->where($key,'=',$val);
				}
				
				$x++;
			}
			
			// Build and run SQL query
			$sql = DingoSQL::build_insert($clean,$this->name,'mysql');
			$this->db->query($sql);
			
			// Return Select
			//return $this->db->con->insert_id;
			if($query)
			{
				$row = array_reverse($select->execute());
				return $row[0];
			}
		}
	}
	
	
	// Execute
	// ---------------------------------------------------------------------------
	public function execute($query)
	{
		// Selects
		if($query->type == 'select')
		{
			$data = $this->db->query(DingoSQL::build_select($query,'mysql'));
		}
		// Counts
		elseif($query->type == 'count')
		{
			$tmp = $this->db->query(DingoSQL::build_count($query,'mysql'));
			$data = $tmp[0]['num'];
			
		}
		// Updates
		elseif($query->type == 'update')
		{
			$data = $this->db->query(DingoSQL::build_update($query,'mysql'));
		}
		// Deletes
		elseif($query->type == 'delete')
		{
			$data = $this->db->query(DingoSQL::build_delete($query,'mysql'));
		}
		
		return $data;
	}
}