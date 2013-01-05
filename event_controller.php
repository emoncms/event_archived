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
    require "Modules/event/event_model.php";
    require "Modules/feed/feed_model.php";
    global $session, $route;

    $format = $route['format'];
    $action = $route['action'];

    $output['content'] = "";
    $output['message'] = "";

    $userid = $session['userid'];

    if ($action == 'add' && $session['write'])
    {
      $eventfeed = intval(get('eventfeed'));
      $eventtype = intval(get('eventtype'));
      $eventvalue = floatval(get('eventvalue'));
      $action = intval(get('action'));
      $setfeed = intval(get('setfeed'));
      $setvalue = floatval(get('setvalue'));
      $callcurl = get('callcurl');
      $mutetime = get('mutetime');
      $priority = get('priority');
      $message = get('message');
      
      event_add($userid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setvalue,$callcurl,$message,$mutetime,$priority);
      $output['message'] = "Event added";
    }

    else if ($action == 'delete' && $session['write'])
    {
      $id = intval(get('id'));
      event_delete($userid,$id);
      $output['message'] = "Event deleted";
    }

    else if ($action == 'settings' && $session['write'])
    {
      $settings = get_event_settings($session['userid']);
      $output['content'] = view("event/event_settings_view.php", array('user'=>$settings));
    }
    
    //--------------------------------------------------------------------------
    // SET TWITTER
    // http://yoursite/emoncms/user/settwitter
    //--------------------------------------------------------------------------
    else if ($action == 'savesettings' && $session['write'])
    {
      // Store userlang in database

      $prowlkey = post('prowlkey');

      $smtpserver = post('smtpserver');
      $smtpuser = post('smtpuser');
      $smtppassword = post('smtppassword');
      $smtpport = preg_replace('/[^\w\s-]/','',post('smtpport'));

      $consumerkey = post('consumerkey');
      $consumersecret = post('consumersecret');
      $usertoken = post('usertoken');
      $usersecret = post('usersecret');

      set_event_settings($session['userid'],$prowlkey,$consumerkey,$consumersecret,$usertoken,$usersecret,$smtpserver,$smtpuser,$smtppassword,$smtpport);

      // Reload the page	  	
      if ($format == 'html')
      {
        header("Location: settings");
      }
    }

    else if ($session['write'])
    {
      $event_list = event_list($userid);
      $feeds = get_user_feed_names($userid);
      $output['content'] = view("event/event_list.php", array('event_list'=>$event_list, 'feeds'=>$feeds));
    }

    return $output;
  }
