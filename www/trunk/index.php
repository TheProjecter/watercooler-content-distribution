<?php
session_start();
if (!isset($_SESSION['username'])) {
?>
<html>
<head><title>Welcome to the Watercooler!</title></head>
<body>
	<h1>Welcome to the Watercooler!</h1>
	<form action="login.php" method="post">
	      username: <input type="text" name="username" /><br />
	      password: <input type="password" name="password" /><br />
	      <input type="submit" name="submit" value="Login" /><br />
	</form>
	<p>Don't have an account yet?<br />
	   <a href="signup.html">Sign up!</a></p>
</body>
</html>
<?php
} else {
?>
<html>
<head><title>User Homepage</title></head>
<body>
	<a href="logout.php">logout</a>
	<p>This should be <?php print($_SESSION['username']); ?>'s homepage</p>
	<p>Feeds</p>
	<a>settings</a>
</body>
</html>
<?php
}
