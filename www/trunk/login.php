<?php
require_once('common.php');
require_once('db_init.php');

session_start();

if(isset($_REQUEST['userName']) && isset($_REQUEST['userPassword']))
  {
    $sessionUser = User::find('username',$_REQUEST['userName']);
    if ($sessionUser == NULL)
      {
	echo 'This username does not exist';
	exit();
      }
    if($sessionUser->password == md5($_REQUEST['userPassword']))
      {
	if($sessionUser->email_confirmed)
	  {
	    $_SESSION['uid'] = $sessionUser->uid;
	    $_SESSION['password'] = $sessionUser->password;

	    header("Location: {$page_uri_base}");
	  }
	else
	  {
	    echo 'You have not yet confirmed your email. If you need the '.
	      'confirmation email to be resent, please click '.
	      "<a href=\"sendConfirmation.php?id={$sessionUser->id}\">".
	      'here</a>.';
	  }
      }
    else
      {
	echo 'This password does not match the username.  Please try again.';
	exit();
      }
  }
