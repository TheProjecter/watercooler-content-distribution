<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title><?php print($_SESSION['username']); ?>'s homepage</title>
    <link rel="stylesheet" href="homepage.css" title="signup" />
  </head>
  <body>
    <div id="wrap">
      <div id="header">
	<a href="logout.php">logout</a>
      </div>
      <div id="title">
	<p><?php print($_SESSION['userName']); ?>'s Latest News</p>
      </div>
      <div id="nav">
      </div>
      <div id="feedreader">
	<div class="navyorange" id="main">
	  <ul>
	    <li>Feed 1</li> <!--TODO: Replace Feed 1 with info from DB.  Add onclick to paragraph tag-->
	    <li>Feed 2</li> <!--TODO: Replace Feed 2 with info from DB.  Add onclick to paragraph tag-->
	    <li>Feed 3</li> <!--TODO: Replace Feed 3 with info from DB.  Add onclick to paragraph tag-->
	  </ul>
	</div>
	<div class="navyorange" id="sidebar">
	  <ul>
	    <li>This is a long and interesting story that relates to the content in feed 1.</li>
	    <li>Why does this one keep repeating itself?  Why does this one keep repeating itself? Why does this one keep repeating itself?</li>
	  </ul>
	</div>
      </div>
      <div id="footer">
      </div>
    </div>
    <div class="validated">
      <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" /></a>
    </div>
  </body>
</html>   
