<?php
include_once('db_init.php');
if (isset($_REQUEST['id'])) {
  $feed = Feed::find('id', $_REQUEST['id']);
  if ($feed === NULL)
    echo '<h1>Feed not found</h1>';
  else
    echo getFeedOutput($feed);
}

function getFeedOutput($feed) {
  $stories = $feed->stories->get(array('title', 'content'));

  foreach ($stories as $story)
    $contents .= "<h1>{$story['title']}</h1><p>{$story['content']}</p>";

  return $contents;
}
