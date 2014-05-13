<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Talk Class For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class talk
{
	private $dingo;
	private $is_server;
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($dingo)
	{
		$this->dingo = $dingo;
		
		if($this->dingo->input->post('__talk'))
		{
			$this->is_server = TRUE;
		}
		else
		{
			$this->is_server = FALSE;
		}
	}
	
	
	// Request
	// ---------------------------------------------------------------------------
	public function request($url,$postdata=array())
	{
		$post = '__talk=1';
		
		foreach($postdata as $field => $value)
		{
			$post .= "&$field=$value";
		}
		
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		
		return curl_exec($ch);
	}
	
	
	// Is It A Talk Request?
	// ---------------------------------------------------------------------------
	public function is_request()
	{
		return $this->is_server;
	}
	
	
	// Respond
	// ---------------------------------------------------------------------------
	public function respond($response)
	{
		if($this->is_server)
		{
			ob_clean();
			echo $response;
			ob_end_flush();
		}
	}
}

register::library('talk',new talk($dingo));