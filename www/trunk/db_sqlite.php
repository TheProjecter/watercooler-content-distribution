<?php

// for debugging
//include('db.php');

/* class SQLiteDBObject provides a base class for all classes which
   represent objects in a SQLite database
*/
class SQLiteDBObject extends DatabaseObject {
  /* $pdo is defined here so sibling classes to SQLiteDB can access its
     PDO and thus perform low-level operations on a given SQLite database */
  protected $pdo;

  /* $userattrs_to_cols is an associative array mapping attribute names given 
     as a parameter to methods in classes derived from this one to column
     names in the SQLite database. Note that the following attributes are 
     missing and require special handling
       'carrier': column 'cid' needs to be looked up in 'carriors' table by
         'carrior_name' and entered in user table under column 'cid' */
  protected static $userattrs_to_cols = 
    array('username'=>'username',
	  'email'=>'email',
	  'password'=>'password',
	  'phone_number'=>'phone_number');
}

/* class SQLiteDB implements iDatabase on SQLite databases (see
   corresponding documentation)
*/
class SQLiteDB extends SQLiteDBObject implements iDatabase {
/* string SQLiteDB::cfg_ini_main_section is the name of the section in 
   the ini config files passed to SQLiteDB::connectFromIni containing the
   main connection parameters. This is also the base prefix for the opts ini 
   section which is named SQLiteDB::cfg_ini_main_section.' opts'.
*/
  const cfg_ini_main_section = __CLASS__;

/* function SQLiteDB::__construct is the constructor for the class

   $pdo: (PDO object) a valid PDO object connected to the SQLite database to 
         use
*/
  private function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

/* function SQLiteDB::setAsSiteDefault implements 
   iDatabase::setAsSiteDefault (see corresponding documentation)
*/
  public function setAsSiteDefault() {
    self::$site_db = $this;
  }

/* function SQLiteDB::connect implements iDatabase::connect (see 
   corresponding documentation)

   $cfg_vars: (array) the configuration variables for the database connection,
              encoded in the following key-value pairs:
	      'filename': (string) the absolute path to the file which contains
	        the SQLite database (required)
	      'opts': (array) an associative array of PDO connection options,
	        or NULL for PHP defaults (see PHP Manual documentation for PDO
		and the PDO SQLite driver)

   returns a SQLiteDB object connected to the database
*/
  public static function connect(array $cfg_vars) {
    if (!isset($cfg_vars['filename']))
	throw new InvalidArgumentException('filename is a required key-value '.
					   'pair in parameter $cfg_vars');

    // construct dsn string
    $dsn = 'sqlite:'.$cfg_vars['filename'];

    // create PDO object
    $pdo = new PDO($dsn, NULL, NULL, $cfg_vars['opts']);

    // set PDO error mode so that we get exceptions instead of PHP errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $c = __CLASS__;
    $db = new $c($pdo);

    return $db;
  }

/* function SQLiteDB::connectFromIni implements iDatabase::connectFromIni
   (see corresponding documentation)

   $cfg_file: (string) the ini file to read connection configuration variables
              from, encoded in the var-value pairs listed in the documentation
	      for SQLiteDB::connect, split into the following sections:
	      Section 'SQLiteDB' contains
	        'username', 'password', 'host', 'port', 'dbname'
	      Section 'SQLiteDB opts' contains
	        the PDO connection options, encoded in var-value pairs with the
		variable names being names of PDO constants, and the values
		being the desired corresponding values for the options. Note
		that values MUST be single quoted to avoid values from being
		interpreted by PHP (see PHP Manual documentation for PDO and
		the PDO SQLite driver for PDO connection options).

   returns a SQLiteDB object connected to the database
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

/* class SQLiteUsers implements iUsers on SQLite databases (see corresponding 
   documentation)
*/
class SQLiteUsers extends SQLiteDBObject implements iUsers {
  private $db;
  public $users;

  private function __construct(array $users, SQLiteDB $db) {
    $this->users = $users;
    $this->db = $db;
  }

/* SQLiteUsers::searchAll implements iUsers::searchAll (see corresponding
   documentation). This function is safe to SQL injection.
*/
  public static function searchAll($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAll($userinfo, $db);
  }
  /* SQLiteUsers::__searchAll is a helper function to SQLiteUsers::searchAll 
     which performs the actual search operation. This function was added in
     order to use typehinting on parameter $db.
  */
  private static function __searchAll($userinfo, SQLiteDB $db) {
    static $carrier_attr = 'carrier';
    static $carrier_sql = 
      'cid=(SELECT cid FROM carriors WHERE carrior_name=:carrior_name) AND ';
    // build SQL query to use to search for users
    $search_sql = 'SELECT uid FROM users WHERE ';
    foreach ($userinfo as $attr=>$value) {
      if (isset(self::$userattrs_to_cols[$attr]))
	$search_sql .= 
	  self::$userattrs_to_cols[$attr].'=:'.
	  self::$userattrs_to_cols[$attr].' AND ';
      elseif($attr === $carrier_attr)
	$search_sql .= $carrier_sql;
    }
    // remove trailing AND and spaces
    $search_sql = substr($search_sql, 0, -5);
    // add rest of SQL query
    $search_sql .= ';';

    // prepare SQL statement
    $search_stmt = $db->pdo->prepare($search_sql);

    // bind column values
    foreach ($userinfo as $attr=>$value) {
      if (isset(self::$userattrs_to_cols[$attr]))
	$search_stmt->bindValue(':'.self::$userattrs_to_cols[$attr], $value);
      elseif ($attr === $carrier_attr)
	$search_stmt->bindValue(':carrior_name', $value);
    }

    $search_stmt->execute();
    // set fetch mode to create instances of SQLiteUser
    $search_stmt->setFetchMode(PDO::FETCH_CLASS, 'SQLiteUser', array($db));

    // fetch the result and create a new instance of this class
    $search_result = $search_stmt->fetchAll();
    if ($search_result !== FALSE) {
      $c = __CLASS__;
      return new $c($search_result, $db);
    } else
      return NULL;
  }

