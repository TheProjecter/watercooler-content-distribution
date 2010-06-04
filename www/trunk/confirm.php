<?php

require_once('db_init.php');

if (isset($_REQUEST['id']) && isset($_REQUEST['pin']))
  if (($user = User::find('id', $_REQUEST['id'])) !== NULL
      && $user->email_pin === $_REQUEST['pin'])
    $user->email_confirmed = TRUE;
