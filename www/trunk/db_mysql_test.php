require_once('db.php');
require_once('db_mysql.php');
require_once('db_mysql_users.php');

/* class MySQLTest contains functions used for unit testing on MySQLObject
   derived classes
*/
class MySQLTest {
  public static function testAll(MySQLDB $db = NULL) {
    self::testUser($db);
    self::testUsers($db);
  }

  public static function testUser(MySQLDB $db = NULL) {
    static $userinfo = array('username'=>'testuser',
			     'password'=>'testpassword',
			     'email'=>'testemail',
			     'phone_number'=>'testphone',
			     'carrier'=>'AT&T');
    static $userinfo_2 = array('username'=>'testuser2',
			       'password'=>'testpassword2',
			       'email'=>'testemail2',
			       'phone_number'=>'testphone2',
			       'carrier'=>'Verizon');

    // MySQLUser::create test
    $user = MySQLUser::create($userinfo, $db);
    if ($user === NULL)
      throw new Exception('MySQLUser::create test failed');

    // MySQLUser::find test
    $find_user = MySQLUser::find('username', $userinfo['username'], $db);
    if ($find_user === NULL)
      throw new Exception('MySQLUser::find test failed');

    // MySQLUser::delete test
    $user->delete();
    $deleted_user = MySQLUser::find('username', $userinfo['username'], $db);
    if ($deleted_user !== NULL)
      throw new Exception('MySQLUser::delete test failed');

    // MySQLUser::get test
    $user = MySQLUser::create($userinfo, $db);
    $get_userinfo = $user->get(array_keys($userinfo));
    if ($get_userinfo != $userinfo)
      throw new Exception('MySQLUser::get test failed');

    // MySQLUser::get no-carrier-as-attr test
    $userinfo_nocarrier = $userinfo;
    unset($userinfo_nocarrier['carrier']);
    $get_userinfo_nocarrier = $user->get(array_keys($userinfo_nocarrier));
    if ($get_userinfo_nocarrier != $userinfo_nocarrier)
      throw new Exception('MySQLUser::get no-carrier-as-attr test failed');

    // MySQLUser::set test
    $user->set($userinfo_2);
    $set_userinfo = $user->get(array_keys($userinfo_2));
    if ($set_userinfo != $userinfo_2)
      throw new Exception('MySQLUser::set test failed');

    // MySQLUser::set no-carrier-as-attr test
    $userinfo_2_nocarrier = $userinfo_2;
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
  }

  public static function testUsers(MySQLDB $db = NULL) {
    static $userinfo = array('username'=>'testuser',
			     'password'=>'testpassword',
			     'email'=>'testemail',
			     'phone_number'=>'testphone',
			     'carrier'=>'AT&T');
    static $userinfo_2 = array('username'=>'testuser2',
			       'password'=>'testpassword',
			       'email'=>'testemail2',
			       'phone_number'=>'testphone2',
			       'carrier'=>'Verizon');

    // MySQLUsers::searchAll test
    $user = MySQLUser::create($userinfo, $db);
    $user_2 = MySQLUser::create($userinfo_2, $db);
    $search_users = 
      MySQLUsers::searchAll(array_intersect($userinfo, $userinfo_2), $db);
    if ($search_users === NULL || count($search_users->users) !== 2)
      throw new Exception('MySQLUsers::searchAll test failed');

    // MySQLUsers::searchAny username test
    $search_users = 
      MySQLUsers::searchAny(array('username'=>
				   array($userinfo['username'],
					 $userinfo_2['username'])), $db);
    if ($search_users === NULL || count($search_users->users) !== 2)
      throw new Exception('MySQLUsers::searchAny username test failed');

    // MySQLUsers::searchAll match-one test
    $search_users = 
      MySQLUsers::searchAll(array_diff($userinfo, $userinfo_2), $db);
    if ($search_users === NULL || count($search_users->users) !== 1)
      throw new Exception('MySQLUsers::searchAll match-one test failed');

    // MySQLUsers::searchAny match-one test
    $search_users_2 = 
      MySQLUsers::searchAny(array('username'=>$userinfo_2['username']), $db);
    if ($search_users_2 === NULL || count($search_users_2->users) !== 1)
      throw new Exception('MySQLUsers::searchAny username match-one test '.
			  'failed');

    // MySQLUsers::merge test
    $merge_users = $search_users->merge($search_users_2);
    if ($merge_users === NULL || count($merge_users->users) !== 2)
      throw new Exception('MySQLUsers::merge test failed');
  }
}

//MySQLTest::testAll();