  public static function searchAny($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAny($attr, $value, $db);
  }
  private static function __searchAny($userinfo, SQLiteDB $db) {}
  public function merge($users) {}
}

/* class SQLiteUser implements iUser on SQLite databases (see corresponding 
   documentation)
*/
class SQLiteUser extends SQLiteDBObject implements iUser {
  /* $db is the database which contains this user */
  private $db;
  /* $uid is the unique user identifier which is used to access user 
     information in the database */
  private $uid;

  /* function SQLiteDB::__construct is the constructor for the class

     $db: (SQLiteDB object) a valid SQLiteDB object connected to the SQLite
          database to use
  */
  private function __construct(SQLiteDB $db) {
    $this->db = $db;
  }

  /* parseUserInfo transforms a $userinfo array, in the format taken by many
     iUser functions, into an associative array with keys as database column
     names
  */
  private static function parseUserInfo($userinfo, SQLiteDB $db) {
    static $userinfo_to_cols = 
      array('username'=>'username', 'password'=>'password', 'email'=>'email', 
	    'phone_number'=>'phone_number');

    // rename the userinfo keys as database column names
    foreach ($userinfo as $key=>$value)
      if ($userinfo_to_cols[$key] !== NULL)
	$db_userinfo[$userinfo_to_cols[$key]] = $value;

    // XXX fake unused database fields for now
    $db_userinfo['status'] = 666;

    return $db_userinfo;
  }

/* SQLiteUser::find implements iUser::find (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $attr.
*/
  public static function find($attr, $value, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__find($attr, $value, $db);
  }

  /* SQLiteUser::__find is a helper function to SQLiteUser::find which performs
     the actual find operation. This function was added in order to use
     typehinting on parameter $db.
  */
  private static function __find($attr, $value, SQLiteDB $db) {
    $find_sql = "SELECT uid FROM users WHERE $attr=:value;";
    $find_stmt = $db->pdo->prepare($find_sql);
    $find_stmt->bindParam(':value', $value);
    $find_stmt->execute();
    // set fetch mode to create an instance of this class
    $find_stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array('db'=>$db));
    $find_result = $find_stmt->fetch();
    return $find_result !== FALSE ? $find_result : NULL;
  }

/* SQLiteUser::set implements iUser::set (see corresponding documentation).
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

/* SQLiteUser::get implements iUser::get (see corresponding documentation).
   This function IS vulnerable to SQL injection in parameter $userattr.
*/
  public function get($userattrs) {
    static $carrier_attr = 'carrier';
    static $carrier_sql = '(SELECT carrior_name FROM carriors WHERE
                           cid=(SELECT cid FROM users WHERE uid=:uid2))';

    // build SQL query to use to get user attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($userattrs as $key=>$attr) {
      if (isset(self::$userattrs_to_cols[$attr]))
	$get_sql .= self::$userattrs_to_cols[$attr]." AS $attr, ";
      elseif ($attr === $carrier_attr)
	$get_sql .= "$carrier_sql AS $attr, ";
    }
    // remove trailing comma and space
    $get_sql = substr($get_sql, 0, -2);
    // add rest of SQL query
    $get_sql .= ' FROM users WHERE uid=:uid;';

    $get_stmt = $this->db->pdo->prepare($get_sql);
    $get_stmt->bindParam(':uid', $this->uid);

    // bind carrier specific column values
    if (in_array('carrier', $userattrs))
      $get_stmt->bindParam(':uid2', $this->uid);
    
    $get_stmt->execute();
    $get_result = $get_stmt->fetch(PDO::FETCH_ASSOC);
    if ($get_result === FALSE)
      throw new Exception('PDOStatement::fetch failed');
    return $get_result;
  }

/* SQLiteUser::create implements iUser::create (see corresponding 
   documentation)
*/
  public static function create($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($userinfo, $db);
  }

  /* SQLiteUser::__create is a helper function to SQLiteUser::create which
     performs the actual create operation. This function was added in order to 
     use typehinting on parameter $db.
  */
  private static function __create($userinfo, SQLiteDB $db) {
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

    // XXX there is probably a better way to do this
    return SQLiteUser::find('username', $userinfo['username'], $db);
  }

