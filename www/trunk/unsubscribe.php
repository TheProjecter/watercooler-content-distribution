<?php
require_once('auth.php');

if (isset($_REQUEST['confirm'])) {
  if (isset($user)) {
    $user->delete();
?>
user deleted <a href=".">home</a>
<?php
  }
} else {
?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
    <label>Are you sure you want to DELETE your account??</label>
    <input type="submit" name="confirm" value="YES" />
</form>
<?php
}