<html>
  <head>
    <title><?php print($_SESSION['username']); ?>'s homepage</title>
    <link rel="stylesheet" href="homepage.css" title="signup">
  </head>
  <body>
    <div id="wrap">
      <div id="header">
	<a href="logout.php">logout</a>
      </div>
      <div id="title">
	<p><?php print($_SESSION['username']); ?>'s Latest News</p>
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
  </body>
</html>   
