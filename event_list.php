<?php
  /*
   All Emoncms code is released under the GNU Affero General Public License.
   See COPYRIGHT.txt and LICENSE.txt.

    ---------------------------------------------------------------------
    Emoncms - open source energy visualisation
    Part of the OpenEnergyMonitor project:
    http://openenergymonitor.org
  */

  //$event_list = array();
  //$event_list[] = array('eventfeed'=>"power", 'eventtype'=>"more than", 'eventvalue'=>"200", 'action'=>"send email", 'setfeed'=>"", 'setvalue'=>"");
  //$event_list[] = array('eventfeed'=>"temperature", 'eventtype'=>"less than", 'eventvalue'=>"1", 'action'=>"set feed", 'setfeed'=>"heater", 'setvalue'=>"1");

?>

<?php global $path, $feed; ?>

<div style="float:right;"><a href="<?php echo $path; ?>event/settings"><?php echo _('Event Settings'); ?></a></div>
<h2><?php echo _('Event'); ?></h2>

<p><?php echo _('Setup actions to occur when a feed goes above, below or is equal to a specified value, or becomes inactive. Send an email or set another feed to a specified value.'); ?></p>

<?php if (!$event_list) { ?>
<div class="alert alert-block">
<h4 class="alert-heading"><?php echo _('No event notifications created'); ?></h4>
<p><?php echo _('To add an event based notification:'); ?></p>
<p><?php echo _('1) Select the feed you wish to be notified about from the drop down menu'); ?></p>
<p><?php echo _('2) Select whether you want to be notifed if the feed goes above, below or equals the value specified, or is inactive.'); ?></p>
<p><?php echo _('3) Enter a value'); ?></p>
</div>
<?php } else { ?>
<table class="table table-hover" style="">

  <?php $i=0; foreach ($event_list as $item) { $i++; ?>
  <tr class="d<?php echo ($i & 1); ?>" >
    <td><i>if</i></td>
    <td><b><?php echo $feed->get_field($item['eventfeed'],'name'); ?></b></td>

    <td>
    <?php
    if ($item['eventtype']==0) echo ">";
    if ($item['eventtype']==1) echo "<";
    if ($item['eventtype']==2) echo "==";
    if ($item['eventtype']==3) echo _("inactive");
    if ($item['eventtype']==4) echo _("updated");
    if ($item['eventtype']==5) echo _("inc by");
    if ($item['eventtype']==6) echo _("dec by");
    if ($item['eventtype']==7) echo _("manual update");
    ?></td>
    <td><?php echo $item['eventvalue']; ?></td>
    <td>
    <?php
    $state = false;
    if ($item['eventtype']==0 && ($feed->get_field($item['eventfeed'],'value')>$item['eventvalue'])) $state = true;
    if ($item['eventtype']==1 && ($feed->get_field($item['eventfeed'],'value')<$item['eventvalue'])) $state = true;
    if ($item['eventtype']==2 && ($feed->get_field($item['eventfeed'],'value')==$item['eventvalue'])) $state = true;
    if ($item['eventtype']==3 && (((time()-strtotime($feed->get_field($item['eventfeed'],'time')))/3600)>24)) $state = true;
    // Perhaps a n/a state?
    //if ($item['eventtype']==4) $state = false;
    if ($state == true) echo '<span class="label label-success" >TRUE</span>'; else echo '<span class="label label-important" >FALSE</span>';
    ?>
    </td>
    <td>
    <?php
    if ($item['action']!= 1)
    {
      echo "<span class='label label-info' >Last true: ".date("Y-n-j H:i:s", $item['lasttime'])."</span>";
    }
    ?>
    </td>

    <td>

    <?php
    if ($item['action']==0) echo _("email");
    if ($item['action']==1) echo _("feed");
    if ($item['action']==2) echo _("curl");
    if ($item['action']==3) echo _("tweet");
    if ($item['action']==4) echo _("prowl");
    if ($item['action']==5) echo _("nma");
    ?></td>

    <td><?php if ($item['action']==1) echo $feed->get_field($item['setfeed'],'name'); ?></td>
    <td><i><?php if ($item['action']==1) echo "="; ?></i></td>
    <td><?php if ($item['action']==1) echo $item['setvalue']; ?></td>

    <td><?php if ($item['action']==2) echo $item['callcurl']; ?></td>
    <td><?php echo $item['message']; ?> </td>
    <td><?php echo $item['mutetime']; ?> <?php echo _('secs'); ?></td>
   <td><div class="editevent btn"
            eventid="<?php echo $item['id']; ?>"
            eventtype="<?php echo $item['eventtype']; ?>"
            eventvalue="<?php echo $item['eventvalue']; ?>"
            action="<?php echo $item['action']; ?>"
            setvalue="<?php echo $item['setvalue']; ?>"
            setemail="<?php echo $item['setemail']; ?>"
            setfeed="<?php echo $item['setfeed']; ?>"
            eventfeed="<?php echo $item['eventfeed']; ?>"
            callcurl="<?php echo $item['callcurl']; ?>"
            message="<?php echo $item['message']; ?>"
            mutetime="<?php echo $item['mutetime']; ?>"

        ><?php echo _('Edit'); ?></div></td>

    <td><div class="deleteevent btn" eventid="<?php echo $item['id']; ?>" ><?php echo _('Delete'); ?></div></td>
    <?php
    if($item['disabled'] != 1){ ?>
    <td><div class="disableevent btn" eventid="<?php echo $item['id']; ?>" feedid="<?php echo $item['eventfeed']; ?>" ><?php echo _('Disable'); ?></div></td>
    <?php
    }else{ ?>
    <td><div class="enableevent btn" eventid="<?php echo $item['id']; ?>" feedid="<?php echo $item['eventfeed']; ?>" ><?php echo _('Enable'); ?></div></td>

    <?php
    } ?>
    <td><div class="testevent btn" eventid="<?php echo $item['id']; ?>" feedid="<?php echo $item['eventfeed']; ?>" ><?php echo _('Test'); ?></div></td>
  </tr>
  <?php } ?>
</table>
<?php } ?>



