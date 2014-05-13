<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Bootstrap Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class bootstrap
{
	// Core
	// ---------------------------------------------------------------------------
	static function core()
	{
		$dingo = new DingoCore();
		$dingo->load = new DingoLoad();
		$dingo->load->dingo = $dingo;
		$dingo->input = new DingoInput();
		$dingo->input->dingo = $dingo;
		$dingo->message = new DingoMessage();
		$dingo->message->dingo = $dingo;
		
		return $dingo;
	}
	
	
	// Get URL
	// ---------------------------------------------------------------------------
	static function get_url($url,$route)
	{
		// Check routes
		if(!empty($route[preg_replace('/^(\/)/','',$url)]))
		{
			$url = $route[preg_replace('/^(\/)/','',$url)];
		}
		
		// Regex Routes
		$route_keys = array_keys($route);
		
		foreach($route_keys as $okey)
		{
			$key = ('/^'.str_replace('/','\\/',$okey).'$/');
			
			$url = preg_replace('/^(\/)/','',$url);
			
			if(preg_match($key,$url))
			{
				$url = preg_replace($key,$route[$okey],$url);
			}
		}
		
		return $url;
	}
	
	// Get the requested URL, parse it, then clean it up
	// ---------------------------------------------------------------------------
	static function get_request_url()
	{	
		// Get the filename of the currently executing script relative to docroot
		$url = (empty($_SERVER['PHP_SELF'])) ? $_SERVER['PHP_SELF'] : '/';
		
		// Get the current script name (eg. /index.php)
		$script_name = (isset($_SERVER['SCRIPT_NAME'])) ? $_SERVER['SCRIPT_NAME'] : $url;
		
		// Parse URL, check for PATH_INFO and ORIG_PATH_INFO server params respectively
		$url = (0 !== stripos($url, $script_name)) ? $url : substr($url, strlen($script_name));
		$url = (empty($_SERVER['PATH_INFO'])) ? $url : $_SERVER['PATH_INFO'];
		$url = (empty($_SERVER['ORIG_PATH_INFO'])) ? $url : $_SERVER['ORIG_PATH_INFO'];
		
		//Tidy up the URL by removing trailing slashes
		$url = (!empty($url)) ? rtrim($url, '/') : '/';
		
		return $url;
	}
	
	// Run
	// ---------------------------------------------------------------------------
	static function run()
	{
		global $application;
		global $system;
		global $config;
		global $allowed_chars;
		global $dingo;
		global $_controller;
		
		define('DINGO_VERSION','0.4.2');
		define('APPLICATION',$application);
		define('SYSTEM',$system);
		ob_start();
		
		// Load Core Files
		require_once("$system/core/DingoCore.php");
		require_once("$system/core/DingoConfig.php");
		require_once("$system/core/DingoLoad.php");
		require_once("$system/core/DingoInput.php");
		require_once("$system/core/DingoError.php");
		require_once("$application/$config/config.php");
		
		set_error_handler('dingo_error');
		set_exception_handler('dingo_exception');
		
		$plugin = array();
		
		// Load Core Classes
		$dingo = bootstrap::core();
		
		config::set('system',$system);
		config::set('application',$application);
		config::set('config',$config);
		
		// Load Application Configurations
		require_once("$application/$config/routes.php");
		require_once("$application/$config/plugins.php");
		
		
		// Get URL
		//----------------------------------------------------------------------------------------------
		$model = FALSE;
		$url = bootstrap::get_url(bootstrap::get_request_url(),$route);

		// If URL is empty use default route
		if(empty($url) || $url == '/')
		{
			$url = $route['default_route'];
		}
		
		// Remove the /index.php/ at the beginning
		$url = preg_replace('/^(\/)/','',$url);
		
		
		// Plugins
		//----------------------------------------------------------------------------------------------
		if(is_array($plugin))
		{
			$tmp = array_keys($plugin);
			$x = 0;
		
			foreach($tmp as $plug)
			{
				if(preg_match("/^($plug\/)/",$url))
				{
					$url = preg_replace("/^($plug\/)/",'',$url);
					
					$application = "$application/".config::get('folder_plugins')."/{$plugin[$plug]}";
					$dingo->plugin = $plugin[$plug];
					$dingo->plugin_dir = config::get('folder_plugins')."/$plugin[$plug]";
					
					define('CURRENT_PLUGIN',$plug);
				}
				
				$x += 1;
			}
		}
		
		define('CURRENT_PAGE',$url);
		
		// Split URL into array
		$url = explode('/',$url);
		
		
		// Detect Illegal Characters in URL
		//----------------------------------------------------------------------------------------------
		foreach($url as $segment)
		{
			if(!preg_match($allowed_chars,$segment))
			{
				$dingo->load->error('general','Invalid URL','The requested URL contains invalid characters.');
			}
		}
		
		
		// Load Controller
		//----------------------------------------------------------------------------------------------
		
		// If controller does not exist, give 404 error
		if(!file_exists("$application/".config::get('folder_controllers')."/{$url[0]}.php"))
		{
			$dingo->load->error('404');
		}
		
		// Otherwise, load controller
		require_once("$application/".config::get('folder_controllers')."/{$url[0]}.php");
		$_controller = new controller;
		$_controller->dingo = $dingo;
		
		
		// Build arguments list and set default method for controller (if not specified)
		//----------------------------------------------------------------------------------------------
		
		$arguments = array();
		
		if(!empty($url[1]))
		{	
			// Get PHP to do the array slicing for arguments
			$arguments = array_slice($url, 2);
		}
		else
		{
			// If no function is defined in the URL use "index"
			$url[1] = 'index';
		}
		
		
		// Autoload Components
		//----------------------------------------------------------------------------------------------
		bootstrap::autoload();
		
		
		// Shorten Function "Path"
		//----------------------------------------------------------------------------------------------
		foreach($_controller->dingo->load->libraries as $lib)
		{
			if(isset($_controller->dingo->$lib))
			{
				$_controller->$lib = $_controller->dingo->$lib;
			}
		}
		
		
		// Check to see if function exists
		//----------------------------------------------------------------------------------------------
		if(!is_callable(array($_controller,$url[1])))
		{
			$_controller->dingo->load->error('404');
		}
		
		
		// Run Function
		//----------------------------------------------------------------------------------------------
		call_user_func_array(array($_controller,$url[1]),$arguments);
		
		
		// Display echoed content
		ob_end_flush();
	}
	
	
	// Autoload
	// ---------------------------------------------------------------------------
	static function autoload()
	{
		global $dingo;
		global $_controller;
		
		// Autoload Libraries
		foreach(config::get('autoload_libraries') as $class)
		{
			$dingo->load->library($class);
		}
		
		if(!empty($_controller->autoload_libraries) AND is_array($_controller->autoload_libraries))
		{
			foreach($_controller->autoload_libraries as $class)
			{
				$_controller->dingo->load->library($class);
			}
		}
		
		// Autoload Helpers
		foreach(config::get('autoload_helpers') as $helper)
		{
			$dingo->load->helper($helper);
		}
		
		if(!empty($_controller->autoload_helpers) AND is_array($_controller->autoload_helpers))
		{
			foreach($_controller->autoload_helpers as $helper)
			{
				$_controller->dingo->load->helper($helper);
			}
		}
	}
}