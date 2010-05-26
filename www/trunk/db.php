<?php

/* interface iDatabase includes all operations that directly involve the
   underlying database. Most higher level operations involving objects in the
   database should be in their other respective interfaces.
*/
interface iDatabase {
/* function iDatabase::connect connects to a database using the specified
   configuration variables

   $cfg_vars: (array) the configuration variables for the connection to make, 
              encoded in key-value pairs which are to be defined by the 
	      implementation (see corresponding documentation)

   returns an iDatabase implementing object connected to the specified database
*/
  public static function connect(array $cfg_vars);
/* function iDatabase::connectFromIni connects to a database using 
   configuration variables read from the specified ini file

   $cfg_vars: (string) the filename of an ini file which contains the
              configuration variables for the connection to make, encoded in
	      ini sections and variables which are to be defined by the
	      implementation (see corresponding documentation)

   returns an iDatabase implementing object connected to the specified database
*/
  public static function connectFromIni($cfg_file);

/* iDatabase::setAsSiteDefault sets the site default database to the current
   database object. This site default is used in other database object classes
   as the default database to access (see corresponding documentation).
*/
  public function setAsSiteDefault();
}

/* interface iFeeds represents a group of feed sources, and handles all
   operations involving multiple feed sources
*/
interface iFeeds {
  // XXX fill this in
}

/* interface iFeed handles all operations involving a single feed source
*/
interface iFeed {
  // XXX fill this in
}

/* interface iUsers represents a group of users, and handles all database
   operations directly involving multiple users
*/
interface iUsers {
/* function iUsers::searchAll searches for users in the database matching ALL
   the given user information

   $userinfo: (array) the user information to use in the search, encoded in
              the following key-value pairs
	         'uid': (integer) the user's id number
	         'username': (string) the user's username
	 	 'email': (string) the user's email
		 'phone_number': (string) the user's cell phone number
		 'carrier': (string) the user's cell phone carrier
		 'send_email': (boolean) TRUE if the user selected email
		   delivery, FALSE otherwise
		 'send_sms_text': (boolean) TRUE if the user selected SMS text
		   delivery, FALSE otherwise
		 'send_sms_link': (boolean) TRUE if the user selected SMS link
		   delivery, FALSE otherwise
		 'feeds': (iFeeds) an object representing the user's 
		   subscribed feeds
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

    returns an iUsers object representing the matched users or NULL if an error
      occurred
*/
  public static function searchAll($userinfo, $db = NULL);

/* function iUsers::searchAny searches for users in the database matching ANY
   of the given user information

   $userinfo: (array) the user information to use in the search, encoded in
              the following key-value pairs
	         'uid': (array of integers) the users' id numbers
	         'username': (array of strings) the users' usernames
	 	 'email': (array of strings) the users' emails
		 'phone_number': (array of strings) the users' cell phone
		   numbers
		 'carrier': (array of strings) the users' cell phone carriers
		 'send_email': (boolean) TRUE if the user selected email
		   delivery, FALSE otherwise
		 'send_sms_text': (boolean) TRUE if the user selected SMS text
		   delivery, FALSE otherwise
		 'send_sms_link': (boolean) TRUE if the user selected SMS link
		   delivery, FALSE otherwise
		 'feeds': (iFeeds) an object representing the users' 
		   subscribed feeds
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

    returns an iUsers object representing the matched users or NULL if an error
      occurred
*/
  public static function searchAny($userinfo, $db = NULL);

/* function iUsers::merge merges this iUsers object with another, creating a
   new iUser object that represents all users in both groups

   $users: (object) the iUsers implementing object to merge with. Note that the
           two objects to merge must be instances of the same class and objects
	   from the same database

   returns an iUsers implementing object representing all users in both groups,
     or NULL if an error occurred
*/
  public function merge($users);
}

/* interface iUser handles all database operations involving a single user
*/
interface iUser {
/* function iUser::find finds a user by any attribute guaranteed to be unique
   for each user

   $attr: (string) an attribute name, selected from the following attribute-
          value pairs
	    'uid': (integer) the user's id number
            'username': (string) the user's username
	    'email': (string) the user's email
	    'phone_number': (string) the user's phone number
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iUser object representing the matched user, or NULL if an error
     occurred
*/
  public static function find($attr, $value, $db = NULL);

/* function iUser::set sets the user's information in the database

   $userinfo: (array) the user information to set, encoded in the following 
              key-value pairs
	        'username': (string) the desired username
		'password': (string) the desired password, in plaintext or as a
		            hash
		'email': (string) the user's email
		'phone_number': (string) the user's cell phone number, with
		  no spaces or dashes, optionally with a '+' as the first
		  character
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
		 'password': (string) the user's password (or hash of password)
	 	 'email': (string) the user's email
		 'phone_number': (string) the user's cell phone number
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

/* function iUser::create registers a new user in the database using 
   information from $userinfo

   $userinfo: (array) initial user information to set, encoded in key-value
              pairs as described for the $userinfo parameter in iUser::set
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iUser object representing the newly created user, or NULL if
     an error occurred
*/
  public static function create($userinfo, $db = NULL);

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

/* abstract class DatabaseObject should be the base class for all classes
   representing database objects, if its functionality is needed that is
*/
abstract class DatabaseObject {
  /* $site_db is the site default database, set by
     DatabaseObject::setAsSiteDefault */
  protected static $site_db;
}
