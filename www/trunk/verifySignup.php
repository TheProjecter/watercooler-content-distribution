      
 <?php

 /**
 * This function can be used to check the sanity of variables
 * @param string $type  The type of variable can be bool, float, numeric, string, array, or object
 *| @param string $string The variable name you would like to check
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
	if(User::find('username',$_REQUEST['userName']) != NULL)
	  {
	    echo '<p style="color:red">Username is already in use.  Please try another username.</p>';
	    exit();
	  }
	else
	  {
	    $userName = $_REQUEST['userName'];
	  }
      }
    else
      {
        echo '<p style="color:red">Username is not set</p>';
	$_REQUEST['userName'] = '';
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
	    echo '<p style="color:red">Please choose a password of at least 6 characters</p>';
	    $_REQUEST['userPassword'] = '';
	    exit();
	  }
	else
	  {
	    $userPassword = $_REQUEST['userPassword'];
	  }
      }
    else
      {
        echo '<p style="color:red">Please enter a valid Password</p>';
	$_REQUEST['userPassword'] = '';
        exit();
      }

    // Make sure that the two password entries are identical
    if (empty($_REQUEST['userRepeatPass'])==FALSE && sanityCheck($_REQUEST['userRepeatPass'], 'string', 10) != FALSE)
      {
	$userRepeatPass = $_REQUEST['userRepeatPass'];
	if ($userPassword != $userRepeatPass)
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

    // Make sure that the email is syntactically valid
    if (empty($_REQUEST['userEmail'])==FALSE && sanityCheck($_REQUEST['userEmail'], 'string', 50) != FALSE)
      {
	if (checkEmail($_REQUEST['userEmail']) == FALSE)
	  {
	    echo '<p style="color:red">Please enter a valid email address.</p>';
	    $_REQUEST['userEmail'] = '';
	    exit();
	  }
	  else
	    {
	      if(User::find('email',$_REQUEST['userEmail']) != NULL)
		{
		  echo '<p style="color:red">This email is already in use. Please use another email address.</p>';
		  exit();
		}
	      else
		{
		  $userEmail = $_REQUEST['userEmail'];
		}
	    }
      }
    else
      {
	echo '<p style="color:red">A valid email address is required to register with Watercooler.</p>';
	exit();
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
		    echo '<p style="color:red">There is already an account associated with this cell phone number.  If you do not own an account named ';
		    $this_user_array  = $this_user_object->get((array)'username');
		    echo $this_user_array['username'];
		    echo ', please email our <a href="mailto:tripledouble1210@gmail.com">webmaster</p>';
		    exit();
		  }
		$userCell = $_REQUEST['userCell'];
	      }
	  }
	else
	  {
	    echo '<p style="color:red">Please enter a valid cell phone number (only numeric characters).</p>';
	    $_REQUEST['userCell'] = '';
	    exit();
	  }
      }
    else
      {
	echo '<p style="color:red">Please enter your cell phone number.  Note that you will not receive text messages from Watercooler unless you select a texting reception method.</p>';
	exit();
      }

    if (isset($_REQUEST['feed'])) {
      $feedinfos = array();
      foreach ($_REQUEST['feed'] as $index=>$feed)
	if ($feed != '')
	  $feedinfos[] = array('name'=>$feed,'url'=>$feed);
      if (count($feedinfos) > 0)
	$feeds = Feeds::create($feedinfos);
    }

    $emailPin = mt_rand(1000,9999);
    $smsPin = mt_rand(1000,9999);
    $userInfo = array('username'=>$userName, 
		      'password'=>md5($userPassword), 
		      'email'=>$userEmail, 
		      'phone_number'=>$userCell, 
		      'carrier'=>$_REQUEST['userCarrier'], 
		      'send_email'=>$_REQUEST['receive_email'] === 'yes', 
		      'send_sms_text'=>$_REQUEST['receive_sms_text'] === 'yes',
		      'send_sms_link'=>$_REQUEST['receive_sms_link'] === 'yes',
		      'email_pin'=>$emailPin,
		      'phone_pin'=>0,
		      'feeds'=>$feeds);
    
    if (($user = User::create($userInfo)) == NULL)
      {

	echo '<p style="color:red">User registration failed.</p>';
	exit();
      }

    else
      {
	print('<p style="color:navy">Registration Successful!</p>');

	$hyperlink = 'confirm.php' . "?id={$user->id}&pin={$emailPin}";
	$EmailConfirmationString = "python2.5 -c \"import EmailServer; EmailServer.sendConfirmEmail('{$page_uri_base}{$hyperlink}','{$user->username}','{$user->email}');\"";
	exec($EmailConfirmationString);
	//$SMSConfirmationString = "python2.5 -c \"import EmailServer; EmailServer.sendConfirmSMS('{$user->phone_number}','{$user->carrier}','{$user->username}', '{$smsPin}');\"";
	//exec($SMSConfirmationString);







	$rssString = "/var/www/rss/{$user->username}.xml";
	$rssTemplate = "/var/www/rss/template.xml";


	// initialize the feed to the template and set permissions
	system("cp {$rssTemplate} {$rssString}");
	system("chmod g+w {$rssString}");

	// set the default title
	$category = "title";
	$scriptString = "sed -i 's/<$category>.*<\/$category>/<!-- Feedinfo --><$category>{$user->username}<\/$category>/g'";
	system("{$scriptString} {$rssString}");

	// set the default website
	$category = "link";
	$scriptString = "sed -i 's/<$category>.*<\/$category>/<!-- Feedinfo --><$category>http:\/\/watercooler\.geogriffin\.info<\/$category>/g'";
	system("{$scriptString} {$rssString}");

	// set the default description
	$category = "description";
	$scriptString = "sed -i 's/<$category>.*<\/$category>/<!-- Feedinfo --><$category>My feed<\/$category>/g'";
	system("{$scriptString} {$rssString}");

	// set the publishing date
	$category = "lastBuildDate";
	$date = date('F\ j\,\ Y\ g:i\ A\ T');
	$scriptString = "sed -i 's/<$category>.*<\/$category>/<!-- Feedinfo --><$category>{$date}<\/$category>/g'";
	system("{$scriptString} {$rssString}");

	// set the last build date
	$category = "lastBuildDate";
	$date = date('F\ j\,\ Y\ g:i\ A\ T');
	$scriptString = "sed -i 's/<$category>.*<\/$category>/<!-- Feedinfo --><$category>{$date}<\/$category>/g'";
	system("{$scriptString} {$rssString}");

	// set the default managing editor
	$category = "managingEditor";
	$scriptString = "sed -i 's/<$category>.*<\/$category>/<!-- Feedinfo --><$category>{$user->email}<\/$category>/g'";
	system("{$scriptString} {$rssString}");



	print('<p>You have been sent a confirmation email and text message.  Please follow the instructions in the email and text message in order to enjoy full access to the Watercooler.</p>');
	print('<a href="index.php">Login here.</a>');
	header("Location: {$page_uri_base}success.php?email={$user->email}&cell={$user->phone_number}");
      }
  }
  else
    {
      // this will be the default message if the form accessed without POSTing
      echo '<p style="color:navy;">Please fill in the form below</p>';
    }

?>
