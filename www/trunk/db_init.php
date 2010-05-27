<?php
require_once('db.php');
require_once('db_mysql.php');

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

class Feed extends MySQLFeed {}
class Feeds extends MySQLFeeds {}
class User extends MySQLUser {}
class Users extends MySQLUsers {}
