<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Capcha Library For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class capcha
{
	private $dingo;
	private $chars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789';
	
	private $_font_file;
	private $_font_size = FALSE;
	private $_font_color = array(20,40,100);
	private $_background_color = array(255,255,255);
	private $_noise_color = array(100,120,180);
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($dingo)
	{
		$this->dingo = $dingo;
	}
	
	
	// Font File
	// ---------------------------------------------------------------------------
	public function font_file($font_file)
	{
		$this->_font_file = $font_file;
		return $this;
	}
	
	
	// Font Size
	// ---------------------------------------------------------------------------
	public function font_size($font_size)
	{
		$this->_font_size = $font_size;
		return $this;
	}
	
	
	// Font Color
	// ---------------------------------------------------------------------------
	public function font_color($f1,$f2,$f3)
	{
		$this->_font_color = array($f1,$f2,$f3);
		return $this;
	}
	
	
	// Background Color
	// ---------------------------------------------------------------------------
	public function background_color($b1,$b2,$b3)
	{
		$this->_background_color = array($b1,$b2,$b3);
		return $this;
	}
	
	
	// Noise Color
	// ---------------------------------------------------------------------------
	public function noise_color($n1,$n2,$n3)
	{
		$this->_noise_color = array($n1,$n2,$n3);
		return $this;
	}
	
	
	// Generate
	// ---------------------------------------------------------------------------
	public function generate($id,$width=100,$height=40)
	{
		$code = $this->dingo->session->get("capcha_$id");
		
		// If not set then font size will be 75% size of height or width
		if(!$this->_font_size)
		{
			if($width > $height)
			{
				$this->_font_size = $height * 0.75;
			}
			else
			{
				$this->_font_size = $width * 0.75;
			}
		}
		
		// Create image
		$image = imagecreate($width,$height) or die('Cannot initialize new GD image stream');
		
		// set the colors
		$background_color = imagecolorallocate($image,$this->_background_color[0],$this->_background_color[1],$this->_background_color[2]);
		$text_color = imagecolorallocate($image,$this->_font_color[0],$this->_font_color[1],$this->_font_color[2]);
		$noise_color = imagecolorallocate($image,$this->_noise_color[0],$this->_noise_color[1],$this->_noise_color[2]);
		
		// Generate random dots in background
		for($i=0;$i<($width*$height)/3;$i++)
		{
			imagefilledellipse($image,mt_rand(0,$width),mt_rand(0,$height),1,1,$noise_color);
		}
		
		// Generate random lines in background
		for($i=0;$i<($width*$height)/150;$i++)
		{
			imageline($image,mt_rand(0,$width),mt_rand(0,$height),mt_rand(0,$width),mt_rand(0,$height),$noise_color);
		}
		
		// create textbox and add text
		$textbox = imagettfbbox($this->_font_size,0,$this->_font_file,$code) or die('Error in imagettfbbox function');
		$x = ($width - $textbox[4])/2;
		$y = ($height - $textbox[5])/2;
		imagettftext($image,$this->_font_size,0,$x,$y,$text_color,$this->_font_file,$code) or die('Error in imagettftext function');
		
		// Output captcha image to browser
		header('Content-Type:image/jpeg');
		imagejpeg($image);
		imagedestroy($image);
		
		return $this;
	}
	
	
	// Set
	// ---------------------------------------------------------------------------
	public function set($id,$len=4)
	{
		$this->dingo->session->delete("capcha_$id");
		$this->dingo->session->set("capcha_$id",$this->code($len));
		return $this;
	}
	
	
	// Delete
	// ---------------------------------------------------------------------------
	public function delete($id)
	{
		$this->dingo->session->delete("capcha_$id");
		return $this;
	}
	
	
	// Check
	// ---------------------------------------------------------------------------
	public function check($id,$value)
	{
		if($this->dingo->session->get("capcha_$id") == strtoupper($value))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	
	// Generate Unique Code
	// ---------------------------------------------------------------------------
	public function code($len)
	{
		$code = '';
		$i = 0;

		while($i < $len)
		{
			$char = substr($this->chars, mt_rand(0, strlen($this->chars) - 1), 1);
			$code .= $char;
			$i++;
		}
		
		return $code;
	}
}

register::library('capcha',new capcha($dingo));