<?php
require_once('auth.php');
if (!isset($user)) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <link rel="SHORTCUT ICON" href="http://geogriffin.mine.nu/watercooler/matt/watercooler-content-distribution/favicon.ico" />
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Welcome to the Watercooler!</title>
    <link rel="stylesheet" title="watercooler" href="watercooler.css" type="text/css"/>
    <script type="text/JavaScript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js">
      $(document).ready(function(){$('#userName').focus();});
    </script>
    
  </head>
  <body>
    <div id="wrap">
      <div id="logo">
	<a href="index.php"><img src="watercooler_logo.png" alt="Welcome to the Watercooler" /></a>
      </div>
      <form action="login.php" method="post">
        <fieldset class="thin"><legend>Watercooler</legend>
	  <p class="thin">
            <label for="userName">username</label>
            <input type="text" id="userName"name="userName" />
          </p>
	  <p class="thin">
	    <label for="userPassword">password</label>
            <input type="password" name="userPassword" />
	  </p>
	  <p class="submit">
	    <input type="submit" value="Login" 
		   name="submit" />
	  </p>
        </fieldset>
      </form>
      <div>Need an account?</div>
      <div>
	<a href="signup.php">Sign up!</a>
      </div>
    </div>
    <div class="validated">
      <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" /></a></div>
    </body>
</html>
<?php
   } else {
   include("homepage.php");
   }
   
