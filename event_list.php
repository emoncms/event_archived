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

<div style="float:right;"><a href="<?php echo $path; ?>event/settings">Event Settings</a></div>
<h2>Event</h2>

<p>Setup actions to occur when a feed goes above, below or is equal to a specified value, or becomes inactive. Send an email or set another feed to a specified value.</p>

<?php if (!$event_list) { ?>
<div class="alert alert-block">
<h4 class="alert-heading">No event notifications created</h4>
<p>To add an event based notification:</p>
<p>1) Select the feed you wish to be notified about from the drop down menu</p>
<p>2) Select whether you want to be notifed if the feed goes above, below or equals the value specified, or is inactive.</p>
<p>3) Enter a value</p> 
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
    if ($item['eventtype']==3) echo "inactive";
    if ($item['eventtype']==4) echo "updated";
    if ($item['eventtype']==5) echo "inc by";
    if ($item['eventtype']==6) echo "dec by";
    ?></td>
    <td><?php if ($item['eventtype']!=3) echo $item['eventvalue']; ?></td>
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
    if ($item['action']==0)
    {
      echo "<span class='label label-info' >Last true: ".date("Y-n-j H:i:s", $item['lasttime'])."</span>"; 
    }
    ?>
    </td>
    
    <td>
    <?php 
    if ($item['action']==0) echo "send email"; 
    if ($item['action']==1) echo "set feed"; 
    if ($item['action']==2) echo "call url"; 
    if ($item['action']==3) echo "send tweet"; 
    if ($item['action']==4) echo "send prowl"; 
    if ($item['action']==5) echo "send nma"; 
    ?></td>

    <td><?php if ($item['action']==1) echo $feed->get_field($item['setfeed'],'name'); ?></td>
    <td><i><?php if ($item['action']==1) echo "="; ?></i></td>
    <td><?php if ($item['action']==1) echo $item['setvalue']; ?></td>

    <td><?php if ($item['action']==2) echo $item['callcurl']; ?></td>
    <td><?php echo $item['message']; ?> </td>
    <td><?php echo $item['mutetime']; ?> secs</td>
    
    <td><div class="deleteevent btn" eventid="<?php echo $item['id']; ?>" >Delete</div></td>
  </tr>
  <?php } ?>
</table>
<?php } ?>


<form id="eventform" action="event/add" method="get" onsubmit="return false;">

  <div style=" background-color:#eee; margin-bottom:10px; border: 1px solid #ddd">
    <div style="padding:10px;  border-top: 1px solid #fff; ">
      <div style="float:left; padding-top:2px; font-weight:bold;">IF</div>

      <div style="float:right;">
      <select name="eventfeed" style="width:160px; margin:0px;">
      <?php foreach ($feeds as $feed){ ?>
      <option value="<?php echo $feed['id']; ?>"><?php echo $feed['name']; ?></option>
      <?php } ?>
      </select>
      <span style="font-weight:bold;" >is</span> 
      <select id="eventtype" name="eventtype" style="width:100px; margin:0px;">
          <option value="0" >more than</option>
          <option value="5" >increases by</option>
          <option value="1" >less than</option>
          <option value="6" >reduces by</option>
          <option value="2" >equal to</option>
          <option value="3" >inactive</option>
          <option value="4" >is updated</option>
      </select>
      
      <span id="not-inactive">
          <input name="eventvalue" type="text" style="width:60px; margin:0px;" />
      </span>

      <span style="font-weight:bold;" >: </span> 

      <select id="action" name="action" style="width:100px; margin:0px;">
          <option value="0" >send email</option>
          <option value="1" >set feed</option>
          <option value="2" >call url</option>
          <option value="3" >tweet</option>
          <option value="4" >send prowl</option>
          <option value="5" >send nma</option>
      </select>

      <span id="not-email" style="display:none">
          <input name="setemail" type="text" style="width:180px; margin:0px;" />
      </span>

      <span id="not-feed" style="display:none">
          <select name="setfeed" style="width:160px; margin:0px;">
              <?php foreach ($feeds as $feed){ ?>
                  <option value="<?php echo $feed['id']; ?>"><?php echo $feed['name']; ?></option>
              <?php } ?>
          </select>
      </span>

      <span id="not-value" style="font-weight:bold;" >to
          <input name="setvalue" type="text" style="width:60px; margin:0px;" />
      </span>

      <span id="not-message" style="font-weight:bold;" > message
          <input name="message" type="text" style="width:180px; margin:0px;" value="Feed is {value}"/>
      </span>

      <span id="not-prowl" style="font-weight:bold;" > priority
          <select id="action" name="priority" style="width:100px; margin:0px;">
              <option value="-2">Very Low</option>
              <option value="-1">Moderate</option>
              <option value="0">Normal</option>
              <option value="1">High</option>
              <option value="2">Emergency</option>
          </select>
      </span>


      <span id="not-curl" style="display:none">
          <input name="callcurl" type="text" style="width:180px; margin:0px;" />
      </span>

      <select id="action" name="mutetime" style="width:100px; margin:0px;">
          <option value="0">No mute</option>
          <option value="5">5 secs</option>
          <option value="15">15 secs</option>
          <option value="30">30 secs</option>
          <option value="60">1 min</option>
          <option value="300">5 min</option>
          <option value="600">10 min</option>
          <option value="1800">30 min</option>
          <option value="3600">1 hour</option>
          <option value="14400">3 hour</option>
          <option value="28800">6 hour</option>
          <option value="57600">12 hour</option>
          <option value="86400">24 hour</option>
      </select>
 
      <div id="addevent" class="btn btn-info" >Add</div>
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

  $(".deleteevent").click(function() {
    var eventid = $(this).attr("eventid");
    $.ajax({type:'GET',url:path+'event/delete.json',data:'id='+eventid,dataType:'json',success:function(){location.reload();}});
    return false;
  });

  $("#eventtype").click(function() {
    if ($(this).val() == 0) $("#not-inactive").show();
    if ($(this).val() == 1) $("#not-inactive").show();
    if ($(this).val() == 2) $("#not-inactive").show();
    if ($(this).val() == 3) $("#not-inactive").hide();
    if ($(this).val() == 4) $("#not-inactive").hide();
    if ($(this).val() == 5) $("#not-inactive").hide();
  });

  $("#action").click(function() {
    if ($(this).val() == 0) { $("#not-email").show(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-prowl").hide();}
    if ($(this).val() == 1) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").show(); $("#not-value").show(); $("#not-message").hide(); $("#not-prowl").hide();}
    if ($(this).val() == 2) { $("#not-email").hide(); $("#not-curl").show(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").hide(); $("#not-prowl").hide();}
    if ($(this).val() == 3) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-prowl").hide();}
    if ($(this).val() == 4) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-prowl").show();}
    if ($(this).val() == 5) { $("#not-email").hide(); $("#not-curl").hide(); $("#not-feed").hide(); $("#not-value").hide(); $("#not-message").show(); $("#not-prowl").hide();}
  });
</script>

