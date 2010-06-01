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
      $contents .= "<li class=\"story\"><h3 class=\"storyTitle\"><a href=\"{$story['url']} target=\"_blank\"\">{$story['title']}</a></h3><div class=\"storyDate\">{$story['timestamp']}</div><div class=\"storyContent\">{$story['content']}</div></li>";
    }
  $contents .= '</ul>';

  return $contents;
}