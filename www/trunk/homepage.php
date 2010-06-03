<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title><?php echo $user->username; ?>'s homepage</title><!-- ' -->
    <link rel="stylesheet" href="watercooler.css" title="watercooler" />
    <script src="http://www.google.com/jsapi"></script>
    <script>google.load("jquery", "1");</script>
  </head>
  <body>
    <div id="wrap">
      <div id="header">
	<a href="logout.php" style="float:left; position:absolute; text-align:left;">logout</a>
	<div id="logo">
      <a href="index.php"><img src="watercooler_logo.png" alt="Welcome to the Watercooler" /></a>
	</div>
      </div>
      <div style="border-style:none;" class="center">
        <div id="feedreader">
	  <div id="nav">
	    <h1 class="title">
	      <?php echo $user->username; ?>'s Latest News <!-- ' -->
	    </h1>
	  </div>
	  <div id="feedreader_feeds">
	    <ul>
	      <?php

            function getDomain($url)
            {
	      $www_stripped = ereg_replace('www\.','',$url);
	      $domain = parse_url($www_stripped);
	      if(!empty($domain["host"]))
		{
		  return $domain["host"];
		}
	      else
		{
		  return $domain["path"];
		}

	    }

            foreach($user->feeds as $currentFeed)
            {
	      $domain = getDomain($currentFeed->url);
	      $icon = "http://";
	      $icon .=$domain;
	      $icon .= '/favicon.ico';
	      $handle = @fopen($icon, 'r');
	      
	      if($handle !== false)
		{
		  $icon = $icon;
		}
	      else
		{
		  $icon = 'feed-icon-14x14.png';
		}
//"
	      print("<div class=\"feed\"> <button type=\"button\" onclick=\"getStories('{$currentFeed->id}', 'feedreader_stories')\">");
	      print("<img class=\"icon\" src=\"{$icon}\" alt=\"{$domain}\"></img>");
	      print("<div class=\"feedName\">{$currentFeed->name}</div></button>");//"
	    }
	    ?>
	    </ul>
	  </div>
	  <div id="feedreader_stories">
	    <!--
	       getStories(<?php print("");  ?>);
	      -->
          </div>
	</div>
	<div id="userspace">
	  <div id="browse">
	    <h1 class="title">Browse Feeds</h1>
	  </div>
	  <div id="feedBrowser">
	    <div id="feedHeader" style="margin:1em 1em 0 0; border-bottom: 2px solid navy;">
	      <div style="width:12em;text-align:left; float:left;">Title</div>
	      <div style="width:3.5em; text-align:right; float:right; margin-right:1em;">Users</div>
	    </div>
	    <div id="feedRows">
	      <?php include_once('feedBrowser.php') ?>
	    </div>
          </div>
	</div>
	<div id="footer">
	  <a href="settings.php"><button type="button" class="left bigButton">Settings</button></a>
	  <a href="unsubscribe.php"><button type="button" class="right bigButton">Unsubscribe</button></a>
	</div>
      </div>
    </div>

    <script type="text/javascript">
      function getStories(feedId, readerElementId) {
      // get reader element
      reader = $('#'+readerElementId);
      // notify user that data is being fetched
      reader.html('<h1>Fetching stories...</h1>');
      
      // set up and execute the request
      reader.load('retrieve_feeds.php',{id:feedId});
      }

function addFeed(feed_id) {
  //$feed = Feed::find('id',$id);
  //$if($feed !== NULL)
  //  $user->addFeed($feed);
}
    </script>
    
  </body>
</html>   

