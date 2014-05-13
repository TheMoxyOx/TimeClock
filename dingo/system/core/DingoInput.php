<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Input Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class DingoInput
{
	// Post
	// ---------------------------------------------------------------------------
	public function post($field)
	{
		if(isset($_POST[$field]))
		{
			return $_POST[$field];
		}
		else
		{
			return false;
		}
	}
	
	
	// Get
	// ---------------------------------------------------------------------------
	public function get($field)
	{
		if(isset($_GET[$field]))
		{
			return $_GET[$field];
		}
		else
		{
			return false;
		}
	}
	
	
	// Cookie
	// ---------------------------------------------------------------------------
	public function cookie($field)
	{
		if(isset($_COOKIE[$field]))
		{
			return $_COOKIE[$field];
		}
		else
		{
			return false;
		}
	}
	
	
	// Files
	// ---------------------------------------------------------------------------
	public function files($field)
	{
		if(isset($_FILES[$field]))
		{
			return $_FILES[$field];
		}
		else
		{
			return false;
		}
	}
	
	
	// Request
	// ---------------------------------------------------------------------------
	public function request($field)
	{
		if(isset($_REQUEST[$field]))
		{
			return $_REQUEST[$field];
		}
		else
		{
			return false;
		}
	}
}