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
    {
      echo $story['url'];
      $contents .= "<h3><a href=\"{$story['url']}\">{$story['title']}</a></h3><p>{$story['content']}</p>";
    }
  return $contents;
}
