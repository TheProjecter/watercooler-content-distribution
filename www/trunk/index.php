<?php
session_start();
if (!isset($_SESSION['userName'])) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <link rel="SHORTCUT ICON" href="http://geogriffin.mine.nu/watercooler/matt/watercooler-content-distribution/favicon.ico" />
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Welcome to the Watercooler!</title>
    <link rel="stylesheet" title="template" href="template.css" type="text/css"/>
    
  </head>
  <body>
    <h1>Welcome to the Watercooler!</h1>
    <div class="outerbody"><div class="google">
	<form action="login.php" method="post">
          <fieldset>
            <legend>TODO</legend>
	    <p><label for="userName">username</label>
              <input type="text" name="userName" /></p>
	    <p><label for="password">password</label>
              <input type="password" name="password" /><br /></p>
	    <p class="submit"><input type="submit" value="Login" 
				     name="submit" /></p>
          </fieldset>
	</form>
	<p>Need an account?<br />
	  <a href="signup.php">Sign up!</a></p>
    </div></div>
    <div class="validated">
      <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" /></a></div>
  </body>
</html>
<?php
   } else {
   include("homepage.php");
   }
   
