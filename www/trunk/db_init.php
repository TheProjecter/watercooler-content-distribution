<?php

$db_file = 'test/watercooler.db';
$db_sql = 'SQLiteDB.sql';
$sqlite3_prog = 'sqlite3';

if (!file_exists($db_file))
  exec("$sqlite3_prog -init $db_sql $db_file");

$db = SQLiteDB::connect(array('filename'=>$db_file));
if (!($db instanceof SQLiteDB))
  throw new Exception('SQLiteDB::connect failed');
