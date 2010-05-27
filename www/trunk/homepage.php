<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title><?php echo $user->username; ?>'s homepage</title>
    <link rel="stylesheet" href="homepage.css" title="signup" />
    <link rel="stylesheet" href="template.css" title="template" />
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
            foreach($user->feeds as $currentFeed)
            {
              print("<li onclick=\"getStories('{$currentFeed->name}','reader')\">{$currentFeed->name}</li>");//"
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
      function getStories(feedName,id){
        document.getElementById(id).innerHTML = feedName;
      }
    </script>
    
  </body>
</html>   
