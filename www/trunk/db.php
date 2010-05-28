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

/* interface iStories represents a group of feed stories, and handles all
   operations involving multiple feed stories
*/
interface iStories extends Iterator {
}

/* interface iStory handles all operations involving a single feed story
*/
interface iStory {
/* function iStory::find finds a feed by any attribute guaranteed to be unique
   for each feed story

   $attr: (string) an attribute name, selected from the following attribute-
          value pairs
	    'fid': (integer) the feeds's id number
   $value: (mixed) the value associated with the attribute
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iFeed object representing the matched user, or NULL if none was
     found
*/
  public static function find($attr, $value, iDatabase $db = NULL);

/* function iStory::get gets the feed story's information from the database

   $storyattrs: (array) an array of strings specifying the desired feed story
               attributes to get, selected from the possible keys in the
	       following list of key-value pairs returned by this function
	         'fid': (integer) the feed story's id number
	         'title': (string) the feed story's title
		 'content': (string) the feed story's content
		 'url': (string) the feed story's url
		 'timestamp': (integer) the feed story's timestamp, in seconds
		              since 1970-01-01 00:00:00 UTC
		 'feed': (iFeed) the feed story's source feed
		 'category': (string) the feed story's category

    returns an array containing all requested feed story information that could
      be successfully fetched, in the form described in the description of the
      $storyattrs parameter
*/
  public function get(array $feedattrs);
}

/* interface iFeeds represents a group of feed sources, and handles all
   operations involving multiple feed sources
*/
interface iFeeds extends Iterator {
/* function iFeeds::create registers multiple feeds in the database using 
   information from $feedinfos or updates their information if they already
   exist

   $feedinfos: (array) initial feed information to set, encoded in an array of
               arrays with key-value pairs as described for the $feedinfo
	       parameter in iFeed::set
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iFeeds object representing the registered or updated feeds
*/
  public static function create(array $feedinfos, iDatabase $db = NULL);
}

/* interface iFeed handles all operations involving a single feed source
*/
interface iFeed {
/* function iFeed::find finds a feed by any attribute guaranteed to be unique
   for each feed

   $attr: (string) an attribute name, selected from the following attribute-
          value pairs
	    'sid': (integer) the feeds's id number
	    'name': (string) the feed's name
	    'url': (string) the feed's url
   $value: (mixed) the value associated with the attribute
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iFeed object representing the matched user, or NULL if none was
     found
*/
  public static function find($attr, $value, iDatabase $db = NULL);

/* function iFeed::create registers a new feed in the database using 
   information from $feedinfo, or finds one by URL if it already exists

   $feedinfo: (array) initial feed information to set, encoded in key-value
              pairs as described for the $feedinfo parameter in iFeed::set
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iFeed object representing the newly created/found feed
*/
  public static function create(array $feedinfo, iDatabase $db = NULL);

/* function iFeed::get gets the feed's information from the database

   $feedattrs: (array) an array of strings specifying the desired feed
               attributes to get, selected from the possible keys in the
	       following list of key-value pairs returned by this function
	         'sid': (integer) the feed's id number
	         'name': (string) the feed's name
		 'url': (string) the feed's url

    returns an array containing all requested feed information that could be
      successfully fetched, in the form described in the description of the
      $feedattrs parameter
*/
  public function get(array $feedattrs);

/* function iFeed::delete deletes the feed and all information associated with
   the feed in the database. Do not use an iFeed object after deleting it.
*/
  public function delete();
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

    returns an iUsers object representing the matched users
*/
  public static function searchAll(array $userinfo, iDatabase $db = NULL);

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

    returns an iUsers object representing the matched users
*/
  public static function searchAny(array $userinfo, iDatabase $db = NULL);

/* function iUsers::merge merges this iUsers object with another, creating a
   new iUser object that represents all users in both groups

   $users: (object) the iUsers implementing object to merge with. Note that the
           two objects to merge must be instances of the same class and objects
	   from the same database

   returns an iUsers implementing object representing all users in both groups
*/
  public function merge(iUsers $users);
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
   $value: (mixed) the value associated with the attribute
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iUser object representing the matched user, or NULL if none was
     found
*/
  public static function find($attr, $value, iDatabase $db = NULL);

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
*/
  public function set(array $userinfo);

/* function iUser::addFeeds adds feeds to a user's list of subscribed feeds

   $feeds: (iFeeds) the set of feeds to add to the user
*/
  public function addFeeds(iFeeds $feeds);

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
  public function get(array $userattrs);

/* function iUser::create registers a new user in the database using 
   information from $userinfo

   $userinfo: (array) initial user information to set, encoded in key-value
              pairs as described for the $userinfo parameter in iUser::set
   $db: (object) an object representing the database to use, or NULL to use
        the database established as the site default. Note that the type of
	object required for this parameter is implementation-specific

   returns an iUser object representing the newly created user
*/
  public static function create(array $userinfo, iDatabase $db = NULL);

/* function iUser::delete deletes the user and all information associated with
   the user in the database. Do not use an iUser object after deleting it.
*/
  public function delete();
}

/* abstract class DatabaseObject should be the base class for all classes
   representing database objects, if its functionality is needed that is
*/
abstract class DatabaseObject {
  /* $site_db is the site default database, set by
     DatabaseObject::setAsSiteDefault */
  protected static $site_db;
}
