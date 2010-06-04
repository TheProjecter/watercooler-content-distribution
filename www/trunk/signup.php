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
    <fieldset id="feedback">
      <?php include_once('verifySignup.php'); ?>
    </fieldset>

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
  </body>
</html>
