<?php
//include databse functions and objects
//include('db.php');
//include('db_sqlite.php');
ob_start();
include_once('db_init.php');
include_once('common.php');

//start user session
session_start();

$fieldNumber = 0;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title>Sign up</title>
    <link rel="stylesheet" href="watercooler.css" title="signup" />
    <script type="text/JavaScript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js">
      $(document).ready(function(){$('#userName').focus();});
    </script>
  </head>
  <body>
    <!-- Header -->
    <div id="header">
      <div class="corner">
	<a href="index.php">home</a>
      </div>
      <div id="logo">
	<a href="index.php"><img src="watercooler_logo.png" alt="Welcome to the Watercooler" /></a>
      </div>
    </div>

    <!-- Form -->
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">

      <!-- Personal Information Fieldset -->
      <fieldset><legend>Personal Information</legend>

	<!-- Username -->
	<div class="lineWidth">
	  <label class="leftCol" for="userName">Username</label>
	  <input class="middleCol" id="userName" type="text" name="userName" maxlength="25" value="<?php echo $_REQUEST['userName']; ?>"/>
	  <script type="text/javascript">
	    $('#userName').focus();
          </script>
	</div>
	
	<!-- Password -->
	<div class="lineWidth">
	  <label class="leftCol" for="pass">Password</label>
	  <input class="middleCol" id="pass" type="password" name="userPassword" maxlength="10" />
	</div>

	<!-- Repeat Password -->
	<div class="lineWidth"><label class="leftCol" for="repeatPass">Repeat Password</label>
	  <input class="middleCol" id="repeatPass"type="password" name="userRepeatPass" maxlength="10" />
	</div>

	<!-- Email -->
	<div class="lineWidth"><label class="leftCol" for="email">Email</label>
	  <input class="middleCol" id="email" type="text" name="userEmail" maxlength="50"/ value="<?php echo $_REQUEST['userEmail'];  ?>"/>
	</div>
	
	<!-- Phone Number -->
	<div class="lineWidth"><label class="leftCol" for="cell">Cell Phone #</label>
	  <input class="middleCol" id="cell" type="text" name="userCell" maxlength="10" value="<?php echo $_REQUEST['userCell']; ?>"/>
	</div>

	<!-- Carrier -->
	<div class="lineWidth">
	  <label class="leftCol" for="carrier">Carrier</label>
	  <select style="float:left; border: 1px solid navy;" id="carrier" name="userCarrier">
	    <option value="AT&T">AT&#38;T</option>
	    <option <?php if($_REQUEST['userCarrier'] == 'Verizon') echo 'selected'; ?> value="Verizon">Verizon</option>
	    <option <?php if($_REQUEST['userCarrier'] == 'T-Mobile') echo 'selected'; ?> value="T-Mobile">T-Mobile</option>
	    <option <?php if($_REQUEST['userCarrier'] == 'Sprint') echo 'selected'; ?> value="Sprint">Sprint</option>
	  </select>
	</div>
      </fieldset>

      <!-- Feed Information Fieldset -->
      <fieldset><legend>Feed Information</legend>

	<!-- Methods of Reception -->
	<div class="lineWidth">
	  <label class="leftCol" for="reception">Methods of Reception</label>
	  <object>
	    <input style="margin-left:-2.6em;" type="checkbox" name="receive_email" value="yes" <?php if($_REQUEST['receive_email'] == 'yes') echo 'checked'; ?>/>Email<br />
	    <input type="checkbox" name="receive_sms_text" value="yes" <?php if($_REQUEST['receive_sms_text'] == 'yes') echo 'checked'; ?>/>SMS (Text)<br />
	    <input type="checkbox" name="receive_sms_link" value="yes" <?php if($_REQUEST['receive_sms_link'] == 'yes') echo 'checked'; ?>/>SMS (Link)<br />
	  </object>
	</div>
	
	<!-- Feeds -->
        <div class="lineWidth">
	  <label for="feeds">Feeds</label>
	  <object class="middleCol">
            <div id="rightCol">
	      <?php
              if(isset($_REQUEST['feed']))
              {
	        foreach($_REQUEST['feed'] as $currentFeed)
                {
                  print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" value=\"{$currentFeed}\"/><br />");
                }
              }
              else
              {
                for($counter=0; $counter < 3; $counter++)
                  print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" /><br />");
              }
	    ?>
            </div>
	  </object>

	  <!--Add More Feeds -->
	</div>
	<div>
          <button class="clickable" type="button" onclick="addFeed()">Add More Feeds</button>
	  <input class="clickable" type="submit" name="submit" value="Register!"/>
	</div>
      </fieldset>
    </form>
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
      
      function expandFeed()
      {
      var url;
      }
      
    </script>
    <fieldset id="feedback">
      
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

    $userInfo = array('username'=>$userName, 
		      'password'=>md5($userPassword), 
		      'email'=>$userEmail, 
		      'phone_number'=>$userCell, 
		      'carrier'=>$_REQUEST['userCarrier'], 
		      'send_email'=>$_REQUEST['receive_email'] === 'yes', 
		      'send_sms_text'=>$_REQUEST['receive_sms_text'] === 'yes',
		      'send_sms_link'=>$_REQUEST['receive_sms_link'] === 'yes',
		      'feeds'=>$feeds);
    
    if (($user = User::create($userInfo)) == NULL)
      {

	echo '<p style="color:red">User registration failed.</p>';
	exit();
      }

    else
      {
	print('<p style="color:navy">Registration Successful!</p>');

	$emailPin = mt_rand(0,9999);
	$smsPin = mt_rand(0,9999);
	$hyperlink = 'confirm.php' . "?id={$user->id}&pin={$emailPin}";
	$commandString = "python2.5 -c \"import EmailServer; EmailServer.sendConfirmEmail('{$page_uri_base}{$hyperlink}','{$user->username}','{$user->email}');\"";
	print($commandString);
	system($commandString);

	print('<p>You have been sent a confirmation email and text message.  Please follow the instructions in the email and text message in order to enjoy full access to the Watercooler.</p>');
	print('<a href="index.php">Login here.</a>');
	header("Location: {$page_uri_base}success.php?email={$user->email}&cell={$user->phone_number}");
      }
  }
  else
    {
      // this will be the default message if the form accessed without POSTing
      echo '<p style="color:navy;">Please fill in the form above</p>';
    }

?>
    </fieldset>
  </body>
</html>
