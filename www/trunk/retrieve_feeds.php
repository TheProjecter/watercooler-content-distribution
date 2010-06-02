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
  $stories = $feed->stories->get(array('url', 'title', 'content', 'timestamp'));
  $contents = '<ul>';
  foreach ($stories as $story)
    {
      $date = date('F\ j\,\ Y\ g:i\ A\ T',$story['timestamp']);
      $contents .= "<li class=\"story\"><h3><a href=\"{$story['url']}\" target=\"_blank\">{$story['title']}</a></h3><div class=\"storyDate\">{$date}</div><div class=\"storyContent\">{$story['content']}</div></li>";
    }
  $contents .= '</ul>';

  return $contents;
}