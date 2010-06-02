<?php

$db = DB::getSiteDefault();
$db_feeds = $db->getFeeds();
foreach ($db_feeds as $feed)
  {
    $subscriptions = $feed->getUserCount();
    print("<div><div style=\"float:right;\">{$subscriptions}</div><div class=\"feedName\">{$feed->name}</div></div>");
  }

 ?>