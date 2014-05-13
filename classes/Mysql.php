<?php

require_once 'includes/constants.php';

class Mysql {
	private $conn;

//  build connection function
function __construct() {
	$this->conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME) or 
		die('There was a problem connecting to the database.');
}
// Verify user credentials. arguments, username string, password string.
function verify_Username_and_Pass($un, $pwd) {
	$query = "SELECT *
			FROM users
			WHERE username = ? AND password = ?
			LIMIT 1";
				
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('ss', $un, $pwd);
		$stmt->execute();
			
		if($stmt->fetch()) {
				$stmt->close();
				return true;
		}
	}
		
}

// checks current status (in or out). argument: username string.		
function check_current($un) {		
	$query1 = "SELECT current
			FROM users
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query1)) {
		$stmt1->bind_param('s', $un);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['current']);
		
		if($stmt1->fetch()) {
			$stmt1->close();
			return true;
		}
	}
	
}	
// toggles current status between in and out. argument: username string.	
function update_current($un) {
	$curtime = time();
	if ($_SESSION['current'] == 'in') {
	$query = "UPDATE users SET stop = ? WHERE username =?";
			
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('ds', $curtime, $un);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();		
		}
	}		
	$query = "UPDATE users SET current = 'out' WHERE username =?";
			
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('s', $un);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();		
		}
	}

	}
	else {
		$query = "UPDATE users SET start = ? WHERE username =?";
			
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('ds', $curtime, $un);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();		
		}
	}		
		$query = "UPDATE users SET current = 'in' WHERE username =?";
			
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('s', $un);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();
		}
	}
	
	}
}


// clock in: records time clocked in and date.
function clock_in ($username) {
//get current date
	$date = time();
// prepare and execute statement to store username and start time
	$query = "INSERT INTO times (username, starttime) VALUES (?, ?)";
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('sd', $username, $date);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();
		}
	}
// updates current status to "in"
	$this->update_current($username);
}





//clock out: generates a DB row: Stop, inserts before total. 
// and updates total.
function clock_out ($username) {
// gets current date	
	$date = time();

	$query_start = "SELECT starttime
			FROM times
			WHERE stoptime = 0 AND username = ?";
			
	if($stmt1 = $this->conn->prepare($query_start)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($starttime);
		
		if($stmt1->fetch()) {
			$stmt1->close();

		}
	}
// prepare and execute statement to store username and start time
	$query = "UPDATE times SET stoptime = ? WHERE username = ? AND starttime = ?";
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('dsd', $date, $username, $starttime);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();
		}
	}
// updates current status to "out"
	$this->update_current($username);

//updates line total to current total for shift
	$total = $date - $starttime;
	$query = "UPDATE times SET total = ? WHERE username = ? AND starttime = ?";
	if($stmt = $this->conn->prepare($query)) {
		$stmt->bind_param('dsd', $total, $username, $starttime);
		$stmt->execute();
		
		if($stmt->fetch()) {
			$stmt->close();
		}
	}		
}
// get all data matching current username, argument: username string.
function fetch_times($username) {		
	$query = "SELECT starttime, stoptime, total
			FROM times
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['starttime'], $_SESSION['stoptime'], $_SESSION['total']);
		echo "<table border='1'>";
		$_SESSION['grandtotal'] = 0;
		$curDate = time();
		$curDayNum = date(N, $curDate);
		$curDay = date(z, $curDate);
		$curYear = date(Y, $curDate);
		while ($stmt1->fetch()){
			$starttime = $_SESSION['starttime'];
			$formattedDate = date(z, $starttime);
			$stoptime = $_SESSION['stoptime'];
			$total = $_SESSION['total'];
			
			if (($curDay >= $formattedDate ) && ($formattedDate >= ($curDay - $curDayNum))){
				$_SESSION['grandtotal'] = ($_SESSION['grandtotal'] + $total);
			$this->format_dates($starttime, $stoptime, $total);}
		}
	$ghours = floor($_SESSION['grandtotal'] / 3600);
	$gmins = floor(($_SESSION['grandtotal'] - ($ghours*3600)) / 60);
	$_SESSION['grandtotal'] = floor(($_SESSION['grandtotal'] - (($ghours*3600)+($gmins*60))));
	echo "<div id='total'>Total: ".$ghours.":".$gmins."</div>";
	echo "<div class='activity'><h1>Activity</h1><div>";
	echo "<tr id='rawtotal'><td>raw total: ".$ghours.":".$gmins.":".$_SESSION['grandtotal']."</td></tr>";
		if(($gmins > 0) &&($gmins < 6)) $gmins = 0;
	if(($gmins > 6) && ($gmins <= 15)) $gmins = 15;
	if(($gmins > 15) && ($gmins <= 21)) $gmins = 15;
	if(($gmins > 21) && ($gmins <= 30)) $gmins = 30;
	if(($gmins > 30) && ($gmins <= 36)) $gmins = 30;
	if(($gmins > 36) && ($gmins <= 45)) $gmins = 45;
	if(($gmins > 45) && ($gmins <= 51)) $gmins = 45;
	if(($gmins > 51) && ($gmins <= 60)) {$gmins = 0; $ghours = $ghours +1;}
	echo "<tr id='roundedtotal'><td>rounded total: </td><td>".$ghours.":".$gmins."</td></tr>";
	echo "</table>";
			$stmt1->close();

		
	}
	
}

