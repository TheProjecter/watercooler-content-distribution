<?php
require_once('db_init.php');

session_start();

// XXX implement authentication
if ($_SESSION['userName']) {
  $user = User::find('username', $_SESSION['userName']);
}