/* SQLiteUser::delete implements iUser::delete (see corresponding 
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

    /* unset $this->uid so that future operations on this SQLiteUser object
       will fail */
    unset ($this->uid);
  }
}

/* class SQLiteTest contains functions used for unit testing on SQLiteObject
   derived classes
*/
class SQLiteTest {
  public static function testAll() {
    $db = self::testDB();
    self::testUser($db);
    self::testUsers($db);
  }

/* function SQLiteTest::testDB tests the semantics of operations in class 
   SQLiteDB. These tests require a file called 'SQLiteDB.sql' containing SQL
   which initializes a valid watercooler SQLite database and an empty writable
   directory called 'test'.

   returns an SQLiteDB object connected to a test database
*/
  public static function testDB() {
    static $db_file = 'test/SQLiteTest.db';
    static $db_sql = 'SQLiteDB.sql';
    static $sqlite3_prog = 'sqlite3';
    static $ini_file = 'test/db_def_cfg.ini';
    static $ini_contents = "\
[SQLiteDB]
filename=test/SQLiteTest.db
";

    // create test SQLite database
    unlink($db_file);
    exec("$sqlite3_prog -init $db_sql $db_file");

    // SQLiteDB::connect test
    $db = SQLiteDB::connect(array('filename'=>$db_file));
    if (!($db instanceof SQLiteDB))
      throw new Exception('SQLiteDB::connect test failed');

    // SQLiteDB::connectFromIni test
    file_put_contents($ini_file, $ini_contents);
    $db = SQLiteDB::connectFromIni('test/db_def_cfg.ini');
    if (!($db instanceof SQLiteDB))
      throw new Exception('SQLiteDB::connect test failed');

    return $db;
  }

  public static function testUser(SQLiteDB $db) {
    static $userinfo = array('username'=>'testuser',
			     'password'=>'testpassword',
			     'email'=>'testemail',
			     'phone_number'=>'testphone',
			     'carrier'=>'testcarrier');
    static $userinfo_2 = array('username'=>'testuser2',
			       'password'=>'testpassword2',
			       'email'=>'testemail2',
			       'phone_number'=>'testphone2',
			       'carrier'=>'testcarrier2');

    // SQLiteUser::create test
    $user = SQLiteUser::create($userinfo, $db);
    if ($user === NULL)
      throw new Exception('SQLiteUser::create test failed');

    // SQLiteUser::find test
    $find_user = SQLiteUser::find('username', $userinfo['username'], $db);
    if ($find_user === NULL)
      throw new Exception('SQLiteUser::find test failed');

    // SQLiteUser::delete test
    $user->delete();
    $deleted_user = SQLiteUser::find('username', $userinfo['username'], $db);
    if ($deleted_user !== NULL)
      throw new Exception('SQLiteUser::delete test failed');

    // SQLiteUser::get test
    $user = SQLiteUser::create($userinfo, $db);
    $get_userinfo = $user->get(array_keys($userinfo));
    if ($get_userinfo != $userinfo)
      throw new Exception('SQLiteUser::get test failed');

    // SQLiteUser::get no-carrier-as-attr test
    $userinfo_nocarrier = $userinfo;
    unset($userinfo_nocarrier['carrier']);
    $get_userinfo_nocarrier = $user->get(array_keys($userinfo_nocarrier));
    if ($get_userinfo_nocarrier != $userinfo_nocarrier)
      throw new Exception('SQLiteUser::get no-carrier-as-attr test failed');

    // SQLiteUser::set test
    $user->set($userinfo_2);
    $set_userinfo = $user->get(array_keys($userinfo_2));
    if ($set_userinfo != $userinfo_2)
      throw new Exception('SQLiteUser::set test failed');

    // SQLiteUser::set no-carrier-as-attr test
    $userinfo_2_nocarrier = $userinfo_2;
    unset($userinfo_2_nocarrier['carrier']);
    $get_userinfo_2_nocarrier = $user->get(array_keys($userinfo_2_nocarrier));
    if ($get_userinfo_2_nocarrier != $userinfo_2_nocarrier)
      throw new Exception('SQLiteUser::set no-carrier-as-attr test failed');

    $user->delete();
  }

  public static function testUsers(SQLiteDB $db) {
    static $userinfo = array('username'=>'testuser',
			     'password'=>'testpassword',
			     'email'=>'testemail',
			     'phone_number'=>'testphone',
			     'carrier'=>'testcarrier');
    static $userinfo_2 = array('username'=>'testuser2',
			       'password'=>'testpassword',
			       'email'=>'testemail2',
			       'phone_number'=>'testphone2',
			       'carrier'=>'testcarrier');

    // SQLiteUser::find test
    $user = SQLiteUser::create($userinfo, $db);
    $user_2 = SQLiteUser::create($userinfo_2, $db);
    $search_users = 
      SQLiteUsers::searchAll(array_intersect($userinfo, $userinfo_2), $db);
    var_dump($search_users);
    if ($search_users === NULL)
      throw new Exception('SQLiteUser::search test failed');
  }
}

//SQLiteTest::testAll();
