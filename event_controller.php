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

      event_add($session['userid'],$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setvalue);
      $output['message'] = "Event added";
    }

    else if ($action == 'delete' && $session['write'])
    {
      $id = intval(get('id'));
      event_delete($session['userid'],$id);
      $output['message'] = "Event deleted";
    }

    else if ($session['write'])
    {
      $event_list = event_list($session['userid']);
      $feeds = get_user_feed_names($session['userid']);
      $output['content'] = view("event/event_list.php", array('event_list'=>$event_list, 'feeds'=>$feeds));
    }

    if ($action == 'run' && $session['write'])
    {
      $event_list = event_list($session['userid']);

      foreach ($event_list as $event)
      {
        $time = strtotime(get_feed_field($event['eventfeed'],'time'));        
        $value = get_feed_field($event['eventfeed'],'value');
        $name = get_feed_field($event['eventfeed'],'name');


        // More than
        if ($event['eventtype']==0)
        {
          if ($value > $event['eventvalue']) 
          {
            // echo "Feed ".$name." value (".$value.") is more than set value (".$event['eventvalue'].") ";

            // Sending email
            if ($event['action']==0)
            {
              event_set_lasttime($session['userid'],$event['id'],time());
              if ((time()-$event['lasttime'])>120) echo "sending email";
            }

            // set feed
            if ($event['action']==1)
            {
              echo "setting feed ".get_feed_field($event['setfeed'],'name')." = ".$event['setvalue'];
              set_feed_field($event['setfeed'],'value',$event['setvalue']);
              set_feed_field($event['setfeed'],'time',date("Y-n-j H:i:s",time()));
            }
            // echo "<br>";
          }
        }

        // Less than
        if ($event['eventtype']==1)
        {
          if ($value < $event['eventvalue']) 
          {
            // echo "Feed ".$name." value (".$value.") is less than set value (".$event['eventvalue'].") ";

            // Sending email
            if ($event['action']==0)
            {
              event_set_lasttime($session['userid'],$event['id'],time());
              if ((time()-$event['lasttime'])>120) echo "sending email";
            }

            if ($event['action']==1)
            {
              echo "setting feed ".get_feed_field($event['setfeed'],'name')." = ".$event['setvalue'];
              set_feed_field($event['setfeed'],'value',$event['setvalue']);
              set_feed_field($event['setfeed'],'time',date("Y-n-j H:i:s",time()));
            }
            // echo "<br>";
          }
        }

        // Equal to
        if ($event['eventtype']==2)
        {
          if ($value == $event['eventvalue']) 
          {
            // echo "Feed ".$name." value (".$value.") is equal to set value (".$event['eventvalue'].") ";

            // Sending email
            if ($event['action']==0)
            {
              event_set_lasttime($session['userid'],$event['id'],time());
              if ((time()-$event['lasttime'])>120) echo "sending email";
            }

            if ($event['action']==1)
            {
              echo "setting feed ".get_feed_field($event['setfeed'],'name')." = ".$event['setvalue'];
              set_feed_field($event['setfeed'],'value',$event['setvalue']);
              set_feed_field($event['setfeed'],'time',date("Y-n-j H:i:s",time()));
            }
            // echo "<br>";
          }
        }

        // Inactive
        if ($event['eventtype']==3)
        {
          if (((time()-$time)/3600)>24) 
          {
            // echo "Feed: ".$name." is inactive: ";

            // Sending email
            if ($event['action']==0)
            {
              event_set_lasttime($session['userid'],$event['id'],time());
              if ((time()-$event['lasttime'])>120) echo "sending email";
            }

            if ($event['action']==1)
            {
              echo "setting feed ".get_feed_field($event['setfeed'],'name')." = ".$event['setvalue'];
              set_feed_field($event['setfeed'],'value',$event['setvalue']);
              set_feed_field($event['setfeed'],'time',date("Y-n-j H:i:s",time()));
            }
            // echo "<br>";
          }
        }

      }
    }


    return $output;
  }

?>
