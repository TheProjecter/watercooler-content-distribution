<?php

require_once('db_init.php');

if (isset($_REQUEST['id']) && isset($_REQUEST['pin']))
  if (($user = User::find('id', $_REQUEST['id'])) !== NULL)
    // XXX validate pin and confirm user
    //$user->confirmed = TRUE;
    echo 'confirmed';