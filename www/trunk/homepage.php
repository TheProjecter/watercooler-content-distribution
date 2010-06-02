<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title><?php echo $user->username; ?>'s homepage</title><!-- ' -->
    <link rel="stylesheet" href="homepage.css" title="signup" />
    <link rel="stylesheet" href="template.css" title="template" />
    <script src="http://www.google.com/jsapi"></script>
    <script>google.load("jquery", "1");</script>
  </head>
  <body>
    <div id="wrap">
      <div id="header">
	<a href="logout.php" style="position:absolute; text-align:left;">logout</a>
	<div id="logo" style="text-align:center;">
	  <img style="text-align: center;" src="watercooler_logo.png" alt="Welcome to the Watercooler" />
	</div>
      </div>
      <div id="outline">
      </div>
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
	      print("<div onclick=\"getStories('{$currentFeed->id}', 'feedreader_stories')\">");
	      //print("<img src=\"{$icon}\" alt=\"{$domain}\"></img>");
	      print("<div class=\"feedName\" style=\"float:right; margin-left:1em;\">{$currentFeed->name}</div></div><br />");//"
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
	<a href="settings.php" id="footerLeft">Settings</a>
	<a href="unsubscribe.php" id="footerRight">Unsubscribe</a>
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
    </script>
    
  </body>
</html>   

