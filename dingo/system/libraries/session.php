<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Session Class For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class session
{
	private $config;
	private $dingo;
	private $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789';
	private $table = array();
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($dingo)
	{
		$this->config = config::get('session');
		$this->dingo = $dingo;
		$this->cleanup();
	}
	
	
	// Reset DB Rows
	// ---------------------------------------------------------------------------
	public function cleanup()
	{
		$d = new DateTime();
		
		$table = $this->dingo->db->table($this->config['table']);
		
		$table->delete('expire','<=',$d->format('U'));
	}
	
	
	// Get Session
	// ---------------------------------------------------------------------------
	public function get($session)
	{
		// Check to see if in PHP table already
		if(isset($this->table[$session]))
		{
			return $this->table[$session];
		}
		
		// If not then query database
		else
		{
			$table = $this->dingo->db->table($this->config['table']);
			
			$res = $table->select('value')
						 ->where('name','=',$session)
						 ->clause('AND')
						 ->where('cookie','=',$this->dingo->input->cookie($session))
						 //->where('cookie','=',$dingo->db->clean($dingo->input->cookie($session)))
						 ->execute();
			
			if(isset($res[0]['value']))
			{
				return $res[0]['value'];
			}
			else
			{
				return FALSE;
			}
		}
	}
	
	
	// Set Session
	// ---------------------------------------------------------------------------
	public function set($name,$value,$cookie=FALSE)
	{
		if(!$cookie)
		{
			$cookie = $this->config['cookie'];
		}
		
		$d = new DateTime();
		$d->modify($cookie['expire']);
		
		$salt = $this->salt();
		
		$cookie['name'] = $name;
		$cookie['value'] = $salt;
		
		if(empty($cookie['expire']))
		{
			$cookie['expire'] = '+1 hours';
		}
		
		cookie::set($cookie);
		
		$table = $this->dingo->db->table($this->config['table']);
		
		$table->insert(array(
			'name'=>$name,
			'value'=>$value,
			'cookie'=>$salt,
			'expire'=>$d->format('U')
		));
		
		$this->table[$name] = $value;
	}
	
	
	// Reset Session
	// ---------------------------------------------------------------------------
	public function reset($name,$cookie=FALSE)
	{
		if(!$cookie)
		{
			$cookie = $this->config['cookie'];
		}
		
		$value = $this->get($name);
		$this->delete($name,$cookie);
		$this->set($name,$value,$cookie);
		
		$this->table[$name] = $value;
	}
	
	
	// Update Session
	// ---------------------------------------------------------------------------
	public function update($name,$value)
	{
		$table = $this->dingo->db->table($this->config['table']);
		
		$table->update(array('value'=>$value))
		      ->where('cookie','=',$this->dingo->input->cookie($name))
		      ->execute();
		
		$this->table[$name] = $value;
	}
	
	
	
	// Delete Session
	// ---------------------------------------------------------------------------
	public function delete($name,$cookie=FALSE)
	{
		if(!$cookie)
		{
			$cookie = $this->config['cookie'];
		}
		
		$cookie['name'] = $name;
		cookie::delete($cookie);
		
		$table = $this->dingo->db->table($this->config['table']);
		
		$table->delete('cookie','=',$this->dingo->input->cookie($name));
	}
	
	
	// Generate Unique Salt
	// ---------------------------------------------------------------------------
	public function salt()
	{
		$len = rand(10,25);
		
		$salt = '';
		$i = 0;

		while($i < $len)
		{
			$char = substr($this->chars, mt_rand(0, strlen($this->chars) - 1), 1);
			$salt .= $char;
			$i++;
		}
		
		return $salt;
	}
}

register::library('session',new session($dingo));