<?php
require_once('db.php');
require_once('db_mysql.php');
require_once('db_mysql_feeds.php');

/* class MySQLUsers implements iUsers on MySQL databases (see corresponding 
   documentation)
*/
class MySQLUsers extends MySQLDBObject implements iUsers {
  /* $db is the database which contains this user */
  private $db;
  /* $users is an array of MySQLUser objects which represent the users in this
     set */
  // XXX there may be a better way to do this
  public $users;

  /* $carrier_attr is the attribute name which corrosponds to carrier */
  static $carrier_attr = 'carrier';

  /* function MySQLUsers::__construct is the constructor for the class

     $db: (MySQLDB) a valid MySQLDB object connected to the MySQL
          database to use
  */
  private function __construct(array $users, MySQLDB $db) {
    $this->users = $users;
    $this->db = $db;
  }

  /* MySQLUsers::__search is a helper function to MySQLUsers::searchAll and
     MySQLUsers::searchAny which performs the actual search operations

     $userinfo: (array) user information to search by, encoded in key-value
                pairs as described for the $userinfo parameter in iUser::set
     $op: (string) the MySQL operator to use in between each search term 
          (e.g. 'OR' or 'AND')
     $db: (MySQLDB) a valid MySQLDB object connected to the MySQL database to
          use

     returns a MySQLUsers object representing the matched group of users
  */
  private static function __search(array $userinfo, $op, MySQLDB $db) {
    // build SQL query to use to search for users
    $search_sql = 'SELECT uid FROM users WHERE ';
    foreach ($userinfo as $attr=>$values) {
      foreach ((array) $values as $key=>$value) {
	if (isset(self::$userattrs_to_cols[$attr]))
	  $search_sql .= 
	    self::$userattrs_to_cols[$attr]."=? $op ";
	elseif($attr === self::$carrier_attr)
	  $search_sql .= 
	  "cid=(SELECT cid FROM carriors WHERE carrior_name=?) $op ";
      }
    }
    // remove trailing op and spaces
    $search_sql = substr($search_sql, 0, -(strlen($op) + 2));
    // add rest of SQL query
    $search_sql .= ';';

    // prepare SQL statement
    $search_stmt = $db->pdo->prepare($search_sql);

    // create array of column value bindings
    foreach ($userinfo as $attr=>$values)
      foreach ((array) $values as $key=>$value)
      if (isset(self::$userattrs_to_cols[$attr])
	  || $attr === self::$carrier_attr)
	  $search_binds[] = $value;

    $search_stmt->execute($search_binds);
    // set fetch mode to create instances of MySQLUser
    $search_stmt->setFetchMode(PDO::FETCH_CLASS, 'MySQLUser', array($db));

    // fetch the result and create a new instance of this class
    $search_result = $search_stmt->fetchAll();
    if ($search_result !== FALSE) {
      $c = __CLASS__;
      return new $c($search_result, $db);
    } else
      return NULL;
  }

/* MySQLUsers::searchAll implements iUsers::searchAll (see corresponding
   documentation)
*/
  public static function searchAll(array $userinfo, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAll($userinfo, $db);
  }
  /* MySQLUsers::__searchAll is a helper function to MySQLUsers::searchAll 
     which performs the actual search operation. This function was added in
     order to use typehinting on parameter $db.
  */
  private static function __searchAll(array $userinfo, MySQLDB $db) {
    return self::__search($userinfo, 'AND', $db);
  }

/* MySQLUsers::searchAny implements iUsers::searchAny (see corresponding
   documentation)
*/
  public static function searchAny(array $userinfo, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAny($userinfo, $db);
  }
  /* MySQLUsers::__searchAny is a helper function to MySQLUsers::searchAny 
     which performs the actual search operation. This function was added in
     order to use typehinting on parameter $db.
  */
  private static function __searchAny(array $userinfo, MySQLDB $db) {
    return self::__search($userinfo, 'OR', $db);
  }

/* MySQLUsers::merge implements iUsers::merge (see corresponding documentation)
*/
  public function merge(iUsers $users) {
    return self::__merge($users);
  }
  /* MySQLUsers::__merge is a helper function to MySQLUsers::merge which
     performs the actual merge operation. This function was added in order to
     use typehinting on parameter $users.
  */
  public function __merge(MySQLUsers $users) {
    if ($this->db !== $users->db)
      throw new InvalidArgumentException('$db must match between objects');
    $c = __CLASS__;
    return new $c(array_merge($this->users, $users->users), $this->db);
  }
}

