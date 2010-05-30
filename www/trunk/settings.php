<?php
include_once('db_init.php');
include_once('auth.php');

// XXX make this prettier
// die if user not logged in
if (!isset($user))
  die('You are not logged in');
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Settings</title>
    <link rel="stylesheet" href="signup.css" title="signup" />
  </head>
  <body>
    <div class="corner">
      <a href="index.php">home</a>
    </div>
    <h1><?php echo $user->username; ?>'s Settings</h1><!--'-->
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
      <fieldset>
	<legend>Personal Information</legend>
	<p><label for="name">Username</label>
	  <input id="name" type="text" name="userName" maxlength="25" value="<?php echo $user->username; ?>"/></p>
	<p><label for="newPass">New Password</label>
	  <input id="newPass" type="password" name="userNewPass" maxlength="10" /></p>
	<p><label for="repeatNewPass">Repeat Password</label>
	  <input id="repeatNewPass"type="password" name="userRepeatNewPass" maxlength="10" /></p>
	<p><label for="currentPass">Current Password</label>
	  <input id="currentPass" type="password" name="userCurrentPass" maxlength="10" /></p>
	<p><label for="email">Email</label>
	  <input id="email" type="text" name="userEmail" maxlength="50"/ value="<?php echo $user->email;  ?>"></p>
	<p><label for="cell">Cell Phone #</label>
	  <input id="cell" type="text" name="userCell" maxlength="10" value="<?php echo $user->phone_number ?>"/></p>
	<p><label for="carrier">Carrier</label>
	  <select id="carrier" name="userCarrier">
	    <option value="AT&T">AT&#38;T</option>
	    <option <?php if($user->carrier == 'Verizon') echo 'selected'; ?> value="Verizon">Verizon</option>
	    <option <?php if($user->carrier == 'T-Mobile') echo 'selected'; ?> value="T-Mobile">T-Mobile</option>
	    <option <?php if($user->carrier == 'Sprint') echo 'selected'; ?> value="Sprint">Sprint</option>
	</select></p>
	<p><label for="reception">Default Methods of Reception</label>
	  <object class="multifield"><input type="checkbox" name="receive_email" value="yes" <?php if($user->receive_email == 'yes') echo 'checked'; ?>/>Email<br />
	    <input type="checkbox" name="receive_sms_text" value="yes" <?php if($user->receive_sms_text == 'yes') echo 'checked'; ?>/>SMS (Text)<br />
	    <input type="checkbox" name="receive_sms_link" value="yes" <?php if($user->receive_sms_link == 'yes') echo 'checked'; ?>/>SMS (Link)<br /></object></p>
	<p><label for="feeds">Feeds</label> <br />
	  <object id="feedFields" class="multifield">
            <div id="rightCol" style="">
	    <?php  
              foreach($user->feeds as $currentFeed)
              {
                print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" value=\"{$currentFeed->url}\"/><br />");
              }
            ?>
            </div>
	  </object>
	 <div style="float:left;margin-left:11.5em;"> <a onclick="addFeed()">Add More Feeds</a></div>
	</p>
	<input class="rightcolumn" type="submit" name="submit" value="Sign Up" />
      </fieldset>
    </form>
  </div>
  <div class="validated">
    <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" /></a>
  </div>

  <script type="text/javascript">
    function addFeed()
    {
      var currentFeeds = document.getElementById('rightCol');
      var newFeeds = document.createElement('input');
      newFeeds.setAttribute('type', 'text');
      newFeeds.setAttribute('name', 'feed[]');
      newFeeds.setAttribute('maxlength', '500');
      currentFeeds.appendChild(newFeeds);
      currentFeeds.appendChild(document.createElement('br'));
    }
  </script>
</body>
</html>

