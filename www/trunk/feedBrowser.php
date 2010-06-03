<?php

$db = DB::getSiteDefault();
$db_feeds = $db->getFeeds();
$db_feeds->sortByPopularity();
foreach ($db_feeds as $feed)
  {
    $subscriptions = $feed->getUserCount();
    print("<div><div style=\"float:right;margin-right:0.5em;\">{$subscriptions}</div><img onclick=\"addFeed()\" style=\"float:left; margin-right:.5em;\" src=\"rss_small.png\" alt=\"add feed\"></img><div class=\"feedName\">{$feed->name}</div></div>");
  }

 ?>