/* class MySQLUser implements iUser on MySQL databases (see corresponding 
   documentation)
*/
class MySQLUser extends MySQLDBObject implements iUser {
  /* $db is the database which contains this user */
  private $db;
  /* $uid is the unique user identifier which is used to access user 
     information in the database */
  public $uid;

  static $carrier_attr = 'carrier';
  static $feeds_attr = 'feeds';
  static $reception_attrs_to_methods = array('send_email'=>'email',
					     'send_sms_link'=>'sms_link', 
					     'send_sms_text'=>'sms_text');

  /* function MySQLUser::__construct is the constructor for the class

     $db: (MySQLDB) a valid MySQLDB object connected to the MySQL database to
          use
  */
  private function __construct(MySQLDB $db) {
    $this->db = $db;
  }

  /* function MySQLDB::__get is the PHP magic 'get' function for the class */
  public function __get($name) {
    $ret = $this->get(array($name));
    if ($ret === NULL || !isset($ret[$name]))
      return NULL;
    else
      return $ret[$name];
  }

  /* function MySQLDB::__set is the PHP magic 'set' function for the class */
  public function __set($name, $value) {
    $this->set(array($name=>$value));
  }

  /* parseUserInfo transforms a $userinfo array, in the format taken by many
     iUser functions, into an associative array with keys as database column
     names
  */
  private static function parseUserInfo(array $userinfo, MySQLDB $db) {
    static $valid_userinfo_attrs = 
      array('username'=>TRUE, 'email'=>TRUE, 'password'=>TRUE, 
	    'phone_number'=>TRUE, 'phone_pin'=>TRUE, 'phone_confirmed'=>TRUE,
	    'email_pin'=>TRUE, 'email_confirmed'=>TRUE);

    $db_userinfo = array();

    // check for simultaneous pin and confirmed
    if (isset($userinfo['email_pin']) && isset($userinfo['email_confirmed']))
      throw new InvalidArgumentException('email_pin and email_confirmed '.
					 'attributes cannot be set '.
					 'simultaneously');
    if (isset($userinfo['phone_pin']) && isset($userinfo['phone_confirmed']))
      throw new InvalidArgumentException('phone_pin and phone_confirmed '.
					 'attributes cannot be set '.
					 'simultaneously');

    // rename the userinfo keys as database column names
    foreach ($userinfo as $key=>$value)
      if (isset($valid_userinfo_attrs[$key]))
	$db_userinfo[self::$userattrs_to_cols[$key]] = $value;

    return $db_userinfo;
  }

/* MySQLUser::find implements iUser::find (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $attr.
*/
  public static function find($attr, $value, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__find($attr, $value, $db);
  }

  /* MySQLUser::__find is a helper function to MySQLUser::find which performs
     the actual find operation. This function was added in order to use
     typehinting on parameter $db.
  */
  private static function __find($attr, $value, MySQLDB $db) {
    /* $valid_find_userattrs is a list of attributes from $userinfo which can
       be used as input to this function. Keep this list updated with
       MySQLDBObject::$userattrs_to_cols.
    */
    static $valid_find_userattrs = 
      array('id'=>TRUE, 'uid'=>TRUE, 'username'=>TRUE, 'email'=>TRUE,
	    'password'=>TRUE, 'phone_number'=>TRUE);

    if (isset($valid_find_userattrs[$attr]))
      $db_attr = self::$userattrs_to_cols[$attr];
    else
      throw new InvalidArgumentException('parameter $attr is not a valid '.
					 'attribute');


    $find_sql = "SELECT uid FROM users WHERE $db_attr=:value;";
    $find_stmt = $db->pdo->prepare($find_sql);
    $find_stmt->bindParam(':value', $value);
    $find_stmt->execute();
    // set fetch mode to create an instance of this class
    $find_stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array('db'=>$db));
    $find_result = $find_stmt->fetch();
    return $find_result !== FALSE ? $find_result : NULL;
  }

