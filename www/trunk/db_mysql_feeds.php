<?php
require_once('db.php');
require_once('db_mysql.php');

/* class MySQLFeeds implements iFeeds on MySQL databases (see corresponding 
   documentation)
*/
class MySQLFeeds extends MySQLDBObject implements iFeeds {
  private $db;
  public $feeds;
  private $sort_sql = '';

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
    $feeds = array();
    foreach ($feedinfos as $feedinfo)
      $feeds[] = MySQLFeed::create($feedinfo, $db);
    $c = __CLASS__;
    return new $c($feeds, $db);
  }

/* MySQLFeeds::sortBy implements iFeeds::sortBy (see corresponding 
   documentation)
*/
  public function sortBy($feedattr, $reverse) {
    
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

  static $stories_attr = 'stories';

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
    /* $valid_feedinfo_attrs is a list of attributes from $feedinfo which can
       be handled by a simple column name transformation. Keep this list 
       updated with MySQLDBObject::$feedattrs_to_cols.
    */
    static $valid_feedinfo_attrs = array('name'=>TRUE, 'url'=>TRUE);

    // rename the feedinfo keys as database column names
    foreach ($feedinfo as $key=>$value)
      if ($valid_feedinfo_attrs[$key] !== NULL)
	$db_feedinfo[self::$feedattrs_to_cols[$key]] = $value;

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
    /* $valid_find_feedattrs is a list of attributes from $feedinfo which can
       be used as input to this function. Keep this list updated with
       MySQLDBObject::$feedattrs_to_cols.
    */
    static $valid_find_feedattrs = 
      array('id'=>TRUE, 'sid'=>TRUE, 'name'=>TRUE, 'url'=>TRUE);

    if (isset($valid_find_feedattrs[$attr]))
      $db_attr = self::$feedattrs_to_cols[$attr];
    else
      throw new InvalidArgumentException('parameter $attr is not a valid '.
					 'attribute');

    $find_sql = "SELECT sid FROM feed_sources WHERE $db_attr=:value;";
    $find_stmt = $db->pdo->prepare($find_sql);
    $find_stmt->bindParam(':value', $value);
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

  /* MySQLFeed::getStories is a written as a helper function to MySQLFeed::get
     which carries out the operation of getting a feed's stories

     returns a MySQLStories object representing the set of the feed's stories
  */
  private function getStories() {
    static $stories_sql = 'SELECT fid FROM feed_stories WHERE sid=:sid;';
    $stories_stmt = $this->db->pdo->prepare($stories_sql);
    $stories_stmt->bindParam(':sid', $this->sid);
    $stories_stmt->execute();
    /* XXX creating the objects this way relies on DB consistency (sid is not
       checked to be existent in feed_sources table) */
    $stories_stmt->setFetchMode(PDO::FETCH_CLASS, 'MySQLStory', 
			      array('db'=>$this->db));
    $stories_result = $stories_stmt->fetchAll();
    return new MySQLStories($stories_result, $this->db);
  }

/* MySQLFeed::get implements iFeed::get (see corresponding documentation)
*/
  public function get(array $feedattrs) {
    /* $valid_feedattrs is a list of attributes from $feedattrs which can be
       handled by the simple sql query generator below. Keep this list updated
       with MySQLDBObject::$feedattrs_to_cols.
    */
    static $valid_feedattrs = array('name'=>TRUE, 'url'=>TRUE);

    $sql_added = FALSE;
    $get_result = array();

    // build SQL query to use to get feed attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($feedattrs as $key=>$attr) {
      if (isset($valid_feedattrs[$attr])) {
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

    // get id if requested
    if (in_array('id', $feedattrs))
      $get_result['id'] = $this->sid;
    if (in_array('sid', $feedattrs))
      $get_result['sid'] = $this->sid;

    // get stories if requested
    if (in_array(self::$stories_attr, $feedattrs))
      $get_result[self::$stories_attr] = $this->getStories();

    return $get_result;
  }

/* MySQLFeed::delete implements iFeed::delete (see corresponding documentation)
*/
  public function delete() {
    // build the SQL query to use to delete the feed
    static $delete_sql = 'DELETE FROM feed_sources WHERE sid=:sid;';
    // prepare the SQL statement
    $delete_stmt = $this->db->pdo->prepare($delete_sql);
    // bind column values
    $delete_stmt->bindValue(':sid', $this->sid);
    // execute the SQL statement
    $delete_stmt->execute();

    /* unset $this->uid so that future operations on this MySQLUser object
       will fail */
    unset ($this->uid);
  }

/* MySQLFeed::getUserCount implements iFeed::getUserCount (see corresponding 
   documentation)
*/
  public function getUserCount() {
    static $usercount_sql = 'SELECT COUNT(uid) FROM favorites WHERE sid=:sid;';
    $usercount_stmt = $this->db->pdo->prepare($usercount_sql);
    $usercount_stmt->bindParam(':sid', $this->sid);
    $usercount_stmt->execute();
    return $usercount_stmt->fetchColumn();
  }
}
