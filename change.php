<?php
require_once 'classes/Membership.php';

$membership = New Membership();

$membership->confirm_Member();

if($membership->auto_logout("user_time"))
    {
        session_unset();
        session_destroy();
        header("location: expired.php");          
        exit;
}  

if ($_SESSION['current']=='in') {
	$membership->clock_out($_SESSION['username']);
}
else {
	$membership->clock_in($_SESSION['username']);
}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>
<?php header("location: index.php"); ?>
got here
<form action="index.php" method="get">
<input type="submit">
</form>
<body>
</body>
</html>