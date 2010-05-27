<?php
require_once('db.php');

/* class MySQLDBObject provides a base class for all classes which
   represent objects in a MySQL database
*/
class MySQLDBObject extends DatabaseObject {
  /* $pdo is defined here so sibling classes to MySQLDB can access its
     PDO and thus perform low-level operations on a given MySQL database */
  protected $pdo;

  /* $userattrs_to_cols is an associative array mapping attribute names given 
     as a parameter to methods in classes derived from this one to column
     names in the MySQL database. Note that the following attributes are 
     missing and require special handling
       'carrier': column 'cid' needs to be looked up in 'carriors' table by
         'carrior_name' and entered in user table under column 'cid' */
  protected static $userattrs_to_cols = 
    array('username'=>'username',
	  'email'=>'email',
	  'password'=>'password',
	  'phone_number'=>'phone_number');

  protected static $feedattrs_to_cols =
    array('name'=>'source_name',
	  'url'=>'source_url');
}

/* class MySQLDB implements iDatabase on MySQL databases (see
   corresponding documentation)
*/
class MySQLDB extends MySQLDBObject implements iDatabase {
/* string MySQLDB::cfg_ini_main_section is the name of the section in 
   the ini config files passed to MySQLDB::connectFromIni containing the
   main connection parameters. This is also the base prefix for the opts ini 
   section which is named MySQLDB::cfg_ini_main_section.' opts'.
*/
  const cfg_ini_main_section = __CLASS__;

  /* $dsn_cfg_vars is an array of the the connection variables from which the
     dsn (the first argument to the PDO constructor) should be constructed */
  private static $dsn_cfg_vars = array('host', 'port', 'dbname');

/* function MySQLDB::__construct is the constructor for the class

   $pdo: (PDO object) a valid PDO object connected to the MySQL database to 
         use
*/
  private function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

/* function MySQLDB::setAsSiteDefault implements 
   iDatabase::setAsSiteDefault (see corresponding documentation)
*/
  public function setAsSiteDefault() {
    self::$site_db = $this;
  }

/* function MySQLDB::connect implements iDatabase::connect (see 
   corresponding documentation)

   $cfg_vars: (array) the configuration variables for the database connection,
              encoded in the following key-value pairs:
	      'filename': (string) the absolute path to the file which contains
	        the MySQL database (required)
	      'opts': (array) an associative array of PDO connection options,
	        or NULL for PHP defaults (see PHP Manual documentation for PDO
		and the PDO MySQL driver)

   returns a MySQLDB object connected to the database
*/
  public static function connect(array $cfg_vars) {
    if (!isset($cfg_vars['dbname']))
	throw new InvalidArgumentException('dbname is a required key-value '.
					   'pair in parameter $cfg_vars');

    // construct dsn string
    $dsn = 'mysql:';
    foreach (self::$dsn_cfg_vars as $varname)
      if (isset($cfg_vars[$varname]))
	$dsn .= "$varname={$cfg_vars[$varname]};";

    // create PDO object
    $pdo = new PDO($dsn, $cfg_vars['username'], $cfg_vars['password'], 
		   $cfg_vars['opts']);

    // set PDO error mode so that we get exceptions instead of PHP errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $c = __CLASS__;
    $db = new $c($pdo);

    return $db;
  }

/* function MySQLDB::connectFromIni implements iDatabase::connectFromIni
   (see corresponding documentation)

   $cfg_file: (string) the ini file to read connection configuration variables
              from, encoded in the var-value pairs listed in the documentation
	      for MySQLDB::connect, split into the following sections:
	      Section 'MySQLDB' contains
	        'username', 'password', 'host', 'port', 'dbname'
	      Section 'MySQLDB opts' contains
	        the PDO connection options, encoded in var-value pairs with the
		variable names being names of PDO constants, and the values
		being the desired corresponding values for the options. Note
		that values MUST be single quoted to avoid values from being
		interpreted by PHP (see PHP Manual documentation for PDO and
		the PDO MySQL driver for PDO connection options).

   returns a MySQLDB object connected to the database
*/
  public static function connectFromIni($cfg_file) {
    $cfg = @parse_ini_file($cfg_file, TRUE);
    if ($cfg === FALSE) {
      $e = error_get_last();
      throw new ErrorException($e['message'], 0, $e['type'], 
			       $e['file'], $e['line']);
    }

    $cfg_vars = $cfg[self::cfg_ini_main_section];

    // parse PDO options
    if (isset($cfg[self::cfg_ini_main_section.' opts']))
      foreach ($cfg[self::cfg_ini_main_section.' opts'] as $key=>$value)
	$cfg_vars['opts'][constant($key)] = $value;

    return self::connect($cfg_vars);
  }
}