//formats the date output
function format_dates ($starttime, $stoptime, $total) {
	$hours = floor($total / 3600);
	$mins = floor(($total - ($hours*3600)) / 60);
	$total = floor(($total - (($hours*3600)+($mins*60))));
	date_default_timezone_set('America/Chicago');
	echo "<tr><td>".date('D, F d', $starttime)."</td> ";
	if($stoptime != 0){
	echo "<td>".date('g:i:s A', $starttime)." - ".date('g:i:s A', $stoptime)."</td> ";}
	echo "<td>total: ".$hours.":".$mins.":".$total."</td>";
	echo "</tr>";
	
}

function generate_email($username) {
		$i = 0;
		$query = "SELECT DISTINCT username FROM times";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['name']);
		while ($stmt1->fetch()){
			$name[$i] = $_SESSION['name'];
			$i++;			
		}
			$stmt1->close();

		
	}
	$j=0;
	while ($j < $i) {
	if (($name[$j] != "kara") && ($name[$j] != "graham")){
	$_SESSION['classname'] = $name[$j];
	echo "<br><span class='empname' id='".$name[$j]."'>".$name[$j]."</span><br>";	
	$this->email_fetch_times($name[$j]);}	
	$j++;
	}
}
//checks if user is admin
function check_admin($un) {		
	$query1 = "SELECT admin
			FROM users
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query1)) {
		$stmt1->bind_param('s', $un);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['admin']);
		
		if($stmt1->fetch()) {
			$stmt1->close();
		}
	}
	
}


function admin_fetch_times($username) {		
	$query = "SELECT starttime, stoptime, total
			FROM times
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['starttime'], $_SESSION['stoptime'], $_SESSION['total']);
		echo "</div><table style='border:none' id='hidden".$_SESSION['classname']."' border='1'>";
		$_SESSION['grandtotal'] = 0;
		$curDate = time();
		$curDayNum = date(N, $curDate);
		$curDay = date(z, $curDate);
		$curYear = date(Y, $curDate);
		while ($stmt1->fetch()){
			$starttime = $_SESSION['starttime'];
			$formattedDate = date(z, $starttime);
			$stoptime = $_SESSION['stoptime'];
			$total = $_SESSION['total'];
			
			if (($curDay >= $formattedDate ) && ($formattedDate >= ($curDay - $curDayNum))){
				$_SESSION['grandtotal'] = ($_SESSION['grandtotal'] + $total);
			$this->admin_format_dates($starttime, $stoptime, $total);}
		}
	$ghours = floor($_SESSION['grandtotal'] / 3600);
	$gmins = floor(($_SESSION['grandtotal'] - ($ghours*3600)) / 60);
	$_SESSION['grandtotal'] = floor(($_SESSION['grandtotal'] - (($ghours*3600)+($gmins*60))));
	//echo "<div class=\"total_a\"> Total: ".$ghours.":".$gmins."</div>";
	//echo "<tr><td>raw total: ".$ghours.":".$gmins.":".$_SESSION['grandtotal']."</td></tr>";
		if(($gmins > 0) &&($gmins < 6)) $gmins = 0;
	if(($gmins > 6) && ($gmins <= 15)) $gmins = 15;
	if(($gmins > 15) && ($gmins <= 21)) $gmins = 15;
	if(($gmins > 21) && ($gmins <= 30)) $gmins = 30;
	if(($gmins > 30) && ($gmins <= 36)) $gmins = 30;
	if(($gmins > 36) && ($gmins <= 45)) $gmins = 45;
	if(($gmins > 45) && ($gmins <= 51)) $gmins = 45;
	if(($gmins > 51) && ($gmins <= 60)) {$gmins = 0; $ghours = $ghours +1;}
	echo "<tr ><td>Total: </td><td>".$ghours.":".$gmins."</td></tr>";
	echo "</table>";
			$stmt1->close();

		
	}
	
}

