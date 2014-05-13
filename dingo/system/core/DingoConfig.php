<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Config Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class config
{
	// Set
	// ---------------------------------------------------------------------------
	static function set($name,$value=NULL)
	{
		config::go('set',$name,$value);
	}
	
	
	// Get
	// ---------------------------------------------------------------------------
	static function get($name)
	{
		$tmp = config::go('all',NULL);
		
		if(isset($tmp[$name]))
		{
			return $tmp[$name];
		}
		else
		{
			return FALSE;
		}
	}
	
	
	// Remove
	// ---------------------------------------------------------------------------
	static function remove($name)
	{
		config::go('remove',$name);
	}
	
	
	// Go (Handles Set, Get and Remove)
	// ---------------------------------------------------------------------------
	static function go($action,$name,$value=NULL)
	{
		static $settings = array();
		
		// Set
		if($action == 'set')
		{
			$settings[$name] = $value;
		}
		
		// Remove
		elseif($action == 'remove')
		{
			if(isset($settings[$name]))
			{
				unset($settings[$name]);
			}
		}
		
		// All
		elseif($action == 'all')
		{
			return $settings;
		}
	}
}