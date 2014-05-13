<?php //if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Sockets Helper
 * - Requires the XML Helper to be loaded in order to work!
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class socket
{
	private $_location;
	private $_port;
	private $_socket;
	private $_type;
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($location,$port)
	{
		$this->_location = $location;
		$this->_port = $port;
	}
	
	
	// Location
	// ---------------------------------------------------------------------------
	public function location($location='127.0.0.1')
	{
		$this->_location = $location;
	}
	
	
	// Port
	// ---------------------------------------------------------------------------
	public function port($port)
	{
		$this->_port = $port;
	}
	
	
	// Type (tcp or udp)
	// ---------------------------------------------------------------------------
	public function type($type)
	{
		$this->_type = $type;
	}
	
	
	// Create
	// ---------------------------------------------------------------------------
	public function create()
	{
		$this->_socket = socket_create(AF_INET,SOCK_STREAM,0);
		socket_bind($this->_socket,$this->_location,$this->_port);
		socket_listen($this->_socket);
		
		$x = true;
		
		while($x)
		{
			$client = socket_accept($this->_socket);
			
			$input = $input = socket_read($client,1024);
			
			socket_write($client,"Hello, World!");
			
			if($input)
			{
				$x = false;
			}
		}
		
		socket_close($client); 
		socket_close($this->_socket);

	}
}

$s = new socket('127.0.0.1',1234);
$s->create();
