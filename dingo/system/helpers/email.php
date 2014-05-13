<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Email Helper For Dingo Framework
 *
 * @author          Evan Byrne
 * @copyright       2008 - 2009
 * @project page    http://www.dingoframework.com
 */

class email
{
	private $to = array();
	private $from;
	private $content;
	private $subject;
	
	
	// Construct
	// ---------------------------------------------------------------------------
	public function __construct($to=FALSE,$from=FALSE,$content=FALSE,$subject=FALSE)
	{
		$this->to($to);
		$this->from($from);
		$this->content($content);
		$this->subject($subject);
	}
	
	
	// To
	// ---------------------------------------------------------------------------
	public function to()
	{
		$email = func_get_args();
		
		// If email is a list of addresses
		if(is_array($email))
		{
			// Add each address to array
			foreach($email as $address)
			{
				if($address)
				{
					$this->to[] = $address;
				}
			}
		}
		
		return $this;
	}
	
	
	// From
	// ---------------------------------------------------------------------------
	public function from($from=FALSE)
	{
		$this->from = trim($from);
		return $this;
	}
	
	
	// Subject
	// ---------------------------------------------------------------------------
	public function subject($title=FALSE)
	{
		$this->subject = $title;
		return $this;
	}
	
	
	// Content
	// ---------------------------------------------------------------------------
	public function content($message=FALSE)
	{
		$this->content = wordwrap($message,70);
		$this->content = str_replace("\n.", "\n..", $this->content);
		return $this;
	}
	
	
	// Send
	// ---------------------------------------------------------------------------
	public function send()
	{
		foreach($this->to as $to)
		{
			mail($to,$this->subject,$this->content,"From:{$this->from}\r\nMIME-Version: 1.0\r\nContent-type: text/html; charset=iso-8859-1\r\n");
		}
	}
}