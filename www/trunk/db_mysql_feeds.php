<?php
require_once('db.php');
require_once('db_mysql.php');

/* class MySQLFeeds implements iFeeds on MySQL databases (see corresponding 
   documentation)
*/
class MySQLFeeds extends MySQLDBObject implements iFeeds {
  private $db;
  public $feeds;

  public function __construct(array $feeds, MySQLDB $db) {
    $this->feeds = $feeds;
    $this->db = $db;
  }

/* MySQLFeeds::create implements iFeeds::create (see corresponding 
   documentation)
*/
  public static function create(array $feedinfos, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($feedinfos, $db);
  }

  /* MySQLFeeds::__create is a helper function to MySQLFeeds::create which
     performs the actual create operation. This function was added in order to 
     use typehinting on parameter $db.
  */
  private static function __create(array $feedinfos, MySQLDB $db) {
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

/* class MySQLFeed implements iFeed on MySQL databases (see corresponding 
   documentation)
*/
class MySQLFeed extends MySQLDBObject implements iFeed {
  private $db;
  /* $sid is the unique feed identifier which is used to access feed
     information in the database */
  public $sid;

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
  private static function parseFeedInfo(array $feedinfo, MySQLDB $db) {
    static $feedinfo_to_cols = 
      array('name'=>'source_name', 'url'=>'source_url');

    // rename the feedinfo keys as database column names
    foreach ($feedinfo as $key=>$value)
      if ($feedinfo_to_cols[$key] !== NULL)
	$db_feedinfo[$feedinfo_to_cols[$key]] = $value;

    return $db_feedinfo;
  }

/* MySQLFeed::find implements iFeed::find (see corresponding documentation)
*/
  public static function find($attr, $value, iDatabase $db = NULL) {
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


/* MySQLFeed::create implements iFeed::create (see corresponding documentation)
*/
  public static function create(array $feedinfo, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__create($feedinfo, $db);
  }

  /* MySQLFeed::__create is a helper function to MySQLFeed::create which
     performs the actual create operation. This function was added in order to
     use typehinting on parameter $db.
  */
  public static function __create(array $feedinfo, MySQLDB $db) {
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

    return MySQLFeed::find('url', $feedinfo['url'], $db);
  }

/* MySQLFeed::get implements iFeed::get (see corresponding documentation)
*/
  public function get(array $feedattrs) {
    $sql_added = FALSE;

    // build SQL query to use to get feed attributes
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
