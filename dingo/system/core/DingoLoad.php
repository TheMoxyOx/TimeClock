<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Load Class
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class DingoLoad
{
	public $libraries = array('load','input','access','data','message');
	
	
	// Models
	// ---------------------------------------------------------------------------
	public function model($model,$plugin=FALSE)
	{
		// Determine path to model
		if($plugin)
		{
			$path = config::get('application')."/{$this->dingo->plugin_dir}/".config::get('folder_models')."/$model.php";
		}
		else
		{
			$path = config::get('application')."/".config::get('folder_models')."/$model.php";
		}
		
		// If model does not exist display error
		if(!file_exists($path))
		{
			dingo_error(E_USER_ERROR,"The requested model ($path) could not be found.");
		}
		else
		{
			require_once($path);
			
			// Create Class
			$model_class = $model.'_model';
			$m = new $model_class();
			
			// Get controller libraries
			global $_controller;
			$dingo = $_controller->dingo;
			
			foreach($dingo->load->libraries as $lib)
			{
				if(isset($dingo->$lib))
				{
					$m->$lib = $dingo->$lib;
				}
			}
			
			// Check to see if index() funciton exists
			if(is_callable(array($m,'index')))
			{
				// Run index()
				$m->index();
			}
			
			// Return model class
			return $m;
		}
	}
	
	// Plugin Model
	public function plugin_model($model)
	{
		return $this->model($model,TRUE);
	}
	
	
	// Errors
	// ---------------------------------------------------------------------------
	public function error($type = 'general',$title = NULL,$message = NULL)
	{
		ob_clean();
		//$this->dingo = $this;
		foreach($this->dingo->load->libraries as $lib)
		{
			if(isset($this->dingo->$lib))
			{
				$this->$lib = $this->dingo->$lib;
			}
		}
		require_once(config::get('application').'/'.config::get('folder_errors')."/$type.php");
		ob_end_flush();
		exit;
	}
	
	
	// Languages
	// ---------------------------------------------------------------------------
	public function language($language,$data = NULL)
	{
		// If view does not exist display error
		if(!file_exists(config::get('application').'/'.config::get('folder_languages')."/$language.php"))
		{
			dingo_error(E_USER_WARNING,'The requested language ('.config::get('application').'/'.config::get('folder_languages')."/$language.php) could not be found.");
		}
		else
		{
			// If data is array, convert keys to variables
			if(is_array($data))
			{
				extract($data, EXTR_OVERWRITE);
			}
			
			//$this->dingo = $this;
			foreach($this->dingo->load->libraries as $lib)
			{
				if(isset($this->dingo->$lib))
				{
					$this->$lib = $this->dingo->$lib;
				}
			}
			require(config::get('application').'/'.config::get('folder_languages')."/$language.php");
		}
	}
	
	
	// Views
	// ---------------------------------------------------------------------------
	public function view($view,$data = NULL)
	{
		// If view does not exist display error
		if(!file_exists(config::get('application').'/'.config::get('folder_views')."/$view.php"))
		{
			dingo_error(E_USER_WARNING,'The requested view ('.config::get('application').'/'.config::get('folder_views')."/$view.php) could not be found.");
		}
		else
		{
			// If data is array, convert keys to variables
			if(is_array($data))
			{
				extract($data, EXTR_OVERWRITE);
			}
			
			//$this->dingo = $this;
			foreach($this->dingo->load->libraries as $lib)
			{
				if(isset($this->dingo->$lib))
				{
					$this->$lib = $this->dingo->$lib;
				}
			}
			require(config::get('application').'/'.config::get('folder_views')."/$view.php");
		}
	}
	
	// Plugin View
	public function plugin_view($view,$data = NULL)
	{
		$this->view("../{$this->dingo->plugin_dir}/".config::get('folder_views')."/$view",$data);
	}
	
	
	// Helpers
	// ---------------------------------------------------------------------------
	public function helper($helper,$data = NULL)
	{
		$path = config::get('application').'/'.config::get('folder_helpers')."/$helper.php";
		
		// If helper does not exist in application folder
		if(!file_exists($path))
		{
			// And helper does not exist in system folder throw error
			if(!file_exists(config::get('system').'/'.config::get('folder_helpers')."/$helper.php"))
			{
				dingo_error(E_USER_WARNING,'The requested helper ('.config::get('application').'/'.config::get('folder_helpers')."/$helper.php) could not be found.");
				return FALSE;
			}
			
			// If helper is found in system folder load it
			else
			{
				$path = config::get('system')."/helpers/$helper.php";
			}
		}
		
		// If data is array, convert keys to variables
		if(is_array($data))
		{
			extract($data, EXTR_OVERWRITE);
		}
		
		//$this->dingo = $this;
		foreach($this->dingo->load->libraries as $lib)
		{
			if(isset($this->dingo->$lib))
			{
				$this->$lib = $this->dingo->$lib;
			}
		}
		
		require($path);
	}
	
	// Plugin Helper
	public function plugin_helper($helper,$data = NULL)
	{
		$this->helper("../{$this->dingo->plugin_dir}/".config::get('folder_helpers')."/$helper",$data);
	}
	
	
	// Libraries
	// ---------------------------------------------------------------------------
	public function library($class)
	{
		// If class does not exist display error
		if(!file_exists(config::get('system')."/libraries/$class.php"))
		{
			dingo_error(E_USER_WARNING,'The requested library ('.config::get('system')."/libraries/$class.php) could not be found.");
		}
		else
		{
			// If not already loaded then load
			if(!in_array($class,$this->libraries))
			{
				$this->libraries[] = $class;
				global $_controller;
				$dingo = $this->dingo;
				require(config::get('system')."/libraries/$class.php");
			}
		}
	}
}