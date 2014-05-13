<?php

require_once 'classes/Membership.php';

$membership = New Membership();


$membership->confirm_Member();


?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js">
</script>
<script type="text/javascript">
<?php $membership->generate_js($_SESSION['username']); ?>
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/default.css" />
<link rel="stylesheet" href="css/admin.css" />
<title>Untitled Document</title>
</head>

<body>
	<div id="container">

  <img id="logo" src="images/logo.png" alt="logo">
<a id="logout_a" href="login.php?status=loggedout">Log Out</a>
<div id="admintitle">TIME CLOCK ADMIN</div>
<?php $membership->generate_admin($_SESSION['username']); ?>
    
	</div>
</body>
</html>
