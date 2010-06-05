<?php

include_once('db_init.php');
include_once('common.php');
include_once('auth.php');

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <link rel="SHORTCUT ICON" href="http://geogriffin.mine.nu/watercooler/matt/watercooler-content-distribution/favicon.ico" />
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Welcome to the Watercooler!</title>
    <link rel="stylesheet" title="watercooler" href="watercooler.css" type="text/css"/>
  </head>
  <body>
    <div id="wrap">
      <div id="logo">
	<img src="watercooler_logo.png" alt="Welcome to the Watercooler"></img>
      </div>
      
      <form class="publisher" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
	
	<!-- Feed Information -->
	<fieldset><legend>Feed Information</legend>
	  
	  <!-- Title -->
	  <div class="lineWidth">
	    <label class="leftCol" for "feedTitle">Feed Title</label>
	    <input class="middleCol" id="feedTitle" type="text" name="feedTitle" maxlength="100" />
	  </div>
	  
	  <!-- Website -->
	  <div class="lineWidth">
	    <label class="leftCol" for "feedWebsite">Feed Website</label>
	    <input class="middleCol" id="feedWebsite" type="text" name="feedWebsite" maxlength="100" />
	  </div>
	  
	  <!-- Description -->
	  <div class="lineWidth">
	    <label class="leftCol" for "feedDescription">Feed Description</label>
	    <textarea class="middleCol" rows="5" id="feedDescription" name="feedDescription" maxlength="160" style="height:5em;" ></textarea>
	  </div>
	</fieldset>

	<!-- New Story -->
	<fieldset class="publisher"><legend>New Story</legend>

	  <!-- Title -->
	  <div class="lineWidth">
	    <label class="leftCol" for="storyTitle">Title</label>
	    <input class="middleCol" type="text" id="storyTitle" name="storyTitle" maxlength="100" />
	  </div>

	  <!-- Link -->
	  <div class="lineWidth">
	    <label class="leftCol" for="storyLink">Link</label>
	    <input class="middleCol" type="text" id="storyLink" name="storyLink" maxlength="100" />
	  </div>

	  <!-- Description -->
	  <div class="lineWidth">
	    <label class="leftCol" for="storyDescription">Description</label>
	    <textarea class="middleCol" rows="5" id="storyDescription" name="storyDescription" maxlength="160"></textarea>
	  </div>
	  
	  <!-- Submit -->
	  <div class="lineWidth">
	    <input class="middleCol clickable" type="submit" name="submit" value="Publish!" style="float:none; margin-left:8em; text-align:center; font-weight:bolder; height:2em;"/>
	  </div>
	</fieldset>
      </form>
    </div>

<?php


$rssString = "/var/www/rss/{$user->username}.xml";

// set the default title
$category = "title";
$scriptString = "sed -i 's/<!-- Feedinfo --><$category>.*<\/$category>/<!-- Feedinfo --><$category>{$_REQUEST['feedTitle']}<\/$category>/g'";
system("{$scriptString} {$rssString}");

// set the pubdate
$category = "pubdate";
$date = date('F\ j\,\ Y\ g:i\ A\ T');
$scriptString = "sed -i 's/<!-- Feedinfo --><$category>.*<\/$category>/<!-- Feedinfo --><$category>{$date}<\/$category>/g'";
system("{$scriptString} {$rssString}");

// set the default website
$category = "link";
$scriptString = "sed -i 's/<!-- Feedinfo --><$category>.*<\/$category>/<!-- Feedinfo --><$category>{$_REQUEST['feedWebsite']}<\/$category>/g'";
system("{$scriptString} {$rssString}");

// set the default description
$category = "description";
$scriptString = "sed -i 's/<!-- Feedinfo --><$category>.*<\/$category>/<!-- Feedinfo --><$category>{$_REQUEST['feedDescription']}<\/$category>/g'";
system("{$scriptString} {$rssString}");

// set the last build date
$category = "lastBuildDate";
$scriptString = "sed -i 's/<!-- Feedinfo --><$category>.*<\/$category>/<!-- Feedinfo --><$category>{$date}<\/$category>/g'";
system("{$scriptString} {$rssString}");

// remove ending
system("sed -i '\$d' {$rssString}");
system("sed -i '\$d' {$rssString}");

// add opening item tag
$tag = '<item>';
system("echo \"{$tag}\" >> {$rssString}");

// add story title
$line = "<title>{$_REQUEST['storyTitle']}</title>";
system("echo \"{$line}\" >> {$rssString}");

// add link
$line = "<link>{$_REQUEST['storyLink']}</link>";
system("echo \"{$line}\" >> {$rssString}");

// add description
$line = "<description>{$_REQUEST['storyDescription']}</description>";
system("echo \"{$line}\" >> {$rssString}");

// add pubdate
$date = date('F\ j\,\ Y\ g:i\ A\ T');
$line = "<pubdate>{$date}</pubdate>";
system("echo \"{$line}\" >> {$rssString}");

// add ending item tag
$tag = '</item>';
system("echo \"{$tag}\" >> {$rssString}");

// add ending channel tag
$tag = '</channel>';
system("echo \"{$tag}\" >> {$rssString}");

// add ending rss tag
$tag = '</rss>';
system("echo \"{$tag}\" >> {$rssString}");

?>

  </body>
</html>
