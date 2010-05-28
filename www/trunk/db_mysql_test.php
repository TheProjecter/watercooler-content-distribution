<?php
require_once('db.php');
require_once('db_mysql.php');
require_once('db_mysql_users.php');
require_once('db_mysql_feeds.php');
require_once('db_mysql_stories.php');

/* class MySQLTest contains functions used for unit testing on MySQLObject
   derived classes
*/
class MySQLTest extends MySQLDBObject {
    static $userinfo = array('username'=>'MySQLTest_user',
			     'password'=>'MySQLTest_password',
			     'email'=>'MySQLTest_email2@nothing.nothing',
			     'phone_number'=>'8562447598',
			     'send_email'=>TRUE,
			     'send_sms_text'=>FALSE,
			     'carrier'=>'AT&T');
    static $userinfo_2 = array('username'=>'MySQLTest_user2',
			       'password'=>'MySQLTest_password',
			       'email'=>'MySQLTest_email@nothing.nothing',
			       'send_email'=>TRUE,
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
    self::testStories($db);
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

    // MySQLUser::create with-feeds test (depends on MySQLFeeds::create)
    $user->delete();
    $feeds = MySQLFeeds::create(array(self::$feedinfo));
    $user = MySQLUser::create(array_merge(self::$userinfo, 
					  array('feeds'=>$feeds)), $db);
    if ($user === NULL)
      throw new Exception('MySQLUser::create with-feeds test failed');

    // MySQLUser::get feeds test
    $get_userinfo = $user->get(array('feeds'));
    if (!($get_userinfo['feeds'] instanceof MySQLFeeds)
	|| count($get_userinfo['feeds']->feeds) != 1)
      throw new Exception('MySQLUser::get feeds test failed');

    // MySQLUser::set feeds test
    $feeds = MySQLFeeds::create(array(self::$feedinfo, self::$feedinfo_2));
    $user->set(array('feeds'=>$feeds));
    $get_userinfo = $user->get(array('feeds'));
    if (!($get_userinfo['feeds'] instanceof MySQLFeeds)
	|| count($get_userinfo['feeds']->feeds) != 2)
      throw new Exception('MySQLUser::set feeds test failed');

    $user->delete();
    foreach ($feeds as $feed)
      $feed->delete();
    unset($user);
    } catch(Exception $e) {
      if (isset($feeds))
	foreach ($feeds as $feed)
	  $feed->delete();
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

  public static function testStories(MySQLDB $db = NULL) {
    if ($db === NULL)
      $db = self::$site_db;
    try {
    // MySQLFeed::get stories test (relies on MySQLFeed::create)
    $feed = MySQLFeed::create(self::$feedinfo, $db);
    $db->pdo->exec("INSERT INTO feed_stories
		    (title, content, url, time_stamp, sid, gid)
		     VALUES ('MySQLTest_title', 'MySQLTest_content',
                             'MySQLTest_storyurl', ".time().",
                             ".$feed->sid.", 1);");
    $db->pdo->exec("INSERT INTO feed_stories
		    (title, content, url, time_stamp, sid, gid)
		     VALUES ('MySQLTest_title2', 'MySQLTest_content2',
                             'MySQLTest_storyurl', ".time().",
                             ".$feed->sid.", 1);");

    $stories = $feed->get(array('stories'), $db);
    if ($stories === NULL)
      throw new Exception('MySQLFeed::get stories test failed');

    // MySQLStories foreach test
    foreach($stories as $story) {
      if (!($story instanceof MySQLStory))
	throw new Exception('MySQLStories foreach test failed');
    }

    foreach ($stories as $story) {
      // MySQLStory::find test
      $find_story = MySQLStory::find('fid', $story->fid, $db);
      if ($find_story === NULL || $find_story->fid != $story->fid)
	throw new Exception('MySQLFeed::find test failed');

      // MySQLStory::get test
      $get_storyinfo = $story->get(array('url'));
      if ($get_storyinfo['url'] != 'MySQLTest_url')
	throw new Exception('MySQLStory::get test failed');

      // MySQLStory::__get test
      $get_name = $story->get(array('content'));
      if ($story->content !== $get_name['content'])
	throw new Exception('MySQLStory::__get test failed');

      $db->pdo->exec("DELETE FROM feed_stories WHERE sid={$feed->sid}");
    }

    $feed->delete();
    } catch(Exception $e) {
      if (isset($feed))
	$feed->delete();
      throw $e;
    }    
  }
}

require_once('db_init.php');
MySQLTest::testAll();
echo 'all tests passed!';