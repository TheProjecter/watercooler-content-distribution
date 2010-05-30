<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title><?php echo $user->username; ?>'s homepage</title>
    <link rel="stylesheet" href="homepage.css" title="signup" />
    <link rel="stylesheet" href="template.css" title="template" />
    <script src="http://www.google.com/jsapi"></script>
    <script>google.load("jquery", "1");</script>
  </head>
  <body>
    <div id="wrap">
      <div id="header">
	<a href="logout.php">logout</a>
      </div>
      <div id="title">
	<p><?php echo $user->username; ?>'s Latest News</p>
      </div>
      <div id="outline">
	<div id="nav">
	</div>
      </div>
      <div id="feedreader">
	<div id="main">
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
		  $icon = '';
		}

	      print("<div onclick=\"getStories('$currentFeed->id}', 'reader')\">");
	      if($icon != '')
		print("<img src=\"{$icon}\" alt=\"{$domain}\"></img>");
	      print("<div style=\"float:right; margin-left:1em;\">{$currentFeed->name}</div></div>");//"
	    }
	    ?>
	  </ul>
	</div>
	<div id="sidebar">
	  <ul>
	    <div id="reader"></div>
	  </ul>
	</div>
      </div>
      <div id="userspace">
      </div>
      <div id="footer">
	<a href="settings.php" id="footerLeft">Settings</a>
	<a href="unsubscribe.php" id="footerRight">Unsubscribe</a>
      </div>
    </div>
    <div class="validated">
      <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" /></a>
    </div>

    <script type="text/javascript">
   function getStories(feedId, readerElementId) {
   // get reader element
   reader = $('#'+readerElementId);
   // notify user that data is being fetched
   reader.html('<h1>Getting stories...</h1>');

   // set up and execute the request
   reader.load('retrieve_feeds.php',{id:feedId});
 }
    </script>
    
  </body>
</html>   
