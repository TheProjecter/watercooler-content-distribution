<?php
require_once('common.php');
require_once('db_init.php');

// XXX add authentication to this
$user = User::find('id', $_REQUEST['id']);
if ($user !== NULL && !$user->email_confirmed) {
  $hyperlink = 'confirm.php' . "?id={$user->id}&pin={$user->email_pin}";
  $confirmationString = "python2.5 -c \"import EmailServer; EmailServer.sendConfirmEmail('{$page_uri_base}{$hyperlink}','{$user->username}','{$user->email}');\"";
  exec($confirmationString);
}
