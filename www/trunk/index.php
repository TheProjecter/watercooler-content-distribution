<?php
session_start();
if (!isset($_SESSION['username'])) {
?>
<html>
<head>
<title>Welcome to the Watercooler!</title>
<LINK rel="stylesheet" title="template" href="template.css" type="text/css">

</head>
<body>
	<h1>Welcome to the Watercooler!</h1>
        <div class="outerbody"><div class="google">
	<form action="login.php" method="post">
        <fieldset>
        <legend>TODO</legend>
	      <p><label for="username">username</label>
              <input type="text" name="username" /></p>
	      <p><label for="password">password</label>
              <input type="password" name="password" /><br /></p>
	      <p class="submit"><input type="submit" value="Login" 
              name="submit" /></p>
        </fieldset>
	</form>
	<p>Need an account?<br />
	   <a href="signup.html">Sign up!</a></p>
        </div></div>
</body>
</html>
<?php
} else {
  include("homepage.php");
}