/* MySQLUser::set implements iUser::set (see corresponding documentation)
*/
  public function set(array $userinfo) {
    // parse $userinfo into a format able to be fed straight into the database
    $db_userinfo = self::parseUserInfo($userinfo, $this->db);

    $sql_added = FALSE;

    // carrier requires cid to be looked up in database
    static $carrier_col = 'cid';
    static $carrier_sql =
      '(SELECT cid FROM carriors WHERE carrior_name=:carrior_name)';
    $carrier_bind = array();
    if (isset($userinfo[self::$carrier_attr]))
      $carrier_bind['carrior_name'] = $userinfo[self::$carrier_attr];

    // build the SQL query to use to update the user
    $update_sql = 'UPDATE users SET ';
    // add column names and values
    foreach ($db_userinfo as $col=>$value) {
      $update_sql .= $col.'=:'.$col.', ';
      $sql_added = TRUE;
    }
    // add carrier name and value
    if (isset($userinfo[self::$carrier_attr])) {
      $update_sql .= $carrier_col.'='.$carrier_sql.', ';
      $sql_added = TRUE;
    }
    // remove trailing comma and space
    $update_sql = substr($update_sql, 0, -2);
    // add rest of UPDATE statment
    $update_sql .= ' WHERE uid=:uid;';

    // do not attempt the SELECT if no attrs were added to the select
    if ($sql_added === TRUE) {
      // prepare the SQL statement
      $update_stmt = $this->db->pdo->prepare($update_sql);

      // bind column values
      foreach (array_merge($db_userinfo, $carrier_bind) as $col=>$value)
	$update_stmt->bindValue(':'.$col, $value);
      // bind uid
      $update_stmt->bindParam(':uid', $this->uid);
    
      // execute the SQL statement
      $update_stmt->execute();
    }

    // deal with setting user reception methods
    $this->setReceptions($userinfo);

    // set feeds for user if necessary
    if (isset($userinfo[self::$feeds_attr]))
      $this->setFeeds($userinfo[self::$feeds_attr]);
  }

  /* MySQLUser::setReceptions sets reception methods for a user based on an
     arbitrary $userinfo array

     $userinfo: (array) user receptions to set, encoded in key-value pairs as
                described for the $userinfo parameter in MySQLUser::set
  */
  private function setReceptions(array $userinfo) {
    /* receptions require reception_method to be looked up 'reception_methods'
       and set for the user separately in 'receptions' */
    static $reception_enable_sql =
      'INSERT IGNORE INTO receptions (uid, rid) 
       VALUES (:uid, (SELECT rid FROM reception_methods 
                      WHERE method_type=:method));';
    static $reception_disable_sql =
      'DELETE FROM receptions WHERE uid=:uid AND 
       rid=(SELECT rid FROM reception_methods WHERE method_type=:method);';

    foreach (self::$reception_attrs_to_methods as $attr=>$method) {
      if (isset($userinfo[$attr])) {
	if ($userinfo[$attr] === TRUE) {
	  // we only need to prepare statement once
	  if (!isset($reception_enable_stmt)) {
	    // prepare the enabling statement
	    $reception_enable_stmt =
	      $this->db->pdo->prepare($reception_enable_sql);
	    // bind values and dynamic parameters
	    $reception_enable_stmt->bindValue(':uid', $this->uid);
	    $reception_enable_stmt->bindParam(':method', $method);
	  }

	  // execute the statement
	  $reception_enable_stmt->execute();
	} else if ($userinfo[$attr] === FALSE) {
	  // we only need to prepare statement once
	  if (!isset($reception_disable_stmt)) {
	  // prepare the disabling statement
	    $reception_disable_stmt = 
	      $this->db->pdo->prepare($reception_disable_sql);
	    // bind static values and dynamic parameters
	    $reception_disable_stmt->bindValue(':uid', $this->uid);
	    $reception_disable_stmt->bindParam(':method', $method);
	  }

	  // execute the statement
	  $reception_disable_stmt->execute();
	}
      }
    }
  }

  /* MySQLUser::getReceptions gets reception method settings for a user based
     on an arbitrary $userattrs array

     $userattrs: (array) an array of strings specifying the desired user
                 reception method settings to get, selected from the possible
		 keys in the list of key-value pairs returned by MySQLUser::get

    returns an array containing all requested user reception method settings
      that could be successfully fetched, in the form described in the
      description of the $userattrs parameter
  */
  private function getReceptions(array $userattrs) {
    /* receptions require reception_method to be looked up 'reception_methods'
       and retrieved for the user separately in 'receptions' */
    static $reception_sql =
      'SELECT TRUE FROM receptions WHERE uid=:uid AND 
       rid=(SELECT rid FROM reception_methods WHERE method_type=:method);';

    $userinfo = array();

    // prepare the enabling statement
    $reception_stmt = $this->db->pdo->prepare($reception_sql);
    // bind values and dynamic parameters
    $reception_stmt->bindValue(':uid', $this->uid);
    $reception_stmt->bindParam(':method', $method);
    foreach (self::$reception_attrs_to_methods as $attr=>$method) {
      if (in_array($attr, $userattrs)) {
	$reception_stmt->execute();
	$userinfo[$attr] = ($reception_stmt->fetch() !== FALSE);
      }
    }

    return $userinfo;
  }

