<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Page Cache Library For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class cache
{
	private $location;
	private $cached;
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct()
	{
		$this->location = config::get('application').'/cache/'.CURRENT_PAGE;
	}
	
	
	// Create
	// ---------------------------------------------------------------------------
	public function create()
	{
		if(!is_dir($this->location))
		{
			mkdir($this->location,0777,TRUE);
		}
		
		$fh = fopen("{$this->location}.html",'w');
		fwrite($fh,ob_get_contents());
		fclose($fh);
	}
	
	
	// Delete
	// ---------------------------------------------------------------------------
	public function delete($page)
	{
		$file = config::get('application').'/cache/'.$page.'.html';
		
		if(file_exists($file))
		{
			unlink($file);
		}
	}
	
	
	// Start
	// ---------------------------------------------------------------------------
	public function start($loc=FALSE)
	{
		if($loc)
		{
			$this->location = config::get('application')."/cache/$loc";
		}
		
		
		if(file_exists("{$this->location}.html"))
		{
			$this->cached = TRUE;
			//header('Dingo:cache-loaded');
			require_once("{$this->location}.html");
			exit;
		}
		else
		{
			$this->cached = FALSE;
		}
	}
	
	
	// End
	// ---------------------------------------------------------------------------
	public function end()
	{
		if(!$this->cached)
		{
			$this->create();
		}
	}
}

register::library('cache',new cache());