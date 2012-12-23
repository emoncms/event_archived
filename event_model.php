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

/*
 db_query("INSERT INTO table (`field`) VALUES ('$value')");
 db_query("SELECT * FROM table WHERE `field` = '$value'");
 db_query(UPDATE table SET field = '$value' WHERE `field` = '$value');

*/

function event_set_lasttime($userid,$id,$time)
{
  db_query("UPDATE event SET `lasttime` = '$time' WHERE `userid` = '$userid' AND `id` = '$id' ");
}

function event_add($userid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setvalue,$callcurl,$mutetime)
{
  db_query("INSERT INTO event (`userid`,`eventfeed`, `eventtype`, `eventvalue`, `action`, `setfeed`, `setvalue`, `lasttime`, `callcurl`, `mutetime`) VALUES ('$userid','$eventfeed','$eventtype','$eventvalue','$action','$setfeed','$setvalue','0','$callcurl','$mutetime')");
}

function event_delete($userid,$id)
{
 db_query("DELETE FROM event WHERE `userid` = '$userid' AND `id` = '$id'");
}

function event_list($userid)
{
  $list = array();
  $result = db_query("SELECT * FROM event WHERE `userid` = '$userid'");
  while ($row = db_fetch_array($result))
  {
    $list[] = $row;
  }
  return $list;
}