<form id="eventform" action="event/add" method="get" onsubmit="return false;">
  <div style=" background-color:#eee; margin-bottom:10px; border: 1px solid #ddd">
    <div style="padding:10px;  border-top: 1px solid #fff; ">
      <div style="float:left; padding-top:2px; font-weight:bold;"><?php echo _('IF'); ?></div>

      <div style="float:right;">
      <select id="eventfeed" name="eventfeed" style="width:160px; margin:0px;">
      <?php foreach ($feeds as $feed){ ?>
      <option value="<?php echo $feed['id']; ?>"><?php echo $feed['name']; ?></option>
      <?php } ?>
      </select>
      <span style="font-weight:bold;" ><?php echo _('is'); ?></span>
      <select id="eventtype" name="eventtype" style="width:100px; margin:0px;">
          <option value="0" ><?php echo _('more than'); ?></option>
          <option value="5" ><?php echo _('increases by'); ?></option>
          <option value="1" ><?php echo _('less than'); ?></option>
          <option value="6" ><?php echo _('reduces by'); ?></option>
          <option value="2" ><?php echo _('equal to'); ?></option>
          <option value="3" ><?php echo _('inactive'); ?></option>
          <option value="4" ><?php echo _('is updated'); ?></option>
          <option value="7" ><?php echo _('manual update'); ?></option>
      </select>
      <input id="eventid" name="eventid" type="text" hidden="true" style="display:none" />
      <span id="not-inactive">
          <input id="eventvalue" name="eventvalue" type="text" style="width:60px; margin:0px;" />
      </span>

      <span style="font-weight:bold;" >: </span>

      <select id="action" name="action" style="width:100px; margin:0px;">
          <option value="0" ><?php echo _('send email'); ?></option>
          <option value="1" ><?php echo _('set feed'); ?></option>
          <option value="2" ><?php echo _('call url'); ?></option>
          <option value="3" ><?php echo _('tweet'); ?></option>
          <option value="4" ><?php echo _('send prowl'); ?></option>
          <option value="5" ><?php echo _('send nma'); ?></option>
      </select>

      <span id="not-email" style="display:none">
          <input id="setemail" name="setemail" type="text" style="width:180px; margin:0px;" />
      </span>

      <span id="not-feed" style="display:none">
          <select id="setfeed" name="setfeed" style="width:160px; margin:0px;">
              <?php foreach ($feeds as $feed){ ?>
                  <option value="<?php echo $feed['id']; ?>"><?php echo $feed['name']; ?></option>
              <?php } ?>
          </select>
      </span>

      <span id="not-value" style="font-weight:bold;" ><?php echo _('to'); ?>
          <input id="setvalue" name="setvalue" type="text" style="width:60px; margin:0px;" />
      </span>

      <span id="not-message" style="font-weight:bold;" > <?php echo _('message'); ?>
          <input id="message" name="message" type="text" style="width:180px; margin:0px;" value="Feed is {value}"/>
      </span>

      <span id="not-priority" style="font-weight:bold;" > <?php echo _('priority'); ?>
          <select id="action-priority" name="priority" style="width:100px; margin:0px;">
              <option value="-2"><?php echo _('Very Low'); ?></option>
              <option value="-1"><?php echo _('Moderate'); ?></option>
              <option value="0"><?php echo _('Normal'); ?></option>
              <option value="1"><?php echo _('High'); ?></option>
              <option value="2"><?php echo _('Emergency'); ?></option>
          </select>
      </span>


      <span id="not-curl" style="display:none">
          <input id="callcurl" name="callcurl" type="text" style="width:400px; margin:0px;" />
      </span>

      <select id="mutetime" name="mutetime" style="width:100px; margin:0px;">
          <option value="0"><?php echo _('No mute'); ?></option>
          <option value="5"><?php echo _('5 secs'); ?></option>
          <option value="15"><?php echo _('15 secs'); ?></option>
          <option value="30"><?php echo _('30 secs'); ?></option>
          <option value="60"><?php echo _('1 min'); ?></option>
          <option value="300"><?php echo _('5 min'); ?></option>
          <option value="600"><?php echo _('10 min'); ?></option>
          <option value="1800"><?php echo _('30 min'); ?></option>
          <option value="3600"><?php echo _('1 hour'); ?></option>
          <option value="14400"><?php echo _('3 hour'); ?></option>
          <option value="28800"><?php echo _('6 hour'); ?></option>
          <option value="57600"><?php echo _('12 hour'); ?></option>
          <option value="86400"><?php echo _('24 hour'); ?></option>
      </select>

      <div id="addevent" class="btn btn-info" ><?php echo _('Add'); ?></div>
      <div id="editeventbtn" class="btn btn-info" ><?php echo _('Edit'); ?></div>
      </div>
      <div style="clear:both"></div>
    </div>
  </div>

