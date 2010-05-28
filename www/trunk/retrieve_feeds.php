<?php
include_once('db_init.php');
include_once('auth.php');

function getFeeds($feedSource) {
  $stories = $feed->stories;
  foreach ($stories as $story)
    echo $story->content;
}