/* MySQLUser::addFeed implements iUser::addFeed (see corresponding 
   documentation)
*/
  public function addFeed(iFeed $feed) {
    return $this->addFeeds(new MySQLFeeds(array($feed), $this->db));
  }

/* MySQLUser::addFeeds implements iUser::addFeeds (see corresponding 
   documentation)
*/
  public function addFeeds(iFeeds $feeds) {
    return $this->__addFeeds($feeds);
  }

  /* MySQLUser::__addFeeds is a helper function to MySQLUser::addFeeds which
     performs the actual add operation. This function was added in order to use
     typehinting on parameter $feeds.
  */
  private function __addFeeds(MySQLFeeds $feeds) {
    /* $feeds_find_sql is the SQL statement used to verify that the user has
       not already subscribed to a feed */
    static $feeds_find_sql =
      'SELECT TRUE FROM favorites WHERE uid=:uid AND sid=:sid;';
    /* $feeds_sql is the SQL statement used to add a feed subscription to the 
       user */
    static $feeds_sql = 
      'INSERT IGNORE INTO favorites (uid, sid, priority)
       VALUES (:uid, :sid, :priority)';

    // prepare the find subscription SQL statement (to be used multiple times)
    $feeds_find_stmt = $this->db->pdo->prepare($feeds_find_sql);
    // bind the static values
    $feeds_find_stmt->bindValue(':uid', $this->uid);
    // bind the sid to $sid
    $feeds_find_stmt->bindParam(':sid', $sid);

    // prepare the add subscription SQL statement (to be used multiple times)
    $feeds_stmt = $this->db->pdo->prepare($feeds_sql);
    // bind the static values
    $feeds_stmt->bindValue(':uid', $this->uid);
    // XXX this is a dummy priority value
    $feeds_stmt->bindValue(':priority', 0);
    // bind the sid to $sid
    $feeds_stmt->bindParam(':sid', $sid);

    foreach ($feeds as $feed) {
      // change the sid to use in the statement
      $sid = $feed->sid;

      // check that the user is not already subscribed
      $feeds_find_stmt->execute();
      if ($feeds_find_stmt->fetch() === FALSE) {
	// execute the add subscription statement
	$feeds_stmt->execute();
      }
    }
  }

  /* MySQLUser::setFeeds is a written as a helper function to MySQLUser::set
     which carries out the operation of setting a user's feeds

     $feeds: (MySQLFeeds) an iFeeds object representing the set of feeds to add
             to the user
  */
  private function setFeeds(MySQLFeeds $feeds) {
    static $delete_feeds_sql = 'DELETE FROM favorites WHERE uid=:uid';

    // delete the user's existing feeds
    $delete_feeds_stmt = $this->db->pdo->prepare($delete_feeds_sql);
    $delete_feeds_stmt->bindValue(':uid', $this->uid);
    $delete_feeds_stmt->execute();

    // add the new feeds to the user
    $this->addFeeds($feeds);
  }

  /* MySQLUser::getFeeds is a written as a helper function to MySQLUser::get
     which carries out the operation of getting a user's feeds

     returns a MySQLFeeds object representing the set of the user's feeds
  */
  private function getFeeds() {
    static $feeds_sql = 'SELECT sid FROM favorites WHERE uid=:uid;';
    $feeds_stmt = $this->db->pdo->prepare($feeds_sql);
    $feeds_stmt->bindParam(':uid', $this->uid);
    $feeds_stmt->execute();
    /* XXX creating the objects this way relies on DB consistency (sid is not
       checked to be existent in feed_sources table) */
    $feeds_stmt->setFetchMode(PDO::FETCH_CLASS, 'MySQLFeed', 
			      array('db'=>$this->db));
    $feeds_result = $feeds_stmt->fetchAll();
    return new MySQLFeeds($feeds_result, $this->db);
  }

