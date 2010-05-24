<?php
session_start();

if (isset($_REQUEST['submit'])) {
   var_dump($_REQUEST);
   if ($_REQUEST['username'] != '') {
       $_SESSION['username'] = $_REQUEST['username'];
       print("<br />\n".$_SESSION['username'].' logged in<br/>');
       print('<a href="index.php">home</a>');
   }

}
?>