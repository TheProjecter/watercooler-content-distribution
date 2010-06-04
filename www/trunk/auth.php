<?php
require_once('db_init.php');

session_start();

// XXX replace password for authentication with session id 
if (isset($_SESSION['uid']) && isset($_SESSION['password'])) {
  // find user in database
  $user = User::find('uid', $_SESSION['uid']);
  /* end session if username does not exist or password does not match
     or user is not confirmed by email */
  if ($user === NULL
      || $_SESSION['password'] != $user->password
      || !$user->email_confirmed) {
    unset($_SESSION['uid']);
    unset($_SESSION['password']);
    unset($user);
  }
}
