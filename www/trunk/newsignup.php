<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Sign up</title>
    <link rel="stylesheet" href="signup.css" title="signup" />
  </head>
  <body>
    <div class="corner">
      <a href="index.php">home</a>
    </div>
    <h1>Sign up</h1>
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
      <fieldset>
	<legend>Personal Information</legend>
	<p><label for="name">Username</label>
	  <input id="name" type="text" name="userName" maxlength="25"/></p>
	<p><label for="pass">Password</label>
	  <input id="pass" type="password" name="userPassword" maxlength="10" /></p>
	<p><label for="repeatPass">Repeat Password</label>
   <input id="repeatPass"type="password" name="userRepeatPass" maxlength="10"/></p>
	<p><label for="email">Email</label>
	  <input id="email" type="text" name="userEmail" maxlength="50"/></p>
	<p><label for="cell">Cell Phone #</label>
   <input id="cell" type="text" name="userCell" maxlength="10"/></p>
	<p><label for="carrier">Carrier</label>
	  <select id="carrier" name="userCarrier">
	    <option value="att">AT&#38;T</option>
	    <option value="verizon">Verizon</option>
	</select></p>
	<p><label for="reception">Default Methods of Reception</label>
	  <object class="multifield"><input type="checkbox" name="receive_email" value="yes" />Email<br />
	    <input type="checkbox" name="receive_sms_text" value="yes" />SMS (Text)<br />
	    <input type="checkbox" name="receive_sms_link" value="yes" />SMS (Link)<br /></object></p>
	<p><label for="feeds">Feeds</label> <br />
	  <object class="multifield">
	    <input type="text" name="feed1" maxlength="500"/><br />
	    <input type="text" name="feed2" maxlength="500"/><br />
	    <input type="text" name="feed3" maxlength="500"/><br />
	    <a href="#">Add More Feeds</a>
	  </object>
	</p>
	<input class="rightcolumn" type="submit" name="submit" value="Sign Up" />
      </fieldset>
    </form>
  </div>
  <div class="validated">
    <a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-xhtml10" alt="Valid XHTML 1.0 Strict" /></a>
  </div>
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

// check all our variables are set
if(checkSet() != FALSE)
  {
    // Sanity check the username variable.

    if(empty($_REQUEST['userName'])==FALSE && sanityCheck($_REQUEST['userName'], 'string', 25) != FALSE)
      {
        $userName = $_REQUEST['userName'];
      }
    else
      {
        echo 'Username is not set';
        exit();
      }

    // *************** TODO **************
    // *Verify that username is available*
    // ***********************************

    // Validate the password input
    if(empty($_REQUEST['userPassword'])==FALSE && sanityCheck($_REQUEST['userPassword'], 'string', 10) != FALSE)
      {
	if (strlen($_REQUEST['userPassword']) < 6)
	  {
	    echo 'Please choose a password of at least 6 characters';
	    exit();
	  }
	else
	  {
	    $userPassword = $_REQUEST['userPassword'];
	  }
      }
    else
      {
        echo 'Please enter a valid Password';
        exit();
      }

    // Make sure that the two password entries are identical
    if (empty($_REQUEST['userRepeatPass'])==FALSE && sanityCheck($_REQUEST['userRepeatPass'], 'string', 10) != FALSE)
      {
	$userRepeatPass = $_REQUEST['userRepeatPass'];
	if ($userPassword != $userRepeatPass)
	  {
	    echo 'Password mismatch.  Please re-enter your password.';
	    exit();
	  }
      }
    else
      {
	echo 'Please enter your password again in the Repeat Password field.';
	exit();
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
	      $userEmail = $_REQUEST['userEmail'];
	    }
      }
    else
      {
	echo 'A valid email address is required to register with Watercooler.';
	exit();
      }

    // Validate the user's cell phone number
    if (empty($_REQUEST['userCell'])==FALSE)
      {
	if (sanityCheck($_REQUEST['userCell'],'numeric', 10) != FALSE)
	  {
	    if (strlen($_REQUEST['userCell']) < 9)
	      {
		echo 'A valid cell phone number must be either nine or ten digits long';
		exit();
	      }
	    else
	      {
		$userCell = $_REQUEST['userCell'];
	      }
	  }
	else
	  {
	    echo 'Please enter a valid cell phone number (only numeric characters).';
	    exit();
	  }
      }
    elseif($_REQUEST['receive_sms_text'] == 'yes' || $_REQUEST['receive_sms_link'])
      {
	$userCell = $_REQUEST['userCell'];
      }
    else
      {
	echo 'Please enter your cell phone number or deselect the SMS(text) and SMS(link) default methods of reception.';
	exit();
      }

  }
  else
    {
      // this will be the default message if the form accessed without POSTing
      echo '<p>Please fill in the form above</p>';
    }

?>
