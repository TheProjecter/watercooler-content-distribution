<?php
require_once('db.php');
require_once('db_mysql.php');
require_once('db_mysql_users.php');
require_once('db_mysql_feeds.php');
require_once('db_mysql_stories.php');

/*
$db_file = 'test/watercooler.db';
$db_sql = 'SQLiteDB.sql';
$sqlite3_prog = 'sqlite3';

if (!file_exists($db_file))
  exec("$sqlite3_prog -init $db_sql $db_file");
*/

$db = MySQLDB::connectFromIni('db_def_cfg.ini');
if (!($db instanceof MySQLDB))
  throw new Exception('MySQLDB::connect failed');
$db->setAsSiteDefault();

class Stories extends MySQLStories {}
class Story extends MySQLStory {}
class Feed extends MySQLFeed {}
class Feeds extends MySQLFeeds {}
class User extends MySQLUser {}
class Users extends MySQLUsers {}
