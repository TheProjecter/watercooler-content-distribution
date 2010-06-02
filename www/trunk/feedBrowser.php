<?php

$db = DB::getSiteDefault();
$db_feeds = $db->getFeeds();
foreach ($db_feeds as $feed)
  {
    print("<div class=\"feedName\">{$feed->name}</div>");
  }

 ?>