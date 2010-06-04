<?php
include_once('db_init.php');
include_once('auth.php');

// XXX make this prettier
// die if user not logged in
if (!isset($user))
  die('You are not logged in');

$displayUserName;
$displayEmail;
$displayCell;
$displayCarrier;
$displayReceiveEmail;
$displayReceiveText;
$displayReceiveLink;

if(isset($_REQUEST['userName']))
  $displayUserName = $_REQUEST['userName'];
else
  $displayUserName = $user->username;

if(isset($_REQUEST['userEmail']))
  $displayEmail = $_REQUEST['userEmail'];
else
  $displayEmail = $user->email;

if(isset($_REQUEST['userCell']))
  $displayCell = $_REQUEST['userCell'];
else
  $displayCell = $user->phone_number;

if(isset($_REQUEST['userCarrier']))
  $displayCarrier = $_REQUEST['userCarrier'];
else
  $displayCarrier = $user->carrier;

if(isset($_REQUEST['receive_email']))
  $displayReceiveEmail = $_REQUEST['receive_email'];
else
  $displayReceiveEmail = $user->send_email;

if(isset($_REQUEST['receive_sms_text']))
  $displayReceiveText = $_REQUEST['receive_sms_text'];
else
  $displayReceiveText = $user->send_sms_text;

if(isset($_REQUEST['receive_sms_link']))
  $displayReceiveLink = $_REQUEST['receive_sms_link'];
else
  $displayReceiveLink = $user->send_sms_link;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="EN" dir="ltr" xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/xml; charset=utf-8" />
    <title><?php echo $user->username?>'s Settings</title> <!-- ' -->
    <link rel="stylesheet" href="watercooler.css" title="signup" />
    <script type="text/JavaScript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js">
      $(document).ready(function(){$('#userName').focus();});
    </script>
  </head>
  <body>
    <!-- Header -->
    <div id="header">
      <div class="corner" style="position:absolute; text-align:left;">
	<a href="index.php">home</a>
      </div>
      <div id="logo">
	<a href="index.php"><img src="watercooler_logo.png" alt="Welcome to the Watercooler" /></a>
      </div>
    </div>
    
    <fieldset id="feedback">
      <?php include_once('verifySettings.php'); ?>
    </fieldset>
    
    <!-- Form -->
    <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
      
      <!-- Personal Information Fieldset -->
      <fieldset><legend>Personal Information</legend>
	
	<!-- Username -->
	<div class="lineWidth">
	  <label class="leftCol" for="userName">Username</label>
	  <input class="middleCol" id="userName" type="text" name="userName" maxlength="25" value="<?php echo $displayUserName; ?>"/>
	  <script type="text/javascript">
	    $('#userName').focus();
          </script>
	</div>
	
	<!-- New Password -->
	<div class="lineWidth"><label class="leftCol" for="newPass">New Password</label>
	  <input class="middleCol" id="newPass" type="password" name="userNewPass" maxlength="10" />
	</div>
	
	<!-- Repeat New Password -->
	<div class="lineWidth"><label class="leftCol" for="repeatNewPass">Repeat New Password</label>
	  <input class="middleCol" id="repeatNewPass" type="password" name="userRepeatNewPass" maxlength="10" />
	</div>
	
	<!-- Email -->
	<div class="lineWidth"><label class="leftCol" for="email">Email</label>
	  <input class="middleCol" id="email" type="text" name="userEmail" maxlength="50" value="<?php echo $displayEmail;  ?>" />
	</div>
	
	<!-- Phone Number -->
	<div class="lineWidth"><label class="leftCol" for="cell">Cell Phone #</label>
	  <input class="middleCol" id="cell" type="text" name="userCell" maxlength="10" value="<?php echo $displayCell ?>"/>
	</div>
	
	<!-- Carrier -->
	<div class="lineWidth">
	  <label class="leftCol" for="carrier">Carrier</label>
	  <select style="float:left; border: 1px solid navy;" id="carrier" name="userCarrier">
	    <option value="AT&#38;T">AT&#38;T</option>
	    <option <?php if($displayCarrier == 'Verizon') echo 'selected'; ?> value="Verizon">Verizon</option>
	    <option <?php if($displayCarrier == 'T-Mobile') echo 'selected'; ?> value="T-Mobile">T-Mobile</option>
	    <option <?php if($displayCarrier == 'Sprint') echo 'selected'; ?> value="Sprint">Sprint</option>
          </select>
        </div>
      </fieldset>
      
      <!-- Feed Information Fieldset -->
      <fieldset><legend>Feed Information</legend>
	
	<!-- Methods of Reception -->
	<div class="lineWidth">
	  <label class="leftCol" for="reception">Default Methods of Reception</label>
	  <object class="middleCol">
            <input type="checkbox" name="receive_email" value="yes" <?php if($displayReceiveEmail) echo 'checked'; ?>/>Email<br />
	    <input type="checkbox" name="receive_sms_text" value="yes" <?php if($displayReceiveText) echo 'checked'; ?>/>SMS (Text)<br />
	    <input type="checkbox" name="receive_sms_link" value="yes" <?php if($displayReceiveLink) echo 'checked'; ?>/>SMS (Link)<br />
          </object>
        </div>
	
	<!-- Feeds -->
        <div class="lineWidth">
	  <label for="feeds">Feeds</label>
	  <object class="middleCol">
            <div id="rightCol">
	      <?php
              if (isset($_REQUEST['feed']))
		{
		  foreach($_REQUEST['feed'] as $index=>$currentFeed)
		    {
		      print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" value=\"{$currentFeed}\"/><br />");
		    }
		}
	      else if (($userFeeds = $user->feeds) != NULL)
		{
		  $counter = 0;
		  foreach($userFeeds as $currentFeed)
		    {
		      $counter++;
		      print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" value=\"{$currentFeed->url}\"/><br />");
		    }
		  for(;$counter<3;$counter++)
		    {
		      print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" /><br />");		    
		    }
		}
	      else
		{
		  for($i = 0; $i < 3; $i++)
		    {
		      print("<input type=\"text\" name=\"feed[]\" maxlength=\"500\" /><br />");
		    }
		}
            ?>
            </div>
	  </object>
	  
	  <!--Add More Feeds -->
	</div>
	<div style="text-align:center;">
          <button class="clickable" type="button" onclick="addFeed()">Add More Feeds</button>
	  <input class="clickable" type="submit" name="submit" value="Update!"/>
	</div>
      </fieldset>
      
      
      <!-- Add Feed Function -->
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
    </form>
  </body>
</html>
