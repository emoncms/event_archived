<?php
  /*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  // no direct access
  defined('EMONCMS_EXEC') or die('Restricted access');

  function event_controller()
  {
    global $mysqli,$redis,$user, $session, $route, $timestore_adminkey;

    global $feed;
    include "Modules/feed/feed_model.php";
    $feed = new Feed($mysqli,$redis,$timestore_adminkey);

    require "Modules/event/event_model.php";
    $event = new Event($mysqli,$redis);

    $userid = $session['userid'];
    if ($route->action == 'add' && $session['write'])
    {
      $eventfeed = intval(get('eventfeed'));
      $eventtype = intval(get('eventtype'));
      $eventvalue = floatval(get('eventvalue'));
      $action = intval(get('action'));
      $setfeed = intval(get('setfeed'));
      $setemail = get('setemail');
      $setvalue = floatval(get('setvalue'));
      $callcurl = get('callcurl');
      $mutetime = get('mutetime');
      $priority = get('priority');
      $message = get('message');

      $event->add($userid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setemail,$setvalue,$callcurl,$message,$mutetime,$priority);
      $result = "Event added";
    }
    if ($route->action == 'edit' && $session['write'])
    {
      $eventid = intval(get('eventid'));
      $eventfeed = intval(get('eventfeed'));
      $eventtype = intval(get('eventtype'));
      $eventvalue = floatval(get('eventvalue'));
      $action = intval(get('action'));
      $setfeed = intval(get('setfeed'));
      $setemail = get('setemail');
      $setvalue = floatval(get('setvalue'));
      $callcurl = get('callcurl');
      $mutetime = get('mutetime');
      $priority = get('priority');
      $message = get('message');

      $event->update($userid,$eventid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setemail,$setvalue,$callcurl,$message,$mutetime,$priority);
      $result = "Event updated";
    }


    else if ($route->action == 'delete' && $session['write'])
    {
      $id = intval(get('id'));
      $event->delete($userid,$id);
      $result = "Event deleted";
    }
    else if ($route->action == 'status' && $session['write'])
    {
      $id = intval(get('id'));
      $status = intval(get('status'));
      $event->set_status($userid,$id,$status);
      $result = "Event deleted";
    }

    else if ($route->action == 'test' && $session['write'])
    {
      $id = intval(get('id'));
      $feedid = intval(get('feedid'));
      $event->test($userid,$id,$feedid);
      $result = "Event Test Sent";
    }

    else if ($route->action == 'settings' && $session['write'])
    {
      $settings = $event->get_settings($session['userid']);
      $result = view("Modules/event/event_settings_view.php", array('settings'=>$settings));
    }

    //--------------------------------------------------------------------------
    // SET TWITTER
    // http://yoursite/emoncms/user/settwitter
    //--------------------------------------------------------------------------
    else if ($route->action == 'savesettings' && $session['write'])
    {
      // Store userlang in database

      $prowlkey = post('prowlkey');
      $nmakey = post('nmakey');

      $smtpserver = post('smtpserver');
      $smtpuser = post('smtpuser');

      $salt = $user->get_salt($session['userid']);
      $smtppassword = trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $salt, post('smtppassword'), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));
      $smtpport = preg_replace('/[^\w\s-]/','',post('smtpport'));

      $consumerkey = post('consumerkey');
      $consumersecret = post('consumersecret');
      $usertoken = post('usertoken');
      $usersecret = post('usersecret');

      $result = $event->set_settings($session['userid'],$prowlkey,$consumerkey,$consumersecret,$usertoken,$usersecret,$smtpserver,$smtpuser,$smtppassword,$smtpport,$nmakey);
    }

    else if ($session['write'])
    {
      $list = $event->eventlist($userid);
      $feeds = $feed->get_user_feeds($userid);
      $result = view("Modules/event/event_list.php", array('event_list'=>$list, 'feeds'=>$feeds));
    }

    return array('content'=>$result);
  }
