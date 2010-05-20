<?php

/* class MySQLDatabaseObject provides a base class for all classes which
   represent objects in a MySQL database
*/
class MySQLDatabaseObject extends DatabaseObject {
  /* $pdo is defined here so sibling classes to MySQLDatabase can access its
     PDO and thus perform low-level operations on a given MySQL database */
  protected $pdo;
}

/* class MySQLDatabase implements iDatabase on MySQL databases (see
   corresponding documentation)
*/
class MySQLDatabase extends MySQLDatabaseObject implements iDatabase {
/* string MySQLDatabase::cfg_ini_main_section is the name of the section in the
   ini config files passed to MySQLDatabase::connectFromIni containing the main
   connection parameters. This is also the base prefix for the opts ini section
   which is named MySQLDatabase::cfg_file_main_section.' opts'.
*/
  const cfg_ini_main_section = __CLASS__;

  /* $dsn_cfg_vars is an array of the the connection variables from which the
     dsn (the first argument to the PDO constructor) should be constructed */
  private static $dsn_cfg_vars = array('host', 'port', 'dbname');

/* function MySQLDatabase::__construct is the constructor for the class

   $pdo: (PDO object) a valid PDO object connected to the MySQL database to use
*/
  private function __construct(PDO $pdo) {
    $this->pdo = $pdo;
  }

/* function MySQLDatabase::setAsSiteDefault implements 
   iDatabase::setAsSiteDefault (see corresponding documentation)
*/
  public function setAsSiteDefault() {
    self::$site_db = $this;
  }

/* function MySQLDatabase::connect implements iDatabase::connect (see 
   corresponding documentation)

   $cfg_vars: (array) the configuration variables for the database connection,
              encoded in the following key-value pairs:
	      'username': (string) the username to use to connect to the MySQL
	        server
	      'password': (string) the password to use to connect to the MySQL
	        server
	      'host': (string) the hostname or ip address of the mysql server,
	        or NULL to use the PHP default
	      'port': (integer) the port on which to connect, or NULL to use
	        the PHP default
	      'dbname': (string) the name of the database to use on the MySQL
	        server (required)
	      'opts': (array) an associative array of PDO connection options,
	        or NULL for PHP defaults (see PHP Manual documentation for PDO
		and the PDO MySQL driver)

   returns a MySQLDatabase object connected to the database
*/
  public static function connect(array $cfg_vars) {
    if (!isset($cfg_vars['dbname']))
	throw InvalidArgumentException('dbname is a required key-value pair '.
				       'in parameter $cfg_vars');

    // construct dsn string
    $dsn = 'mysql:';
    foreach (self::$dsn_cfg_vars as $varname)
      if (isset($cfg_vars[$varname]))
	$dsn .= "$varname={$cfg_vars[$varname]};";

    // create PDO object
    $pdo = new PDO($dsn, $cfg_vars['username'], $cfg_vars['password'], 
		   $cfg_vars['opts']);

    $c = __CLASS__;
    $db = new $c($pdo);

    return $db;
  }

/* function MySQLDatabase::connectFromIni implements iDatabase::connectFromIni
   (see corresponding documentation)

   $cfg_file: (string) the ini file to read connection configuration variables
              from, encoded in the var-value pairs listed in the documentation
	      for MySQLDatabase::connect, split into the following sections:
	      Section 'MySQLDatabase' contains
	        'username', 'password', 'host', 'port', 'dbname'
	      Section 'MySQLDatabase opts' contains
	        the PDO connection options, encoded in var-value pairs with the
		variable names being names of PDO constants, and the values
		being the desired corresponding values for the options. Note
		that values MUST be single quoted to avoid values from being
		interpreted by PHP (see PHP Manual documentation for PDO and
		the PDO MySQL driver for PDO connection options).

   returns a MySQLDatabase object connected to the database
*/
  public static function connectFromIni($cfg_file) {
    $cfg = parse_ini_file($cfg_file, TRUE);
    if ($cfg === FALSE) {
      $e = error_get_last();
      throw new ErrorException($e['message'], 0, $e['type'], 
			       $e['file'], $e['line']);
    }

    $cfg_vars = $cfg[self::cfg_file_main_section];

    // parse PDO options
    if (isset($cfg[self::cfg_file_main_section.' opts']))
      foreach ($cfg[self::cfg_file_main_section.' opts'] as $key=>$value)
	$cfg_vars['opts'][constant($key)] = $value;

    return self::connect($cfg_vars);
  }
}

// XXX implement and document this
class MySQLUsers extends MySQLDatabaseObject implements iUsers {
  public static function searchAll($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAll($attr, $value, $db);
  }
  private static function __searchAll($userinfo, MySQLDatabase $db) {}
  public static function searchAny($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__searchAny($attr, $value, $db);
  }
  private static function __searchAny($userinfo, MySQLDatabase $db) {}
  public function merge($users) {}
}

// XXX implement and document this
class MySQLUser extends MySQLDatabaseObject implements iUser {
  public static function find($attr, $value, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__find($attr, $value, $db);
  }
  private static function __find($attr, $value, MySQLDatabase $db) {}
  public function set($userinfo) {}
  public function get($userattrs) {}
  public function validatePassword($password) {}
  public static function create($userinfo, $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($attr, $value, $db);
  }
  private static function __create($userinfo, MySQLDatabase $db) {}
  public function delete() {}
}
