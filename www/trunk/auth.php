<?php
require_once('db_init.php');

session_start();

// XXX replace password for authentication with session id 
if (isset($_SESSION['uid']) && isset($_SESSION['password'])) {
  // find user in database
  $user = User::find('uid', $_SESSION['uid']);
  // end session if username does not exist or password does not match
  if ($user === NULL
      || $_SESSION['password'] != $user->password) {
    unset($_SESSION['uid']);
    unset($_SESSION['password']);
  }
}
