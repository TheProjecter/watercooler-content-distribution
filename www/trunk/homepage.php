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
            <?php include('getFeeds.php'); ?>
	  </div>
	  <div id="feedreader_stories">
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
	      <?php include('feedBrowser.php') ?>
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
      function getFeeds() {
        // get reader element
        reader = $('#feedreader_feeds');

        // set up and execute the request
        reader.load('getFeeds.php');
      }

      function getStories(feedId) {
        // get reader element
	reader = $('#feedreader_stories');
	// notify user that data is being fetched
	reader.html('<h1>Fetching stories...</h1>');
      
	// set up and execute the request
	reader.load('getStories.php',{id:feedId});
      }

      function addFeed(feedId) {
	$.post('addFeed.php', {id:feedId},
	       function(data) {
		 getFeeds();
	       }, 'text');
      }
    </script>
    
  </body>
</html>   