/* MySQLUser::get implements iUser::get (see corresponding documentation)
*/
  public function get(array $userattrs) {
    /* $valid_userattrs is a list of attributes from $userattrs which can be
       handled by the simple sql query generator below. Keep this list updated
       with MySQLDBObject::$userattrs_to_cols.
    */
    static $valid_userattrs = 
      array('username'=>TRUE, 'email'=>TRUE, 'password'=>TRUE, 
	    'phone_number'=>TRUE);
    static $carrier_sql = '(SELECT carrior_name FROM carriors WHERE
                           cid=(SELECT cid FROM users WHERE uid=:uid2))';

    $sql_added = FALSE;
    $get_result = array();

    // build SQL query to use to get user attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($userattrs as $key=>$attr) {
      if (isset($valid_userattrs[$attr])) {
	$get_sql .= self::$userattrs_to_cols[$attr]." AS $attr, ";
	$sql_added = TRUE;
      } elseif ($attr === self::$carrier_attr) {
	$get_sql .= "$carrier_sql AS $attr, ";
	$sql_added = TRUE;
      }
    }
    // remove trailing comma and space
    $get_sql = substr($get_sql, 0, -2);
    // add rest of SQL query
    $get_sql .= ' FROM users WHERE uid=:uid;';

    // do not attempt the SELECT if no attrs were added to the select
    if ($sql_added === TRUE) {
      $get_stmt = $this->db->pdo->prepare($get_sql);
      $get_stmt->bindParam(':uid', $this->uid);

      // bind carrier specific column values
      if (in_array('carrier', $userattrs))
	$get_stmt->bindParam(':uid2', $this->uid);
    
      $get_stmt->execute();
      $get_result = $get_stmt->fetch(PDO::FETCH_ASSOC);
      if ($get_result === FALSE)
	throw new Exception('PDOStatement::fetch failed');
    }

    // get id if requested
    if (in_array('id', $userattrs))
      $get_result['id'] = $this->uid;
    if (in_array('uid', $userattrs))
      $get_result['uid'] = $this->uid;

    // get reception method settings if requested
    $get_result = array_merge($get_result, $this->getReceptions($userattrs));

    // get feeds if requested
    if (in_array(self::$feeds_attr, $userattrs))
      $get_result[self::$feeds_attr] = $this->getFeeds();

    return $get_result;
  }

/* MySQLUser::create implements iUser::create (see corresponding documentation)
*/
  public static function create(array $userinfo, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($userinfo, $db);
  }

  /* MySQLUser::__create is a helper function to MySQLUser::create which
     performs the actual create operation. This function was added in order to 
     use typehinting on parameter $db.
  */
  private static function __create($userinfo, MySQLDB $db) {
    // parse $userinfo into a format able to be fed straight into the database
    $db_userinfo = self::parseUserInfo($userinfo, $db);

    // carrier requires cid to be looked up in database
    static $carrier_col = 'cid';
    static $carrier_sql =
      '(SELECT cid FROM carriors WHERE carrior_name=:carrior_name)';
    $carrier_bind = array('carrior_name'=>$userinfo['carrier']);

    // build the SQL query to use to create the user
    $create_sql = 'INSERT INTO users (';
    // add column names
    foreach ($db_userinfo as $col=>$value)
      $create_sql .= $col.', ';
    $create_sql .= $carrier_col.', ';
    // remove trailing comma and space
    $create_sql = substr($create_sql, 0, -2);
    // add column values
    $create_sql .= ') VALUES (';
    foreach ($db_userinfo as $col=>$value)
      $create_sql .= ':'.$col.', ';
    $create_sql .= $carrier_sql.', ';
    // remove trailing comma and space
    $create_sql = substr($create_sql, 0, -2);
    $create_sql .= ');';

    // prepare the SQL statement
    $create_stmt = $db->pdo->prepare($create_sql);

    // bind column values
    foreach (array_merge($db_userinfo, $carrier_bind) as $col=>$value)
      $create_stmt->bindValue(':'.$col, $value);

    // execute the SQL statement
    $create_stmt->execute();

    // construct the MySQLUser object
    // XXX check that using PDO::lastInsertId is not a race
    $c = __CLASS__;
    $user = new $c($db);
    $user->uid = $db->pdo->lastInsertId();

    // deal with setting user reception methods
    $user->setReceptions($userinfo);

    // set the user's inital feeds
    if (isset($userinfo[self::$feeds_attr]))
      $user->feeds = $userinfo[self::$feeds_attr];

    return $user;
  }

/* MySQLUser::delete implements iUser::delete (see corresponding documentation)
*/
  public function delete() {
    // build the SQL query to use to delete the user
    static $delete_sql = 'DELETE FROM users WHERE uid=:uid;';
    // prepare the SQL statement
    $delete_stmt = $this->db->pdo->prepare($delete_sql);
    // bind column values
    $delete_stmt->bindValue(':uid', $this->uid);
    // execute the SQL statement
    $delete_stmt->execute();

    /* unset $this->uid so that future operations on this MySQLUser object
       will fail */
    unset ($this->uid);
  }
}