class MySQLFeeds extends MySQLDBObject implements iFeeds, Iterator {
  private $db;
  public $feeds;

  public function __construct(array $feeds, MySQLDB $db) {
    $this->feeds = $feeds;
    $this->db = $db;
  }

/* MySQLFeeds::create implements iFeeds::create (see corresponding 
   documentation)
*/
  public static function create($feedinfos, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($feedinfos, $db);
  }

  /* MySQLFeeds::__create is a helper function to MySQLFeeds::create which
     performs the actual create operation. This function was added in order to 
     use typehinting on parameter $db.
  */
  private static function __create($feedinfos, MySQLDB $db) {
    foreach ($feedinfos as $feedinfo)
      $feeds[] = MySQLFeed::create($feedinfo, $db);
    $c = __CLASS__;
    return new $c($feeds, $db);
  }

  // these functions implement Iterator
  public function rewind() {
    reset($this->feeds);
  }
  public function current() {
    return current($this->feeds);
  }
  public function key() {
    return key($this->feeds);
  }
  public function next() {
    return next($this->feeds);
  }
  public function valid() {
    return ($this->current() !== FALSE);
  }
}

class MySQLFeed extends MySQLDBObject implements iFeed {
  private $db;
  /* $sid is the unique feed identifier which is used to access feed
     information in the database */
  private $sid;

  /* function MySQLFeed::__construct is the constructor for the class

     $db: (MySQLDB object) a valid MySQLDB object connected to the MySQL
          database to use
  */
  public function __construct(MySQLDB $db) {
    $this->db = $db;
  }

  /* function MySQLFeed::__get is the PHP magic 'get' function for the class */
  public function __get($name) {
    $ret = $this->get(array($name));
    if ($ret === NULL || !isset($ret[$name]))
      return NULL;
    else
      return $ret[$name];
  }

  /* parseFeedInfo transforms a $feedinfo array, in the format taken by many
     iFeed functions, into an associative array with keys as database column
     names
  */
  private static function parseFeedInfo($feedinfo, MySQLDB $db) {
    static $feedinfo_to_cols = 
      array('name'=>'source_name', 'url'=>'source_url');

    // rename the feedinfo keys as database column names
    foreach ($feedinfo as $key=>$value)
      if ($feedinfo_to_cols[$key] !== NULL)
	$db_feedinfo[$feedinfo_to_cols[$key]] = $value;

    return $db_feedinfo;
  }

/* MySQLFeed::find implements iFeed::find (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $attr.
*/
  public static function find($attr, $value, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__find($attr, $value, $db);
  }

  /* MySQLFeed::__find is a helper function to MySQLFeed::find which performs
     the actual find operation. This function was added in order to use
     typehinting on parameter $db.
  */
  private static function __find($attr, $value, MySQLDB $db) {
    $db_feedinfo = self::parseFeedInfo(array($attr=>$value), $db);

    // XXX better way to do this
    foreach ($db_feedinfo as $db_feedinfo_attr=>$db_feedinfo_value) {
      $db_attr = $db_feedinfo_attr;
      $db_value = $db_feedinfo_value;
    }

    $find_sql = "SELECT sid FROM feed_sources WHERE $db_attr=:value;";
    $find_stmt = $db->pdo->prepare($find_sql);
    $find_stmt->bindParam(':value', $db_value);
    $find_stmt->execute();
    // set fetch mode to create an instance of this class
    $find_stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array('db'=>$db));
    $find_result = $find_stmt->fetch();
    return $find_result !== FALSE ? $find_result : NULL;
  }


/* MySQLFeed::create implements iFeed::create (see corresponding 
   documentation)
*/
  public static function create($feedinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($feedinfo, $db);
  }

  public static function __create($feedinfo, MySQLDB $db) {
    // parse $feedinfo into a format able to be fed straight into database
    $db_feedinfo = self::parseFeedInfo($feedinfo, $db);
    
    // build the SQL query to use to replace the feed
    $create_sql = 'INSERT IGNORE INTO feed_sources (';
    // add column names
    foreach ($db_feedinfo as $col=>$value)
      $create_sql .= $col.', ';
    // remove trailing comma and space
    $create_sql = substr($create_sql, 0, -2);
    // add column values
    $create_sql .= ') VALUES (';
    foreach ($db_feedinfo as $col=>$value)
      $create_sql .= ':'.$col.', ';
    // remove trailing comma and space
    $create_sql = substr($create_sql, 0, -2);
    $create_sql .= ');';
    
    // prepare the SQL statement
    $create_stmt = $db->pdo->prepare($create_sql);
    
    // bind column values
    foreach ($db_feedinfo as $col=>$value)
      $create_stmt->bindValue(':'.$col, $value);
    
    // execute the SQL statement
    $create_stmt->execute();

    return MySQLFeed::find('name', $feedinfo['name'], $db);
  }

