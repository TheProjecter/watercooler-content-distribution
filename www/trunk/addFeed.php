<?php
require_once('db_init.php');
require_once('auth.php');

if (isset($_REQUEST['id'])) {
  $feed = Feed::find('id', $_REQUEST['id']);
  if($feed !== NULL) {
    $user->addFeed($feed);
    echo $feed->id;
  }
}
