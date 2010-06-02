<?php

$db = DB::getSiteDefault();
$db_feeds = $db->getFeeds();
$db_feeds->sortByPopularity();
foreach ($db_feeds as $feed)
  {
    $subscriptions = $feed->getUserCount();
    print("<div><div style=\"float:right;margin-right:0.5em;\">{$subscriptions}</div><div class=\"feedName\">{$feed->name}</div></div>");
  }

 ?>