/* MySQLFeed::get implements iFeed::get (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $userattr.
*/
  public function get($feedattrs) {
    $sql_added = FALSE;

    // build SQL query to use to get user attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($feedattrs as $key=>$attr) {
      if (isset(self::$feedattrs_to_cols[$attr])) {
	$get_sql .= self::$feedattrs_to_cols[$attr]." AS $attr, ";
	$sql_added = TRUE;
      }
    }
    // remove trailing comma and space
    $get_sql = substr($get_sql, 0, -2);
    // add rest of SQL query
    $get_sql .= ' FROM feed_sources WHERE sid=:sid;';

    // do not attempt the SELECT if no attrs were added to the select
    if ($sql_added === TRUE) {
      $get_stmt = $this->db->pdo->prepare($get_sql);
      $get_stmt->bindParam(':sid', $this->sid);

      $get_stmt->execute();
      $get_result = $get_stmt->fetch(PDO::FETCH_ASSOC);
      if ($get_result === FALSE)
	throw new Exception('PDOStatement::fetch failed');
    }

    return $get_result;
  }
}

/* class MySQLUsers implements iUsers on MySQL databases (see corresponding 
   documentation)
*/
class MySQLUsers extends MySQLDBObject implements iUsers {
  private $db;
  public $users;

  private function __construct(array $users, MySQLDB $db) {
    $this->users = $users;
    $this->db = $db;
  }

