<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * PostgreSQL DB Driver Core Class For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class pgsql
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
		
		if(!$this->con = pg_connect("host=$host dbname=$database user=$username password=$password"))
		{
			// If an error exists trigger it
			dingo_error(E_USER_ERROR,'PostgreSQL Connection Failed, '.pg_last_error());
		}
	}
	
	
	// Query
	// ---------------------------------------------------------------------------
	public function query($sql)
	{
		$this->last_result = pg_query($sql) or dingo_error(E_USER_ERROR,'PostgreSQL Error, '.pg_last_error());;
		
		
		// If SQL statement is a SELECT statement
		if(preg_match('/^SELECT/is',$sql))
		{
			// Loop through results and put them into an array
			$list = array();
			while($row = pg_fetch_array($this->last_result,null,PGSQL_ASSOC)){$list[] = $row;}
			return $list;
		}
		
		// Otherwise, try and return the result object
		else
		{
			// return it
			return $this->last_result;
		}
	}
	
	
	// Clean
	// ---------------------------------------------------------------------------
	public function clean($data)
	{
		return pg_escape_string($data);
	}
	
	
	// Affected Rows
	// ---------------------------------------------------------------------------
	public function affected_rows()
	{
		return pg_affected_rows($this->last_result);
	}
	
	
	// Num Rows
	// ---------------------------------------------------------------------------
	public function num_rows()
	{
		return pg_num_rows($this->last_result);
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
		$t = new pgsql_table($table);
		$t->db = $this;
		$t->name = $table;
		
		return $t;
	}
	
	
	// Truncate Table
	// ---------------------------------------------------------------------------
	public function truncate($table)
	{
		return $this->query("TRUNCATE TABLE \"$table\"");
	}
	
	
	// Drop Table
	// ---------------------------------------------------------------------------
	public function drop($table)
	{
		return $this->query("DROP TABLE \"$table\"");
	}
	
	
	// Destruct
	// ---------------------------------------------------------------------------
	public function __destruct()
	{
		pg_close($this->con);
	}
}



/**
 * MySQL DB Driver Table Class For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingocode.com/framework
 */

class pgsql_table
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
		$query = new DingoQuery('select','pgsql');
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
		$query = new DingoQuery('count','pgsql');
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
		$query = new DingoQuery('update','pgsql');
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
		$query = new DingoQuery('delete','pgsql');
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
			trigger_error('PostgreSQL Error: Incorrect data type passed to insert function. You must supply an associative array',E_USER_ERROR);
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
			$sql = DingoSQL::build_insert($clean,$this->name,'pgsql');
			$this->db->query($sql);
			
			// Return Select
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
			$data = $this->db->query(DingoSQL::build_select($query,'pgsql'));
		}
		// Counts
		elseif($query->type == 'count')
		{
			$tmp = $this->db->query(DingoSQL::build_count($query,'pgsql'));
			$data = $tmp[0]['num'];
			
		}
		// Updates
		elseif($query->type == 'update')
		{
			$data = $this->db->query(DingoSQL::build_update($query,'pgsql'));
		}
		// Deletes
		elseif($query->type == 'delete')
		{
			$data = $this->db->query(DingoSQL::build_delete($query,'pgsql'));
		}
		
		return $data;
	}
}