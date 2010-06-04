<?php

require_once('db_init.php');

if (isset($_REQUEST['id']) && isset($_REQUEST['pin']))
  if (($user = User::find('id', $_REQUEST['id'])) !== NULL
      && $user->email_pin === $_REQUEST['pin'])
    $user->email_confirmed = TRUE;

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
      <fieldset style="width:22em;"><legend>Successl</legend>
  <p style="color:navy;">Your account has been confirmed.  Click on the Watercooler logo to bring up the login page.</p>
  <p style="color:navy;">Note that you will not receive any text messages until you have confirmed your phone number via the settings page.</p>
	<p><a href="index.php">Return to the Watercooler homepage</a></p>
      </fieldset>
    </div>
  </body>
</html>
