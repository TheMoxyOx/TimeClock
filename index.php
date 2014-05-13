<?php

require_once 'classes/Membership.php';

$membership = New Membership();




$membership->confirm_Member();



if ($_SESSION['current']=='in') {
	$_SESSION['notcurrent'] = 'out';}
else {$_SESSION['notcurrent'] = 'in';}

if (!isset($_SESSION['total']))
    $_SESSION['total'] = 0;
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" href="css/default.css" />

<!--[if lt IE 7]>
<script type="text/javascript" src="js/DD_belatedPNG_0.0.7a-min.js"></script>
<![endif]-->


<title>Timeclock</title>



</head>

<body>

<div id="container">
  <img id="logo" src="images/logo.png" alt="logo">
	<p id="greeting" style="text-align: center;">
    	Hello, <?php echo $_SESSION['username']; ?> <br> you are currently clocked <strong><?php echo $_SESSION['current']; ?></strong>
    </p>
    <a id="logout" href="login.php?status=loggedout">Log Out</a>
    <div  id="clockbutton" >
   		<form  action="change.php" method="get">
   		  <div align="center">
   		    <input id="clockform" type="submit" value="<?php echo $_SESSION['notcurrent'];?>" />
	      </div>
        <br>
   		</form>
    </div>
    <div>
        <?php
            $membership->create_view($_SESSION['username']);

         ?>
    </div>
    <form id="mail" action="mail.php" method="get">
        <input type="submit" value="Mail">
        </form>
    
</div><!--end container-->

</body>
</html>
