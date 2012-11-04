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

<?php global $path; ?>
<script type="text/javascript" src="<?php echo $path; ?>Lib/flot/jquery.min.js"></script>

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
<table class="catlist" style="">
  <tr>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
    <th></th>
  </tr>
  <?php $i=0; foreach ($event_list as $item) { $i++; ?>
  <tr class="d<?php echo ($i & 1); ?>" >
    <td><i>if</i></td>
    <td><b><?php echo get_feed_field($item['eventfeed'],'name'); ?></b></td>

    <td>
    <?php 
    if ($item['eventtype']==0) echo ">"; 
    if ($item['eventtype']==1) echo "<"; 
    if ($item['eventtype']==2) echo "==";
    if ($item['eventtype']==3) echo "inactive";
    ?></td>
    <td><?php if ($item['eventtype']!=3) echo $item['eventvalue']; ?></td>
    <td>
    <?php 
    $state = false;
    if ($item['eventtype']==0 && (get_feed_field($item['eventfeed'],'value')>$item['eventvalue'])) $state = true;
    if ($item['eventtype']==1 && (get_feed_field($item['eventfeed'],'value')<$item['eventvalue'])) $state = true;
    if ($item['eventtype']==2 && (get_feed_field($item['eventfeed'],'value')==$item['eventvalue'])) $state = true;
    if ($item['eventtype']==3 && (((time()-strtotime(get_feed_field($item['eventfeed'],'time')))/3600)>24)) $state = true;
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
    ?></td>

    <td><?php if ($item['action']==1) echo get_feed_field($item['setfeed'],'name'); ?></td>
    <td><i><?php if ($item['action']==1) echo "="; ?></i></td>
    <td><?php if ($item['action']==1) echo $item['setvalue']; ?></td>
    <td><div class="deleteevent btn" eventid="<?php echo $item['id']; ?>" >Delete</div></td>
  </tr>
  <?php } ?>
</table>
<?php } ?>


<form id="eventform" action="event/add" method="get">

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
      <option value="1" >less than</option>
      <option value="2" >equal to</option>
      <option value="3" >inactive</option>
      </select>
      
      <span id="not-inactive">
      <input name="eventvalue" type="text" style="width:60px; margin:0px;" />
      </span>

      <span style="font-weight:bold;" >: </span> 

      <select id="action" name="action" style="width:100px; margin:0px;">
      <option value="0" >send email</option>
      <option value="1" >set feed</option>
      </select>

      <span id="not-email" style="display:none">

      <select name="setfeed" style="width:160px; margin:0px;">
      <?php foreach ($feeds as $feed){ ?>
      <option value="<?php echo $feed['id']; ?>"><?php echo $feed['name']; ?></option>
      <?php } ?>
      </select>

      <span style="font-weight:bold;" >to</span> 

      <input name="setvalue" type="text" style="width:60px; margin:0px;" />


      </span>
 
      <div id="addevent" class="btn btn-info" >Add</div>
      </div>
      <div style="clear:both"></div>
    </div>
  </div>

</form>

<script type="application/javascript">
  var path =   "<?php echo $path; ?>";

  $("#addevent").click(function() {
    $.ajax({type:'GET',url:path+'event/add.json',data:$('#eventform').serialize(),success:location.reload()});
  });

  $(".deleteevent").click(function() {
    var eventid = $(this).attr("eventid");
    $.ajax({type:'GET',url:path+'event/delete.json',data:'id='+eventid,dataType:'json',success:location.reload()});
  });

  $("#eventtype").click(function() {
    if ($(this).val() == 3) $("#not-inactive").hide(); else $("#not-inactive").show();
  });

  $("#action").click(function() {
    if ($(this).val() == 1) $("#not-email").show();
    if ($(this).val() == 0) $("#not-email").hide();
  });
</script>

