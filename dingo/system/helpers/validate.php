<?php if(!defined('DINGO')){die('External Access to File Denied');}

/**
 * Dingo Framework Validation Helper
 *
 * @Author          Evan Byrne
 * @Copyright       2008 - 2009
 * @Project Page    http://www.dingoframework.com
 */

class valid
{
	// Username
	// ---------------------------------------------------------------------------
	static function username($username)
	{
		return preg_match('/^([-_ a-z0-9]+)$/is',$username);
	}
	
	
	// Name
	// ---------------------------------------------------------------------------
	static function name($name)
	{
		return preg_match('/^([ a-z]+)$/is',$name);
	}
	
	
	// Email Address
	// ---------------------------------------------------------------------------
	static function email($email)
	{
		return preg_match('/^([_\.a-z0-9]{3,})@([-_\.a-z0-9]{3,})\.([a-z]{2,})$/is',$email);
	}
	
	
	// Phone Number
	// ---------------------------------------------------------------------------
	static function phone($phone,$strict=false)
	{
		if(!$strict)
		{
			$phone = preg_replace('/([ \(\)-]+)/','',$phone);
		}
		
		return preg_match('/^([0-9]{10})$/',$phone);
	}
}