  private static function __search($userinfo, $op, MySQLDB $db) {
    static $carrier_attr = 'carrier';

    // build SQL query to use to search for users
    $search_sql = 'SELECT uid FROM users WHERE ';
    foreach ($userinfo as $attr=>$values) {
      foreach ((array) $values as $key=>$value) {
	if (isset(self::$userattrs_to_cols[$attr]))
	  $search_sql .= 
	    self::$userattrs_to_cols[$attr]."=? $op ";
	elseif($attr === $carrier_attr)
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
	if (isset(self::$userattrs_to_cols[$attr]) || $attr === $carrier_attr)
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
   documentation). This function is safe to SQL injection.
*/
  public static function searchAll($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAll($userinfo, $db);
  }
  /* MySQLUsers::__searchAll is a helper function to MySQLUsers::searchAll 
     which performs the actual search operation. This function was added in
     order to use typehinting on parameter $db.
  */
  private static function __searchAll($userinfo, MySQLDB $db) {
    return self::__search($userinfo, 'AND', $db);
  }

/* MySQLUsers::searchAny implements iUsers::searchAny (see corresponding
   documentation). This function is safe to SQL injection.
*/
  public static function searchAny($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAny($userinfo, $db);
  }
  /* MySQLUsers::__searchAny is a helper function to MySQLUsers::searchAny 
     which performs the actual search operation. This function was added in
     order to use typehinting on parameter $db.
  */
  private static function __searchAny($userinfo, MySQLDB $db) {
    return self::__search($userinfo, 'OR', $db);
  }

/* MySQLUsers::merge implements iUsers::merge (see corresponding 
   documentation).
*/
  public function merge($users) {
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
  private $uid;

  /* function MySQLuser::__construct is the constructor for the class

     $db: (MySQLDB object) a valid MySQLDB object connected to the MySQL
          database to use
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
  private static function parseUserInfo($userinfo, MySQLDB $db) {
    static $userinfo_to_cols = 
      array('username'=>'username', 'password'=>'password', 'email'=>'email', 
	    'phone_number'=>'phone_number');

    // rename the userinfo keys as database column names
    foreach ($userinfo as $key=>$value)
      if ($userinfo_to_cols[$key] !== NULL)
	$db_userinfo[$userinfo_to_cols[$key]] = $value;

    // XXX fake unused database fields for now
    $db_userinfo['status'] = 1;

    return $db_userinfo;
  }

/* MySQLUser::find implements iUser::find (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $attr.
*/
  public static function find($attr, $value, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__find($attr, $value, $db);
  }

  /* MySQLUser::__find is a helper function to MySQLUser::find which performs
     the actual find operation. This function was added in order to use
     typehinting on parameter $db.
  */
  private static function __find($attr, $value, MySQLDB $db) {
    $find_sql = "SELECT uid FROM users WHERE $attr=:value;";
    $find_stmt = $db->pdo->prepare($find_sql);
    $find_stmt->bindParam(':value', $value);
    $find_stmt->execute();
    // set fetch mode to create an instance of this class
    $find_stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array('db'=>$db));
    $find_result = $find_stmt->fetch();
    return $find_result !== FALSE ? $find_result : NULL;
  }

/* MySQLUser::set implements iUser::set (see corresponding documentation).
   This function IS vulnerable to SQL injection in keys to array parameter 
   $userinfo.
*/
  public function set($userinfo) {
    // parse $userinfo into a format able to be fed straight into the database
    $db_userinfo = self::parseUserInfo($userinfo, $this->db);

    // carrier requires cid to be looked up in database
    static $carrier_col = 'cid';
    static $carrier_sql =
      '(SELECT cid FROM carriors WHERE carrior_name=:carrior_name)';
    $carrier_bind = array();
    if (isset($userinfo['carrier']))
      $carrier_bind['carrior_name'] = $userinfo['carrier'];

    // build the SQL query to use to update the user
    $update_sql = 'UPDATE users SET ';
    // add column names and values
    foreach ($db_userinfo as $col=>$value)
      $update_sql .= $col.'=:'.$col.', ';
    // add carrier name and value
    if (isset($userinfo['carrier']))
      $update_sql .= $carrier_col.'='.$carrier_sql.', ';
    // remove trailing comma and space
    $update_sql = substr($update_sql, 0, -2);
    // add rest of UPDATE statment
    $update_sql .= ' WHERE uid=:uid;';

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

  public function setFeeds(MySQLFeeds $feeds) {
    static $feeds_sql = 
      'INSERT INTO favorites (uid, sid, priority)
       VALUES (:uid, :sid, :priority)';
  }

  public function getFeeds() {
    static $feeds_sql = 'SELECT sid FROM favorites WHERE uid=:uid;';
    $feeds_stmt = $this->db->pdo->prepare($feeds_sql);
    $feeds_stmt->bindParam(':uid', $this->uid);
    $feeds_stmt->execute();
    $feeds_stmt->setFetchMode(PDO::FETCH_CLASS, 'MySQLFeed', 
			      array('db'=>$this->db));
    $feeds_result = $feeds_stmt->fetchAll();
    if ($feeds_result !== FALSE && count($feeds_result) > 0)
      return new MySQLFeeds($feeds_result, $this->db);
    else
      return NULL;
  }

/* MySQLUser::get implements iUser::get (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $userattr.
*/
  public function get($userattrs) {
    static $carrier_attr = 'carrier';
    static $carrier_sql = '(SELECT carrior_name FROM carriors WHERE
                           cid=(SELECT cid FROM users WHERE uid=:uid2))';
    static $feeds_attr = 'feeds';

    $sql_added = FALSE;

    // build SQL query to use to get user attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($userattrs as $key=>$attr) {
      if (isset(self::$userattrs_to_cols[$attr])) {
	$get_sql .= self::$userattrs_to_cols[$attr]." AS $attr, ";
	$sql_added = TRUE;
      } elseif ($attr === $carrier_attr) {
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

    // get feeds if requested
    if (in_array($feeds_attr, $userattrs))
      $get_result[$feeds_attr] = $this->getFeeds();

    return $get_result;
  }

/* MySQLUser::create implements iUser::create (see corresponding 
   documentation)
*/
  public static function create($userinfo, $db = NULL) {
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

    // XXX check that using PDO::lastInsertId is not a race
    $c = __CLASS__;
    $user = new $c($db);
    $user->uid = $db->pdo->lastInsertId();
    return $user;
  }

/* MySQLUser::delete implements iUser::delete (see corresponding 
   documentation)
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

/* class MySQLTest contains functions used for unit testing on MySQLObject
   derived classes
*/
class MySQLTest {
  public static function testAll(MySQLDB $db = NULL) {
    self::testUser($db);
    self::testUsers($db);
  }

  public static function testUser(MySQLDB $db = NULL) {
    static $userinfo = array('username'=>'testuser',
			     'password'=>'testpassword',
			     'email'=>'testemail',
			     'phone_number'=>'testphone',
			     'carrier'=>'AT&T');
    static $userinfo_2 = array('username'=>'testuser2',
			       'password'=>'testpassword2',
			       'email'=>'testemail2',
			       'phone_number'=>'testphone2',
			       'carrier'=>'Verizon');

    // MySQLUser::create test
    $user = MySQLUser::create($userinfo, $db);
    if ($user === NULL)
      throw new Exception('MySQLUser::create test failed');

    // MySQLUser::find test
    $find_user = MySQLUser::find('username', $userinfo['username'], $db);
    if ($find_user === NULL)
      throw new Exception('MySQLUser::find test failed');

    // MySQLUser::delete test
    $user->delete();
    $deleted_user = MySQLUser::find('username', $userinfo['username'], $db);
    if ($deleted_user !== NULL)
      throw new Exception('MySQLUser::delete test failed');

    // MySQLUser::get test
    $user = MySQLUser::create($userinfo, $db);
    $get_userinfo = $user->get(array_keys($userinfo));
    if ($get_userinfo != $userinfo)
      throw new Exception('MySQLUser::get test failed');

    // MySQLUser::get no-carrier-as-attr test
    $userinfo_nocarrier = $userinfo;
    unset($userinfo_nocarrier['carrier']);
    $get_userinfo_nocarrier = $user->get(array_keys($userinfo_nocarrier));
    if ($get_userinfo_nocarrier != $userinfo_nocarrier)
      throw new Exception('MySQLUser::get no-carrier-as-attr test failed');

    // MySQLUser::set test
    $user->set($userinfo_2);
    $set_userinfo = $user->get(array_keys($userinfo_2));
    if ($set_userinfo != $userinfo_2)
      throw new Exception('MySQLUser::set test failed');

    // MySQLUser::set no-carrier-as-attr test
    $userinfo_2_nocarrier = $userinfo_2;
    unset($userinfo_2_nocarrier['carrier']);
    $get_userinfo_2_nocarrier = $user->get(array_keys($userinfo_2_nocarrier));
    if ($get_userinfo_2_nocarrier != $userinfo_2_nocarrier)
      throw new Exception('MySQLUser::set no-carrier-as-attr test failed');

    // MySQLUser::__get test
    $get_username = $user->get(array('username'));
    if ($user->username !== $get_username['username'])
      throw new Exception('MySQLUser::__get test failed');

    // MySQLUser::__set test
    $user->username = 'newusername';
    if ($user->username !== 'newusername')
      throw new Exception('MySQLUser::__set test failed');

    $user->delete();
  }

  public static function testUsers(MySQLDB $db = NULL) {
    static $userinfo = array('username'=>'testuser',
			     'password'=>'testpassword',
			     'email'=>'testemail',
			     'phone_number'=>'testphone',
			     'carrier'=>'AT&T');
    static $userinfo_2 = array('username'=>'testuser2',
			       'password'=>'testpassword',
			       'email'=>'testemail2',
			       'phone_number'=>'testphone2',
			       'carrier'=>'Verizon');

    // MySQLUsers::searchAll test
    $user = MySQLUser::create($userinfo, $db);
    $user_2 = MySQLUser::create($userinfo_2, $db);
    $search_users = 
      MySQLUsers::searchAll(array_intersect($userinfo, $userinfo_2), $db);
    if ($search_users === NULL || count($search_users->users) !== 2)
      throw new Exception('MySQLUsers::searchAll test failed');

    // MySQLUsers::searchAny username test
    $search_users = 
      MySQLUsers::searchAny(array('username'=>
				   array($userinfo['username'],
					 $userinfo_2['username'])), $db);
    if ($search_users === NULL || count($search_users->users) !== 2)
      throw new Exception('MySQLUsers::searchAny username test failed');

    // MySQLUsers::searchAll match-one test
    $search_users = 
      MySQLUsers::searchAll(array_diff($userinfo, $userinfo_2), $db);
    if ($search_users === NULL || count($search_users->users) !== 1)
      throw new Exception('MySQLUsers::searchAll match-one test failed');

    // MySQLUsers::searchAny match-one test
    $search_users_2 = 
      MySQLUsers::searchAny(array('username'=>$userinfo_2['username']), $db);
    if ($search_users_2 === NULL || count($search_users_2->users) !== 1)
      throw new Exception('MySQLUsers::searchAny username match-one test '.
			  'failed');

    // MySQLUsers::merge test
    $merge_users = $search_users->merge($search_users_2);
    if ($merge_users === NULL || count($merge_users->users) !== 2)
      throw new Exception('MySQLUsers::merge test failed');
  }
}

//MySQLTest::testAll();
