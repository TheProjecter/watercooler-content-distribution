<?php
require_once('db_init.php');
require_once('auth.php');

print('<ul>');
foreach($user->feeds as $currentFeed)
  {
    // get feed site favicon
    /*$domain = getDomain($currentFeed->url);
    $icon = "http://";
    $icon .= $domain;
    $icon .= '/favicon.ico';*/
    // set to default favicon
    $icon = 'feed-icon-14x14.png';

    print("<li class=\"feed\"><button type=\"button\" onclick=\"getStories('{$currentFeed->id}')\">");
    print("<img class=\"icon\" src=\"{$icon}\" alt=\"{$domain}\"></img>");
    print("<div class=\"feedName\">{$currentFeed->name}</div></button></li>");
  }
print('</ul>');

function getDomain($url)
{
  $www_stripped = ereg_replace('www\.','',$url);
  $domain = parse_url($www_stripped);
  if(!empty($domain["host"]))
    {
      return $domain["host"];
    }
  else
    {
      return $domain["path"];
    }

}