</form>

<script type="application/javascript">
  var path =   "<?php echo $path; ?>";

  $("#addevent").click(function() {
    $.ajax({type:'GET',url:path+'event/add.json',data:$('#eventform').serialize(),success:function(){location.reload();}});
    return false;
  });

  $("#editeventbtn").click(function() {
    $.ajax({type:'GET',url:path+'event/edit.json',data:$('#eventform').serialize(),success:function(){location.reload();}});
    return false;
  });

  $(".deleteevent").click(function() {
    var eventid = $(this).attr("eventid");
    $.ajax({type:'GET',url:path+'event/delete.json',data:'id='+eventid,dataType:'json',success:function(){location.reload();}});
    return false;
  });

  $(".testevent").click(function() {
    var eventid = $(this).attr("eventid");
    var feedid = $(this).attr("feedid");
    $.ajax({type:'GET',url:path+'event/test.json',data:'id='+eventid+'&feedid='+feedid,dataType:'json',success:function(){location.reload();}});
    return false;
  });

  $(".enableevent").click(function() {
    var eventid = $(this).attr("eventid");
    $.ajax({type:'GET',url:path+'event/status.json',data:'id='+eventid+'&status=0',dataType:'json',success:function(){location.reload();}});
    return false;
  });

  $(".disableevent").click(function() {
    var eventid = $(this).attr("eventid");
    $.ajax({type:'GET',url:path+'event/status.json',data:'id='+eventid+'&status=1',dataType:'json',success:function(){location.reload();}});
    return false;
  });

  $(".editevent").click(function() {
    var eventid = $(this).attr("eventid");
    $("#editeventbtn").show();
    $("#addevent").hide();
    $("#eventid").val($(this).attr("eventid"));
    $("#eventtype").val($(this).attr("eventtype"));
    $("#eventfeed").val($(this).attr("eventfeed"));
    $("#eventvalue").val($(this).attr("eventvalue"));

    $("#setvalue").val($(this).attr("setvalue"));
    $("#setemail").val($(this).attr("setemail"));
    $("#callcurl").val($(this).attr("callcurl"));
    $("#action").val($(this).attr("action"));
    $("#message").val($(this).attr("message"));
    $("#mutetime").val($(this).attr("mutetime"));
    $("#eventtype").change();
    $("#action").change();
    return false;
  });


  $("#eventtype").change(function() {
    if ($(this).val() == 0) $("#not-inactive").show();
    if ($(this).val() == 1) $("#not-inactive").show();
    if ($(this).val() == 2) $("#not-inactive").show();
    if ($(this).val() == 3) $("#not-inactive").show();
    if ($(this).val() == 4) $("#not-inactive").hide();
    if ($(this).val() == 5) $("#not-inactive").show();
    if ($(this).val() == 6) $("#not-inactive").show();
    if ($(this).val() == 7) $("#not-inactive").hide();
  });

  $("#action").change(function() {
    if ($(this).val() == 0) { $("#not-email").show(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-priority").hide();}
    if ($(this).val() == 1) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").show(); $("#not-value").show(); $("#not-message").hide(); $("#not-priority").hide();}
    if ($(this).val() == 2) { $("#not-email").hide(); $("#not-curl").show(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").hide(); $("#not-priority").hide();}
    if ($(this).val() == 3) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-priority").hide();}
    if ($(this).val() == 4) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-priority").show();}
    if ($(this).val() == 5) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-priority").show();}

  });

  jQuery(document).ready(function(){
      $("#editeventbtn").hide();
});

</script>

