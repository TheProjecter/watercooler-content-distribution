<?php
require_once('db.php');
require_once('db_mysql.php');

/* class MySQLStories implements iStories on MySQL databases (see corresponding
   documentation)
*/
class MySQLStories extends MySQLDBObject implements iStories {
  private $db;
  public $stories;
  public $sort_sql = '';

  public function __construct(array $stories, MySQLDB $db) {
    $this->stories = $stories;
    $this->db = $db;
  }

/* MySQLStories::get implements iStories::get (see corresponding documentation)
*/
  public function get(array $storyattrs, $sortattr = NULL, 
		      $sortreverse = FALSE) {
    // XXX implement 'category' and 'feed' attributes

    if (count($this->stories) == 0)
      return array();

    if ($sortattr !== NULL)
      $sort_sql = self::get_sort_sql($sortattr, $sortreverse);
    else
      $sort_sql = $this->sort_sql;

    $get_result = array();

    // build SQL query
    $get_sql = 'SELECT ';
    // add column names
    foreach ($storyattrs as $key=>$attr) {
      if (isset(self::$storyattrs_to_cols[$attr])) {
	$get_sql .= self::$storyattrs_to_cols[$attr]." AS $attr, ";
	$sql_added = TRUE;
      }
    }
    // remove trailing comma and space
    $get_sql = substr($get_sql, 0, -2);
    // add rest of SQL query
    $get_sql .= ' FROM feed_stories WHERE fid IN (';
    // add story ids
    for ($i = 0; $i < count($this->stories); $i++)
      $get_sql .= '?, ';
    // remove trailing comma and space
    $get_sql = substr($get_sql, 0, -2);
    // add rest of SQL query
    $get_sql .= ") $sort_sql;";

    if ($sql_added === TRUE) {
      $get_stmt = $this->db->pdo->prepare($get_sql);
      // build an array of story ids
      foreach ($this as $story)
	$ids[] = $story->id;
      $get_stmt->execute($ids);
      $get_result = $get_stmt->fetchAll();
      if ($get_result === FALSE)
	throw new Exception('PDOStatement::fetchAll failed');
    }

    return $get_result;
  }

  private static function get_sort_sql($storyattr, $reverse = FALSE) {
    // $forward_dir is the forward sort direction
    static $forward_dir = 'ASC';
    // $reverse_dir is the reversed sort direction
    static $reverse_dir = 'DESC';
    /* setting an attribute as a key to $storyattr_reversed indicates that it
       should be sorted the reverse direction by default */
    static $storyattr_reversed =
      array('timestamp'=>TRUE);

    // validate parameters
    if (!is_bool($reverse))
      throw new InvalidArgumentException('parameter $reverse must be a '.
					 'boolean');
    if (!isset(self::$storyattrs_to_cols[$storyattr]))
      throw new InvalidArgumentException('parameter $storyattr is not a '.
					 'valid attribute');

    // convert story attribute to database column
    $col = self::$storyattrs_to_cols[$storyattr];

    // calculate which direction to sort
    if ($reverse) {
      if (isset($storyattr_reversed[$storyattr]))
	$dir = $forward_dir;
      else
	$dir = $reverse_dir;
    } else {
      if (isset($storyattr_reversed[$storyattr]))
	$dir = $reverse_dir;
      else
	$dir = $forward_dir;
    }

    return "ORDER BY $col $dir";
  }	    
/* MySQLStories::sort implements iStories::sort (see corresponding 
   documentation)
*/
  public function sort($storyattr, $reverse = FALSE) {
    $this->sort_sql = self::get_sort_sql($storyattr, $reverse);
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
    /* $valid_storyattrs is a list of attributes from $storyattrs which can be
       handled by the simple sql query generator below. Keep this list updated
       with MySQLDBObject::$storyattrs_to_cols.
    */
    static $valid_storyattrs = 
      array('title'=>TRUE, 'content'=>TRUE, 'url'=>TRUE, 'timestamp'=>TRUE);
    static $category_sql = '(SELECT category FROM feed_categories WHERE 
                             gid=(SELECT gid FROM feed_stories 
                                  WHERE fid=:fid3))';

    $sql_added = FALSE;
    $get_result = array();

    // build SQL query to use to get story attributes
    $get_sql = 'SELECT ';
    // add column names
    foreach ($storyattrs as $key=>$attr) {
      if (isset($valid_storyattrs[$attr])) {
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

    // get id if requested
    if (in_array('id', $storyattrs))
      $get_result['id'] = $this->fid;
    if (in_array('fid', $storyattrs))
      $get_result['fid'] = $this->fid;

    // get feed source if requested
    if (in_array(self::$feed_attr, $storyattrs))
      $get_result[self::$feed_attr] = $this->getFeed();

    return $get_result;
  }
}