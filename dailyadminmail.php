<?php
require_once 'classes/Membership.php';
require_once 'mail/lib/swift_required.php';

$membership = New Membership();
			ob_start(); // Start output buffering
			$membership->generate_admin($_SESSION['username']);
			$email = ob_get_contents(); // Store buffer in variable

			ob_end_clean(); // End buffering and clean up
			

$membership->confirm_Member();
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

$timestamp = time();
$day = date('l', $timestamp);

$subject = 'Employee Time Sheets - '.$day;
// Create a message
$message = Swift_Message::newInstance($subject)
  ->setFrom(array('service@moxyox.com' => 'Moxy Ox'))
  ->setTo(array('joe.payne@moxyox.com' => 'Joe Payne', 'david.baker@moxyox.com' => 'David Baker', 'stephen.pridmore@moxyox.com' => 'Stephen Pridmore'))
  ->setBody($email, 'text/html')
  ;

// Send the message
$result = $mailer->send($message);

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