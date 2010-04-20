<?php
session_start();

if(isset($_REQUEST['submit']) && $_REQUEST['username'] != '') {
    $_SESSION['username'] = $_REQUEST['username'];
    print($_SESSION['username'].' logged in<br/>');
    print('<a href="index.php">home</a>');
}
