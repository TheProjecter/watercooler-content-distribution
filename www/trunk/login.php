<?php

include('db_init.php');

session_start();

if(isset($_REQUEST['userName']) && isset($_REQUEST['userPassword']))
  {
    $sessionUser = User::find('username',$_REQUEST['userName']);
    if ($sessionUser == NULL)
      {
	echo 'This username does not exist';
	exit();
      }
    $sessionPass = $sessionUser->get(array('password'));
    if($sessionPass['password'] == $_REQUEST['userPassword'])
      {
	$_SESSION['userName'] = $_REQUEST['userName'];

	//NOTE THIS WILL TO BE UPDATED CONSTANTLY UNTIL THE URL IS ESTABLISHED
	header( 'Location:http://www.geogriffin.info/watercooler/matt/watercooler-content-distribution/index.php');
      }
    else
      {
	echo 'This password does not match the username.  Please try again.';
	exit();
      }
  }
