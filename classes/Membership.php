<?php

require 'Mysql.php';

class Membership {
	
// validates user credentials and builds session. arguments: username string, password string.	
	function validate_user($un, $pwd) {
		$mysql = New Mysql();
		$ensure_credentials = $mysql->verify_Username_and_Pass($un, md5($pwd));
		
		if($ensure_credentials) {
			$_SESSION['status'] = 'authorized';
			$_SESSION['username'] = $un;
			$mysql->check_current($un);
			header("location: index.php");
		} else return "Please enter a correct username and password";
		
	} 
//auto logout
function auto_logout($field) {
	    $t = time();
	    $t0 = $_SESSION[$field];
	    $diff = $t - $t0;
	    if ($diff > 150 || !isset($t0))
	    {          
	        return true;
	    }
	    else
	    {
	        $_SESSION[$field] = time();
    }
}

// Logs current user out of session.	
	function log_User_Out() {
		if(isset($_SESSION['status'])) {
			unset($_SESSION['status']);
			
			if(isset($_COOKIE[session_name()])) 
				setcookie(session_name(), '', time() - 1000);
				session_destroy();
		}
	}
// confirms that current viewer is authorized to view page.	
	function confirm_Member() {
		session_start();
		if(!isset($_SESSION['user_time'])){ $_SESSION['user_time'] = time();}
		if($_SESSION['status'] !='authorized') header("location: login.php");
	}

// calls mysql->update current and saves current in $_SESSION['current'].	
	function update_current($un) {
		$mysql = New Mysql();
		$mysql->clock_in($un);
		$mysql->check_current($un);
	}
//dummy functions
	function clock_in($username) {
		$mysql = New Mysql();
		$mysql->clock_in($username);
		$mysql->check_current($username);
	}
	function clock_out($username) {
		$mysql = New Mysql();
		$mysql->clock_out($username);
		$mysql->send_mail($username);
		$mysql->check_current($username);
	}

	function create_view($username) {
		$mysql = New Mysql();
		$mysql->fetch_times($username);

	}
		function generate_email($username) {
		$mysql = New Mysql();
		$mysql->generate_email($username);

	}
		function check_admin($username) {
		$mysql = New Mysql();
		$mysql->check_admin($username);

	}

		function generate_js($username) {
		$mysql = New Mysql();
		$mysql->generate_js($username);

	}

		function generate_admin($username) {
		$mysql = New Mysql();
		$mysql->generate_admin($username);

	}
	
		function send_mail($username) {
		$mysql = New Mysql();
		$mysql->send_mail($username);

	}

}