function admin_format_dates ($starttime, $stoptime, $total) {
	$hours = floor($total / 3600);
	$mins = floor(($total - ($hours*3600)) / 60);
	$total = floor(($total - (($hours*3600)+($mins*60))));
	date_default_timezone_set('America/Chicago');
	echo "<tr><td>".date('D, F d', $starttime)."</td> ";
	if($stoptime != 0){
	echo "<td>".date('g:i:s A', $starttime)." - ".date('g:i:s A', $stoptime)."</td> ";}
	echo "<td>total: ".$hours.":".$mins.":".$total."</td>";
	echo "</tr>";
	
}


function generate_js($username) {
		$i = 0;
		$query = "SELECT DISTINCT username FROM times";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['name']);
		while ($stmt1->fetch()){
			$name[$i] = $_SESSION['name'];
			$i++;			
		}
			$stmt1->close();

		
	}
	$j=0;
	while ($j < $i) {
	$_SESSION['classname'] = $name[$j];	
	echo "$(document).ready(function(){
  	$(\"#hidden".$_SESSION['classname']."\").hide();
  });";	
	echo "$(document).ready(function(){
  $(\"#".$_SESSION['classname']."\").click(function(){
    $(\"#hidden".$_SESSION['classname']."\").toggle();
  });
});";
	$j++;
	}
}


function generate_admin($username) {
		$i = 0;
		$query = "SELECT DISTINCT username FROM times";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['name']);
		while ($stmt1->fetch()){
			$name[$i] = $_SESSION['name'];
			$i++;			
		}
			$stmt1->close();

		
	}
	$j=0;
	while ($j < $i) {
	if (($name[$j] != "kara") && ($name[$j] != "graham")){
	$_SESSION['classname'] = $name[$j];
	echo "<br><div class='empname' id='".$name[$j]."'><strong>".$name[$j]."</strong>";	
	$this->admin_fetch_times($name[$j]);}	
	$j++;
	}
}


function email_fetch_times($username) {		
	$query = "SELECT starttime, stoptime, total
			FROM times
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['starttime'], $_SESSION['stoptime'], $_SESSION['total']);
		//echo "<table id='hidden".$_SESSION['classname']."' border='1'>";
		$_SESSION['grandtotal'] = 0;
		$curDate = time();
		$curDayNum = date(N, $curDate);
		$curDay = date(z, $curDate);
		$curDay = $curDay - 2;
		$curYear = date(Y, $curDate);
		while ($stmt1->fetch()){
			$starttime = $_SESSION['starttime'];
			$formattedDate = date(z, $starttime);
			$stoptime = $_SESSION['stoptime'];
			$total = $_SESSION['total'];
			
			if (($curDay >= $formattedDate ) && ($formattedDate >= ($curDay - $curDayNum - 6))){
				$_SESSION['grandtotal'] = ($_SESSION['grandtotal'] + $total);
			$this->email_format_dates($starttime, $stoptime, $total);}
		}
	$ghours = floor($_SESSION['grandtotal'] / 3600);
	$gmins = floor(($_SESSION['grandtotal'] - ($ghours*3600)) / 60);
	$_SESSION['grandtotal'] = floor(($_SESSION['grandtotal'] - (($ghours*3600)+($gmins*60))));
	//echo "<div class='emptotal' >Total: ".$ghours.":".$gmins."</div>";
	//echo "raw total: ".$ghours.":".$gmins.":".$_SESSION['grandtotal']."<br>";
		if(($gmins > 0) &&($gmins < 6)) $gmins = 0;
	if(($gmins > 6) && ($gmins <= 15)) $gmins = 15;
	if(($gmins > 15) && ($gmins <= 21)) $gmins = 15;
	if(($gmins > 21) && ($gmins <= 30)) $gmins = 30;
	if(($gmins > 30) && ($gmins <= 36)) $gmins = 30;
	if(($gmins > 36) && ($gmins <= 45)) $gmins = 45;
	if(($gmins > 45) && ($gmins <= 51)) $gmins = 45;
	if(($gmins > 51) && ($gmins <= 60)) {$gmins = 0; $ghours = $ghours +1;}
	echo "Total: ".$ghours.".".(($gmins/60)*100)."<br>";
			$stmt1->close();

		
	}
	
}


