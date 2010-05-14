<?php

/* interface iDatabase includes all operations that directly involve the
   underlying database. Most higher level operations involving objects in the
   database should be in their other respective interfaces.
*/
interface iDatabase {
  // XXX fill this in
}

/* interface iFeeds represents a group of feeds, and handles all operations
   involving multiple feeds
*/
interface iFeeds {
  // XXX fill this in
}

/* interface iFeed handles all operations involving a single feed
*/
interface iFeed {
  // XXX fill this in
}

/* interface iUsers represents a group of users, and handles all operations
   directly involving multiple users
*/
interface iUsers {
/* function iUsers::searchAll searches for users in the database matching ALL
   the given user information

   $userinfo: (array) the user information to use in the search, encoded in
              the following key-value pairs
	         'uid': (integer) the user's id number
	         'username': (string) the user's username
	 	 'email': (string) the user's email
		 'phone': (string) the user's cell phone number
		 'carrier': (string) the user's cell phone carrier
		 'send_email': (boolean) TRUE if the user selected email
		   delivery, FALSE otherwise
		 'send_sms_text': (boolean) TRUE if the user selected SMS text
		   delivery, FALSE otherwise
		 'send_sms_link': (boolean) TRUE if the user selected SMS link
		   delivery, FALSE otherwise
		 'feeds': (iFeeds) an object representing the user's 
		   subscribed feeds

    returns an iUsers object representing the matched users or NULL if an error
      occurred
*/
  public static function searchAll($userinfo);

/* function iUsers::searchAny searches for users in the database matching ANY
   of the given user information

   $userinfo: (array) the user information to use in the search, encoded in
              the following key-value pairs
	         'uid': (array of integers) the users' id numbers
	         'username': (array of strings) the users' usernames
	 	 'email': (array of strings) the users' emails
		 'phone': (array of strings) the users' cell phone numbers
		 'carrier': (array of strings) the users' cell phone carriers
		 'send_email': (boolean) TRUE if the user selected email
		   delivery, FALSE otherwise
		 'send_sms_text': (boolean) TRUE if the user selected SMS text
		   delivery, FALSE otherwise
		 'send_sms_link': (boolean) TRUE if the user selected SMS link
		   delivery, FALSE otherwise
		 'feeds': (iFeeds) an object representing the users' 
		   subscribed feeds

    returns an iUsers object representing the matched users or NULL if an error
      occurred
*/
  public static function searchAny($userinfo);

/* function iUsers::merge merges this iUsers object with another, creating a
   new iUser object that represents all users in both groups

   $users: (iUsers) the iUsers object to merge with

   returns an iUsers object representing all users in both groups, or NULL if
     an error occurred
*/
  public function merge(iUsers $users);
}

/* interface iUser handles all operations involving a single user
*/
interface iUser {
/* function iUser::find finds a user by any attribute guaranteed to be unique
   for each user

   $attr: (string) an attribute name, selected from the following attribute-
          value pairs
	    'uid': (integer) the user's id number
            'username': (string) the user's username
	    'email': (string) the user's email
	    'phone': (string) the user's phone number

   returns an iUser object representing the matched user, or NULL if an error
     occurred
*/
  public static function find($attr, $value);

/* function iUser::set sets the user's information in the database

   $userinfo: (array) the user information to set, encoded in the following 
              key-value pairs
	        'username': (string) the desired username
		'password': (string) the desired password, in plaintext
		'email': (string) the user's email
		'phone': (string) the user's cell phone number, with no spaces
		  or dashes, optionally with a '+' as the first character
	        'carrier': (string) the user's cell phone carrier
		'send_email': (boolean) TRUE if the user selected email 
		  delivery, FALSE otherwise
	        'send_sms_text': (boolean) TRUE if the user selected SMS text
		  delivery, FALSE otherwise
	        'send_sms_link': (boolean) TRUE if the user selected SMS link
		  delivery, FALSE otherwise
		'feeds': (iFeeds) an object representing the user's desired
		  feed subscriptions

    returns TRUE if the operation succeeded
*/
  public function set($userinfo);

/* function iUser::get gets the user's information from the database

   $userattrs: (array) an array of strings specifying the desired user
               attributes to get, selected from the possible keys in the
	       following list of key-value pairs returned by this function
	         'uid': (integer) the user's id number
	         'username': (string) the user's username
	 	 'email': (string) the user's email
		 'phone': (string) the user's cell phone number
		 'carrier': (string) the user's cell phone carrier
		 'send_email': (boolean) TRUE if the user selected email
		   delivery, FALSE otherwise
		 'send_sms_text': (boolean) TRUE if the user selected SMS text
		   delivery, FALSE otherwise
		 'send_sms_link': (boolean) TRUE if the user selected SMS link
		   delivery, FALSE otherwise
		 'feeds': (iFeeds) an object representing the user's 
		   subscribed feeds

    returns an array containing all requested user information that could be
      successfully fetched, in the form described in the description of the
      $userattrs parameter
*/
  public function get($userattrs);

/* function iUser::validatePassword checks if $password matches the user's
   password in the database.

   $password: (string) the password to validate, in plaintext

   returns TRUE if $password matches the user's password in the database
*/
  public function validatePassword($password);

/* function iUser::create registers a new user in the database using 
   information from $userinfo

   $userinfo: (array) initial user information to set, encoded in key-value
              pairs as described for the $userinfo parameter in iUser::set

   returns an iUser object representing the newly created user, or NULL if
     an error occurred
*/
  public static function create($userinfo);

/* function iUser::delete deletes the user and all information associated with
   the user in the database

   returns TRUE if the operation succeded
*/
  public function delete();
}


// sample use of iUser
/* PHP 5.3.0 CODE (referencing a class using a variable)
function test_iUser($user_class) {
  if (// user creation
      !($test_user = $user_class::create(array('username'=>'test_iUser')))
      // duplicate user creation (should fail)
      || ($user_class::create(array('username'=>'test_iUser')))
      // second user creation
      || !($test_user_2 = 
	   $user_class::create(array('username'=>'test_iUser_2')))
      // user deletion
      || !($test_user->delete())
      // get username
      || ($test_user_2->get(array('username')) != 'test_iUser_2')
      // find by username
      || !($test_user_2_again = $user_class::find('username', 'test_iUser_2'))
      || ($test_user_2_again->get('username') != $test_user_2->get('username'))
      // set username
      || !($test_user_2_again->set(array('username'=>'test_iUser_2_again')))
      || ($test_user_2_again->get('username') != 'test_iUser_2_again'))
    return FALSE;
  return TRUE;
}
*/