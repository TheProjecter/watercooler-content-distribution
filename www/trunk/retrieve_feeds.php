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
  $stories = $feed->stories->get(array('url', 'title', 'content'));

  $contents = '<ul>';
  foreach ($stories as $story)
    {
      $contents .= "<li><h3><a href=\"{$story['url']}\">{$story['title']}</a></h3><p>{$story['content']}</p></li>";
    }
  $contents .= '</ul>';

  return $contents;
}
