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
  return (isset($_REQUEST['userName']) || isset($_REQUEST['userRepeatPass']) || isset($_REQUEST['userEmail']) || isset($_REQUEST['userCell']) || isset($_REQUEST['userCarrier']) || isset($_REQUEST['userFeeds']) || isset($_REQUEST['receive_email']) || isset($_REQUEST['receive_sms_text']) || isset($_REQUEST['receive_sms_link']));
}

function checkEmail($email)
{
  return preg_match('/^\S+@[\w\d.-]{2,}\.[\w]{2,6}$/iU', $email) ? TRUE : FALSE;
}

$prompt = TRUE;

if(checkset())
  {
    if(empty($_REQUEST['userNewPass'])==FALSE && sanityCheck($_REQUEST['userNewPass'], 'string', 10) != FALSE)
      {
	if (strlen($_REQUEST['userNewPass']) < 6)
	  {
	    echo '<p style="color:red">Please choose a password of at least 6 characters</p>';
	    $_REQUEST['userNewPass'] = '';
	    exit();
	  }
	
	// Make sure that the two password entries are identical
	if (empty($_REQUEST['userRepeatNewPass'])==FALSE && sanityCheck($_REQUEST['userRepeatNewPass'], 'string', 10) != FALSE)
	  {
	    $userRepeatNewPass = $_REQUEST['userRepeatNewPass'];
	    if ($userRepeatNewPass != $_REQUEST['userNewPass'])
	      {
		echo '<p style="color:red">Password mismatch.  Please re-enter your password.</p>';
		exit();
	      }
	  }
	else
	  {
	    echo '<p style="color:red">Please enter your password again in the Repeat Password field.</p>';
	    exit();
	  }
	$user->password = md5($_REQUEST['userNewPass']);
	$prompt = FALSE;
      }
    
    
    // Sanity check the username variable.
    
    if(empty($_REQUEST['userName'])==FALSE && sanityCheck($_REQUEST['userName'], 'string', 25) != FALSE)
      {
	if(User::find('username',$_REQUEST['userName']) != NULL)
	  {
	    if($_REQUEST['userName'] != $user->username)
	      {
		echo '<p style="color:red">Username is already in use.  Please try another username.</p>';
		exit();
	      }
	    
	  }
	else
	  {
	    $user->username = $_REQUEST['userName'];
	    echo "<p style=\"color:navy\">Username successfully updated to {$user->username}</p>";
	    $prompt = FALSE;
	  }
      }
    
    
    // Make sure that the email is syntactically valid
    if (empty($_REQUEST['userEmail'])==FALSE && sanityCheck($_REQUEST['userEmail'], 'string', 50) != FALSE)
      {
	if (checkEmail($_REQUEST['userEmail']) == FALSE)
	  {
	    echo '<p style="color:red">Please enter a valid email address.</p>';
	    exit();
	  }
	else
	  {
	    if ($user->email != $_REQUEST['userEmail'])
	      {
		$user->email = $_REQUEST['userEmail'];
		echo "<p style=\"color:navy\">Email address successfully updated to {$user->email} </p>";
		$prompt = FALSE;
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
		echo '<p style="color:red">A valid cell phone number must be exactly ten digits long</p>';
		$_REQUEST['userCell'] = '';
		exit();
	      }
	    else
	      {
		if(($this_user_object = User::find('phone_number',$_REQUEST['userCell'])) != NULL)
		  {
		    if ($this_user_object != $user)
		      {
			echo '<p style="color:red">There is already an account associated with this cell phone number.  If you do not have an account with username ';
			$this_user_array  = $this_user_object->get((array)'username');
			echo $this_user_array['username'];
			echo ', email our <a href"mailto:tripledouble1210@gmail.com">Customer Service Department</a>.</p>';
			exit();
		      }
		  }
		$user->phone_number = $_REQUEST['userCell'];
		$prompt = FALSE;
	      }
	  }
	else
	  {
	    echo '<p style="color:red">Please enter a valid cell phone number (only numeric characters).</p>';
	    $_REQUEST['userCell'] = '';
	    exit();
	  }
      }
    
    $user->send_email = $_REQUEST['receive_email']=='yes';
    $user->send_sms_text = $_REQUEST['receive_sms_text']=='yes';
    $user->send_sms_link = $_REQUEST['receive_sms_link']=='yes';
    
    $feedinfos = array();
    if($_REQUEST['feed'] != NULL)
      {
	foreach($_REQUEST['feed'] as $index=>$currentFeed)
	  {
	    if (!empty($currentFeed))
	      $feedinfos[] = array('url'=>$currentFeed, 'name'=>$currentFeed);
	  }
	$prompt = FALSE;
      }
    $user->feeds = Feeds::create($feedinfos);
    
    if ($prompt == FALSE)
      {
	print('<p style="color:navy;">Update Successful.</p>');
	print('<a href="index.php">Here is your homepage!</a>');
	print('</br></br>');
      }
  }
else
  {
    echo '<p style="color:navy;">Edit your user information here.</p>';
  }
?>