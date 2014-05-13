<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Core Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class DingoCore{}



/**
 * Dingo Framework Extention Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class dingo
{
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct()
	{
		global $_controller;
		
		// Load libraries into class
		foreach($_controller->load->libraries as $lib)
		{
			if(isset($_controller->$lib))
			{
				$this->$lib = $_controller->$lib;
			}
		}
	}
}



/**
 * Dingo Framework Global Data Class [Experimental]
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class DingoData{}



/**
 * Dingo Language Message Class [Experimental]
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class DingoMessage{}



/**
 * Dingo Cookie Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class cookie
{
	// Set Cookie
	// ---------------------------------------------------------------------------
	static function set($settings)
	{

		if(!isset($settings['path'])){$settings['path']='/';}
		if(!isset($settings['domain'])){$settings['domain']=FALSE;}
		if(!isset($settings['secure'])){$settings['secure']=FALSE;}
		if(!isset($settings['httponly'])){$settings['httponly']=FALSE;}
		if(!isset($settings['expire']))
		{
			$ex = new DateTime();
			$time = $ex->format('U');
		}
		else
		{
			$ex = new DateTime();
			$ex->modify($settings['expire']);
			$time = $ex->format('U');
		}
		
		return setcookie(
			$settings['name'],
			$settings['value'],
			$time,
			$settings['path'],
			$settings['domain'],
			$settings['secure'],
			$settings['httponly']
		);
	}
	
	
	// Delete Cookie
	// ---------------------------------------------------------------------------
	static function delete($settings)
	{
		if(!isset($settings['path'])){$settings['path']='/';}
		if(!isset($settings['domain'])){$settings['domain']=FALSE;}
		if(!isset($settings['secure'])){$settings['secure']=FALSE;}
		if(!isset($settings['httponly'])){$settings['httponly']=FALSE;}
		
		// If given array of settings
		if(is_array($settings))
		{
			return setcookie(
				$settings['name'],
				'',
				time()-3600,
				$settings['path'],
				$settings['domain'],
				$settings['secure'],
				$settings['httponly']
			);
		}
		// Else, just the cookie name was given
		else
		{
			return setcookie($settings,'',time()-3600);
		}
	}
}


/**
 * Dingo Register Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class register
{
	// Register Library
	// ---------------------------------------------------------------------------
	static function library($name,$object)
	{
		global $_controller;
		$_controller->dingo->$name = $object;
		$_controller->$name = $object;
	}
}