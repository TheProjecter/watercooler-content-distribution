<?php
session_start();

if(isset($_REQUEST['submit']) && $_REQUEST['userName'] != '') {
    $_SESSION['userName'] = $_REQUEST['userName'];
    print($_SESSION['userName'].' logged in<br/>');
    print('<a href="index.php">home</a>');
}
