<?php
require_once('db_init.php');

session_start();

//Fix this to work on user ID.  That way user isn't logged out after changing username in the settings page.

// XXX implement authentication
if ($_SESSION['userName']) {
  $user = User::find('username', $_SESSION['userName']);
}
