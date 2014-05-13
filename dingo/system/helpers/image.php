<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Image Helper
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class image
{
	private $_image;
	private $_type;
	private $_quality = 100;
	public $width;
	public $height;
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($file)
	{
		$this->open($file);
	}
	
	
	// Open Image
	// ---------------------------------------------------------------------------
	public function open($file)
	{
		// Check to see if image exists
		if(!file_exists($file))
		{
			throw_error("Image file not found: The image file ($file) could not be found.",E_USER_ERROR);
		}
		
		// Attempt to open image
		else
		{
			switch(exif_imagetype($file))
			{
				case IMAGETYPE_JPEG:
					$this->_image = imagecreatefromjpeg($file);
					$this->_type = 'jpg';
				break;
				case IMAGETYPE_GIF:
					$this->_image = imagecreatefromgif($file);
					$this->_type = 'gif';
				break;
				case IMAGETYPE_PNG:
					$this->_image = imagecreatefrompng($file);
					$this->_type = 'png';
				break;
				default:
					throw_error('Invalid image: The given image could not be opened.',E_USER_ERROR);
				break;
			}
			
			$this->width = imagesx($this->_image);
			$this->height = imagesy($this->_image);
		}
		
		return $this;
	}
	
	
	// Set Image Type
	// ---------------------------------------------------------------------------
	public function type($type)
	{
		// Check to see if supported image type
		if(($type != 'jpg') AND ($type != 'gif') AND ($type != 'png'))
		{
			throw_error("Invalid image type: The given image type ($type) was not valid.",E_USER_ERROR);
		}
		else
		{
			$this->_type = strtolower($type);
		}
		
		return $this;
	}
	
	
	// Image Quality
	// ---------------------------------------------------------------------------
	public function quality($q=100)
	{
		$this->_quality = $q;
		return $this;
	}
	
	
	// Resize
	// ---------------------------------------------------------------------------
	public function resize($x=1,$y=1)
	{
		$tmp = imagecreatetruecolor($x,$y);
		imagecopyresampled($tmp,$this->_image,0,0,0,0,$x,$y,$this->width,$this->height);
		$this->_image = $tmp;
		
		return $this;
	}
	
	
	// Crop
	// ---------------------------------------------------------------------------
	public function crop($x=0,$y=0,$width=1,$height=1)
	{
		
		$tmp = imagecreatetruecolor($width,$height);
		imagecopyresampled($tmp,$this->_image,0,0,$x,$y,$width,$height,$width,$height);
		//imagecopyresampled($tmp,$this->_image,0,0,$x2,$y2,$this->width,$this->height);
		$this->_image = $tmp;
		
		return $this;
	}
	
	
	// Rotate
	// ---------------------------------------------------------------------------
	public function rotate($deg=0,$bg=0)
	{
		$this->_image = imagerotate($this->_image,$deg,$bg);
		
		return $this;
	}
	
	
	// Save Image
	// ---------------------------------------------------------------------------
	public function save($file)
	{
		// JPG
		if($this->_type == 'jpg')
		{
			imagejpeg($this->_image,$file,$this->_quality);
		}
		
		// GIF
		elseif($this->_type == 'gif')
		{
			imagegif($this->_image,$file,$this->_quality);
		}
		
		// PNG
		elseif($this->_type == 'png')
		{
			imagepng($this->_image,$file,$this->_quality);
		}
		
		return $this;
	}
	
	
	// Show Image
	// ---------------------------------------------------------------------------
	public function show()
	{
		// JPG
		if($this->_type == 'jpg')
		{
			header('Content-type:image/jpeg');
			imagejpeg($this->_image,NULL,$this->_quality);
		}
		
		// GIF
		elseif($this->_type == 'gif')
		{
			header('Content-type:image/gif');
			imagegif($this->_image,NULL,$this->_quality);
		}
		
		// PNG
		elseif($this->_type == 'png')
		{
			header('Content-type:image/png');
			imagepng($this->_image,NULL,$this->_quality);
		}
		
		return $this;
	}
	
	
	// Close
	// ---------------------------------------------------------------------------
	public function close()
	{
		imagedestroy($this->_image);
		
		return $this;
	}
}