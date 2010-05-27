<?php
require_once('db.php');
require_once('db_mysql.php');
require_once('db_mysql_users.php');

/* class MySQLTest contains functions used for unit testing on MySQLObject
   derived classes
*/
class MySQLTest {
    static $userinfo = array('username'=>'MySQLTest_user',
			     'password'=>'MySQLTest_password',
			     'email'=>'MySQLTest_email',
			     'phone_number'=>'8562447598',
			     'carrier'=>'AT&T');
    static $userinfo_2 = array('username'=>'MySQLTest_user2',
			       'password'=>'MySQLTest_password',
			       'email'=>'MySQLTest_email@nothing.nothing',
			       'phone_number'=>'9688472548',
			       'carrier'=>'Verizon');
    static $feedinfo = array('name'=>'MySQLTest_feed',
			     'url'=>'MySQLTest_url');
    static $feedinfo_2 = array('name'=>'MySQLTest_feed2',
			       'url'=>'MySQLTest_url2');

  public static function testAll(MySQLDB $db = NULL) {
    self::testUser($db);
    self::testUsers($db);
    self::testFeed($db);
    self::testFeeds($db);
  }

  public static function testUser(MySQLDB $db = NULL) {
    try {
    // MySQLUser::create test
    $user = MySQLUser::create(self::$userinfo, $db);
    if ($user === NULL)
      throw new Exception('MySQLUser::create test failed');

    // MySQLUser::find test
    $find_user = MySQLUser::find('username', self::$userinfo['username'], $db);
    if ($find_user === NULL)
      throw new Exception('MySQLUser::find test failed');

    // MySQLUser::delete test
    $user->delete();
    unset($user);
    $deleted_user = MySQLUser::find('username', 
				    self::$userinfo['username'], $db);
    if ($deleted_user !== NULL)
      throw new Exception('MySQLUser::delete test failed');

    // MySQLUser::get test
    $user = MySQLUser::create(self::$userinfo, $db);
    $get_userinfo = $user->get(array_keys(self::$userinfo));
    if ($get_userinfo != self::$userinfo)
      throw new Exception('MySQLUser::get test failed');

    // MySQLUser::get no-carrier-as-attr test
    $userinfo_nocarrier = self::$userinfo;
    unset($userinfo_nocarrier['carrier']);
    $get_userinfo_nocarrier = $user->get(array_keys($userinfo_nocarrier));
    if ($get_userinfo_nocarrier != $userinfo_nocarrier)
      throw new Exception('MySQLUser::get no-carrier-as-attr test failed');

    // MySQLUser::set test
    $user->set(self::$userinfo_2);
    $set_userinfo = $user->get(array_keys(self::$userinfo_2));
    if ($set_userinfo != self::$userinfo_2)
      throw new Exception('MySQLUser::set test failed');

    // MySQLUser::set no-carrier-as-attr test
    $userinfo_2_nocarrier = self::$userinfo_2;
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
    unset($user);
    } catch(Exception $e) {
      if (isset($user))
	$user->delete();
      throw $e;
    }
  }

  public static function testUsers(MySQLDB $db = NULL) {
    try {
    // MySQLUsers::searchAll test
    $user = MySQLUser::create(self::$userinfo, $db);
    $user_2 = MySQLUser::create(self::$userinfo_2, $db);
    $search_users = 
      MySQLUsers::searchAll(array_intersect(self::$userinfo, 
					    self::$userinfo_2), $db);
    if ($search_users === NULL || count($search_users->users) !== 2)
      throw new Exception('MySQLUsers::searchAll test failed');

    // MySQLUsers::searchAny username test
    $search_users = 
      MySQLUsers::searchAny(array('username'=>
				   array(self::$userinfo['username'],
					 self::$userinfo_2['username'])), $db);
    if ($search_users === NULL || count($search_users->users) !== 2)
      throw new Exception('MySQLUsers::searchAny username test failed');

    // MySQLUsers::searchAll match-one test
    $search_users = 
      MySQLUsers::searchAll(array_diff(self::$userinfo, 
				       self::$userinfo_2), $db);
    if ($search_users === NULL || count($search_users->users) !== 1)
      throw new Exception('MySQLUsers::searchAll match-one test failed');

    // MySQLUsers::searchAny match-one test
    $search_users_2 = 
      MySQLUsers::searchAny(array('username'=>self::$userinfo_2['username']),
			    $db);
    if ($search_users_2 === NULL || count($search_users_2->users) !== 1)
      throw new Exception('MySQLUsers::searchAny username match-one test '.
			  'failed');

    // MySQLUsers::merge test
    $merge_users = $search_users->merge($search_users_2);
    if ($merge_users === NULL || count($merge_users->users) !== 2)
      throw new Exception('MySQLUsers::merge test failed');

    $user->delete();
    $user_2->delete();
    } catch(Exception $e) {
      if (isset($user))
	$user->delete();
      if (isset($user_2))
	$user_2->delete();
      throw $e;
    }    
  }

  public static function testFeed(MySQLDB $db = NULL) {
    try {
    // MySQLFeed::create test
    $feed = MySQLFeed::create(self::$feedinfo, $db);
    if ($feed === NULL)
      throw new Exception('MySQLFeed::create test failed');

    // MySQLFeed::find test
    $find_feed = MySQLFeed::find('name', self::$feedinfo['name'], $db);
    if ($find_feed === NULL)
      throw new Exception('MySQLFeed::find test failed');

    // MySQLFeed::delete test
    $feed->delete();
    unset($feed);
    $deleted_feed = MySQLFeed::find('name', 
				    self::$feedinfo['name'], $db);
    if ($deleted_feed !== NULL)
      throw new Exception('MySQLFeed::delete test failed');

    // MySQLFeed::get test
    $feed = MySQLFeed::create(self::$feedinfo, $db);
    $get_feedinfo = $feed->get(array_keys(self::$feedinfo));
    if ($get_feedinfo != self::$feedinfo)
      throw new Exception('MySQLFeed::get test failed');

    // MySQLFeed::__get test
    $get_name = $feed->get(array('name'));
    if ($feed->name !== $get_name['name'])
      throw new Exception('MySQLFeed::__get test failed');

    $feed->delete();
    } catch(Exception $e) {
      if (isset($feed))
	$feed->delete();
      throw $e;
    }    
  }

  public static function testFeeds(MySQLDB $db = NULL) {
    try {
      $feedinfos = array(self::$feedinfo, self::$feedinfo_2);

      // MySQLFeeds::create test
      $feeds = MySQLFeeds::create($feedinfos);
      if ($feeds === NULL || count($feeds->feeds) < 2)
	throw new Exception('MySQLFeeds::create failed');

      // MySQLFeeds foreach test
      foreach($feeds as $feed) {
	if (!($feed instanceof MySQLFeed))
	  throw new Exception('MySQLFeeds foreach test failed');
      }

      foreach($feeds as $feed)
	$feed->delete();
    } catch (Exception $e) {
      if (isset($feeds)) {
	foreach($feeds->feeds as $feed)
	  $feed->delete();
      }
      throw $e;
    }
  }
}

//MySQLTest::testAll();