<?php

 /**
 * This function can be used to check the sanity of variables
 * @param string $type  The type of variable can be bool, float, numeric, string, array, or object
 * @param string $string The variable name you would like to check
 * @param string $length The maximum length of the variable
 *
 * return bool
 */
 function sanityCheck($string, $type, $length){

       // assign the type
       $type = 'is_'.$type;
       
       if(!$type($string))
	 {
	   return FALSE;
	 }
       // now we see if there is anything in the string
       elseif(empty($string))
	 {
	   return FALSE;
	 }
       // then we check how long the string is
       elseif(strlen($string) > $length)
	 {
	   return FALSE;
	 }
else
    {
      // if all is well, we return TRUE
      return TRUE;
    }
     }

      // check ALL the REQUEST variables
function checkSet()
{
   return isset($_REQUEST['userName'], $_REQUEST['userPassword'], $_REQUEST['userRepeatPass'], $_REQUEST['userEmail'], $_REQUEST['userCell'], $_REQUEST['userCarrier']);
}

function checkEmail($email)
{
  return preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $email) ? TRUE : FALSE;
}

$actualPass = $user->password;
$givenPass  = md5($_REQUEST['userCurrentPass']);
    // Validate the password input
if($actualPass != $givenPass)
  {
    echo 'Please enter your correct password';
    exit();
  }

// Sanity check the username variable.

if(empty($_REQUEST['userName'])==FALSE && sanityCheck($_REQUEST['userName'], 'string', 25) != FALSE)
  {
    if(User::find('username',$_REQUEST['userName']) != NULL)
      {
	if($_REQUEST['userName'] != $user->username)
	  {
	    echo 'Username is already in use.  Please try another username.';
	    exit();
	  }
	
      }
    else
      {
	$user->username = $_REQUEST['userName'];
	echo "<p>Username successfully updated to {$user->username}</p>";
      }
  }



// Make sure that the email is syntactically valid
if (empty($_REQUEST['userEmail'])==FALSE && sanityCheck($_REQUEST['userEmail'], 'string', 50) != FALSE)
  {
    if (checkEmail($_REQUEST['userEmail']) == FALSE)
      {
	echo 'Please enter a valid email address.';
	exit();
      }
    else
      {
	if ($user->email != $_REQUEST['userEmail'])
	  {
	    $user->email = $_REQUEST['userEmail'];
	    echo "<p>Email address successfully updated to {$user->email} </p>";
	  }
      }
  }

// Validate the user's cell phone number
if (empty($_REQUEST['userCell'])==FALSE)
  {
    if (sanityCheck($_REQUEST['userCell'],'numeric', 10) != FALSE)
      {
	if (strlen($_REQUEST['userCell']) != 10)
	  {
	    echo 'A valid cell phone number must be exactly ten digits long';
	    $_REQUEST['userCell'] = '';
	    exit();
	  }
	else
	  {
	    if(($this_user_object = User::find('phone_number',$_REQUEST['userCell'])) != NULL)
	      {
		if ($this_user_object != $user)
		  {
		    echo 'There is already an account associated with this cell phone number.  If you do not have an account with username ';
		    $this_user_array  = $this_user_object->get((array)'username');
		    echo $this_user_array['username'];
		    echo ', email our <a href"mailto:tripledouble1210@gmail.com">Customer Service Department</a>.';
		    exit();
		  }
	      }
	    $user->phone_number = $_REQUEST['userCell'];
	  }
      }
    else
      {
	echo 'Please enter a valid cell phone number (only numeric characters).';
	$_REQUEST['userCell'] = '';
	exit();
      }
  }



foreach($_REQUEST['feed'] as $index=>$currentFeed)
  {
    if (!empty($currentFeed))
      $feedinfos[] = array('url'=>$currentFeed, 'name'=>$currentFeed);
  }
$user->feeds = Feeds::create($feedinfos);

print($_REQUEST['userName']);
print(" 's settings have been updated.");
print('<a href="index.php">Here is your homepage!</a>');
print('</br></br>');

?>
