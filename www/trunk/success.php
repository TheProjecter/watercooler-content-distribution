<?php

function format($cell)
{
  $retval = '(' . substr($cell,0,3) . ') ' . substr($cell,3,3) . '-' . substr($cell,6,4); 
  return $retval;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <link rel="SHORTCUT ICON" href="http://geogriffin.mine.nu/watercooler/matt/watercooler-content-distribution/favicon.ico" />
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Welcome to the Watercooler!</title>
    <link rel="stylesheet" title="watercooler" href="watercooler.css" type="text/css"/>
  </head>
  <body>
    <div id="wrap">
      <div id="logo">
	<a href="index.php"><img src="watercooler_logo.png" alt="Welcome to the Watercooler" /></a>
      <div id="logo">
      <fieldset style="width:22em;"><legend>Registration Successful</legend>
  <p style="color:navy;">A confirmation email has been sent to <?= $_REQUEST['email'] ?>.  Please follow the link in the email to activate your Watercooler account.</p>
  <p style="color:navy;">In addition, a confirmation text message has been sent to <?php echo format($_REQUEST['cell']) ?>.  Enter the pin number in the subject of the text message into the corresponding field in your settings page to activate sms alerts.</p>
	<p><a href="index.php">Return to the Watercooler homepage</a></p>
      </fieldset>
    </div>
  </body>
</html>
