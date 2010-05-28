<?php
require_once('db.php');
require_once('db_mysql.php');

/* class MySQLStories implements iStories on MySQL databases (see corresponding
   documentation)
*/
class MySQLStories extends MySQLDBObject implements iStories {
  private $db;
  public $stories;

  public function __construct(array $stories, MySQLDB $db) {
    $this->stories = $stories;
    $this->db = $db;
  }

  // these functions implement Iterator
  public function rewind() {
    reset($this->stories);
  }
  public function current() {
    return current($this->stories);
  }
  public function key() {
    return key($this->stories);
  }
  public function next() {
    return next($this->stories);
  }
  public function valid() {
    return ($this->current() !== FALSE);
  }
}

/* class MySQLStory implements iStory on MySQL databases (see corresponding 
   documentation)
*/
class MySQLStory extends MySQLDBObject implements iStory {
  private $db;
  /* $fid is the unique feed story identifier which is used to access feed 
     story information in the database */
  public $fid;

  static $feed_attr = 'feed';
  static $category_attr = 'category';

  /* function MySQLStory::__construct is the constructor for the class

     $db: (MySQLDB object) a valid MySQLDB object connected to the MySQL
          database to use
  */
  public function __construct(MySQLDB $db) {
    $this->db = $db;
  }

  /* function MySQLStory::__get is the PHP magic 'get' function for the 
     class */
  public function __get($name) {
    $ret = $this->get(array($name));
    if ($ret === NULL || !isset($ret[$name]))
      return NULL;
    else
      return $ret[$name];
  }

  /* parseStoryInfo transforms a $storyinfo array, in the format taken by many
     iStory functions, into an associative array with keys as database column
     names
  */
  private static function parseStoryInfo(array $storyinfo, MySQLDB $db) {
    // rename the storyinfo keys as database column names
    foreach ($storyinfo as $key=>$value)
      if (self::$storyattrs_to_cols[$key] !== NULL)
	$db_storyinfo[self::$storyattrs_to_cols[$key]] = $value;

    return $db_storyinfo;
  }

/* MySQLStory::find implements iStory::find (see corresponding documentation)
*/
  public static function find($attr, $value, iDatabase $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    return self::__find($attr, $value, $db);
  }

  /* MySQLStory::__find is a helper function to MySQLStory::find which performs
     the actual find operation. This function was added in order to use
     typehinting on parameter $db.
  */
  private static function __find($attr, $value, MySQLDB $db) {
    // XXX do this in a more general way
    if ($attr !== 'fid')
      throw new InvalidArgumentException('parameter $attr must be a unique '.
					 'feed story attribute');
    
    $db_storyinfo = self::parseStoryInfo(array($attr=>$value), $db);

    $db_attr = key($db_storyinfo);
    $db_value = current($db_storyinfo);

    $find_sql = "SELECT fid FROM feed_stories WHERE $db_attr=:value;";
    $find_stmt = $db->pdo->prepare($find_sql);
    $find_stmt->bindParam(':value', $db_value);
    $find_stmt->execute();
    // set fetch mode to create an instance of this class
    $find_stmt->setFetchMode(PDO::FETCH_CLASS, __CLASS__, array('db'=>$db));
    $find_result = $find_stmt->fetch();
    return $find_result !== FALSE ? $find_result : NULL;
  }

  /* MySQLStory::getFeed is a written as a helper function to MySQLStory::get
     which carries out the operation of getting a story's feed source

     returns a MySQLFeed object representing story's feed source
  */
  private function getFeed() {
    static $feed_sql = 'SELECT sid FROM feed_sources WHERE 
                        sid=(SELECT sid FROM feed_stories WHERE fid=:fid);';
    $feed_stmt = $this->db->pdo->prepare($feed_sql);
    $feed_stmt->bindParam(':fid', $this->fid);
    $feed_stmt->execute();
    $feed_stmt->setFetchMode(PDO::FETCH_CLASS, 'MySQLFeed', 
			     array('db'=>$this->db));
    $feed_result = $feed_stmt->fetch();
    return $feed_result === FALSE ? NULL : $feed_result;
  }

/* MySQLStory::get implements iStory::get (see corresponding documentation)
*/
  public function get(array $storyattrs) {
    static $category_sql = '(SELECT category FROM feed_categories WHERE 
                             gid=(SELECT gid FROM feed_stories 
                                  WHERE fid=:fid3))';

    $sql_added = FALSE;
    $get_result = array();

    // build SQL query to use to get story attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($storyattrs as $key=>$attr) {
      if (isset(self::$storyattrs_to_cols[$attr])) {
	$get_sql .= self::$storyattrs_to_cols[$attr]." AS $attr, ";
	$sql_added = TRUE;
      } else if ($attr === self::$category_attr) {
	$get_sql .= "$category_sql AS $attr, ";
	$sql_added = TRUE;
      }
    }
    // remove trailing comma and space
    $get_sql = substr($get_sql, 0, -2);
    // add rest of SQL query
    $get_sql .= ' FROM feed_stories WHERE fid=:fid;';

    // do not attempt the SELECT if no attrs were added to the select
    if ($sql_added === TRUE) {
      $get_stmt = $this->db->pdo->prepare($get_sql);
      $get_stmt->bindParam(':fid', $this->fid);

      $get_stmt->execute();
      $get_result = $get_stmt->fetch(PDO::FETCH_ASSOC);
      if ($get_result === FALSE)
	throw new Exception('PDOStatement::fetch failed');
    }

    // get feed source if requested
    if (in_array(self::$feed_attr, $storyattrs))
      $get_result[self::$feed_attr] = $this->getFeed();

    return $get_result;
  }
}