function email_format_dates ($starttime, $stoptime, $total) {
	$hours = floor($total / 3600);
	$mins = floor(($total - ($hours*3600)) / 60);
	$total = floor(($total - (($hours*3600)+($mins*60))));
	date_default_timezone_set('America/Chicago');
	//echo "<tr><td>".date('D, F d', $starttime)."</td> ";
	if($stoptime != 0){
	//echo "<td>".date('g:i:s A', $starttime)." - ".date('g:i:s A', $stoptime)."</td> ";
	//echo "<td>total: ".$hours.":".$mins.":".$total."</td>";
	//echo "</tr>";
	
	}
}

function generate_update ($username) {
	$query = "SELECT starttime, stoptime, total
			FROM times
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($_SESSION['starttime'], $_SESSION['stoptime'], $_SESSION['total']);
		//echo "<table id='hidden".$_SESSION['classname']."' border='1'>";
		$_SESSION['grandtotal'] = 0;
		$curDate = time();
		$curDayNum = date(N, $curDate);
		$curDay = date(z, $curDate);
		$curYear = date(Y, $curDate);
		while ($stmt1->fetch()){
			$starttime = $_SESSION['starttime'];
			$formattedDate = date(z, $starttime);
			$stoptime = $_SESSION['stoptime'];
			$total = $_SESSION['total'];
			
			if (($curDay >= $formattedDate ) && ($formattedDate >= ($curDay - $curDayNum))){
				$_SESSION['grandtotal'] = ($_SESSION['grandtotal'] + $total);
			$this->email_format_dates($starttime, $stoptime, $total);}
		}
	$ghours = floor($_SESSION['grandtotal'] / 3600);
	$gmins = floor(($_SESSION['grandtotal'] - ($ghours*3600)) / 60);
	$_SESSION['grandtotal'] = floor(($_SESSION['grandtotal'] - (($ghours*3600)+($gmins*60))));
	//echo "<div class='emptotal' >Total: ".$ghours.":".$gmins."</div>";
			if(($gmins > 0) &&($gmins < 6)) $gmins = 0;
	if(($gmins > 6) && ($gmins <= 15)) $gmins = 15;
	if(($gmins > 15) && ($gmins <= 21)) $gmins = 15;
	if(($gmins > 21) && ($gmins <= 30)) $gmins = 30;
	if(($gmins > 30) && ($gmins <= 36)) $gmins = 30;
	if(($gmins > 36) && ($gmins <= 45)) $gmins = 45;
	if(($gmins > 45) && ($gmins <= 51)) $gmins = 45;
	if(($gmins > 51) && ($gmins <= 60)) {$gmins = 0; $ghours = $ghours +1;}
	
$monday = "Hi, ".$username.".<br> You kicked this week off by logging".$ghours.":".$gmins."today. For your records, here is a summary of your time sheet for Monday:";	
	echo "Just a quick update, you have worked".$ghours.":".$gmins." so far this week. <br>";
			$stmt1->close();

	}

}






function send_mail ($username) {
require_once 'mail/lib/swift_required.php';

$query1 = "SELECT email
			FROM users
			WHERE username = ?";
			
	if($stmt1 = $this->conn->prepare($query1)) {
		$stmt1->bind_param('s', $username);
		$stmt1->execute();
		$stmt1->bind_result($emailAddress);
		
		if($stmt1->fetch()) {
			$stmt1->close();
		}
	}



			ob_start(); // Start output buffering
			$this->generate_update($username);
			$email = ob_get_contents(); // Store buffer in variable

			ob_end_clean(); // End buffering and clean up
			

// Create the Transport
$transport = Swift_SmtpTransport::newInstance('smtp.gmail.com', 465, 'ssl')
  ->setUsername('service@moxyox.com')
  ->setPassword('moxyox4180')
  ;

/*
You could alternatively use a different transport such as Sendmail or Mail:

// Sendmail
$transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

// Mail
$transport = Swift_MailTransport::newInstance();
*/

// Create the Mailer using your created Transport
$mailer = Swift_Mailer::newInstance($transport);

// Create a message
$message = Swift_Message::newInstance('Moxy Ox Hours')
  ->setFrom(array('service@moxyox.com' => 'Moxy Ox'))
  ->setTo(array($emailAddress => $username))
  ->setBody($email, 'text/html')
  ;

// Send the message
$result = $mailer->send($message);

	
}



}


