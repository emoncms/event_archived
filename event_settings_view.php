
<?php global $user,$session; ?>

<h2>Event settings</h2>

<form action="savesettings" method="post">

<div class="row-fluid">
  <div class="span4">

    <h3><?php echo _('Email settings'); ?></h3>
    
    <label><?php echo _('SMTP server'); ?></label>
    <input type="text" name="smtpserver" value="<?php echo $settings['smtpserver']; ?>" />
    <br>
    
    <label><?php echo _('SMTP user'); ?></label>
    <input type="text" name="smtpuser"  value="<?php echo $settings['smtpuser']; ?>" />
    <br>

    <label><?php echo _('SMTP password'); ?></label>
    <?php
    $salt = $user->get_salt($session['userid']);
    $smtppassword   = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($settings['smtppassword']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));   // GMAIL password
    ?>
    <input type="password" name="smtppassword"  value="<?php echo $smtppassword; ?>" />
    <br>    

    <select name="smtpport">
        <option <?php if ($settings['smtpport'] == '25') { echo "selected"; }?>  value="25">25 (No SSL)</option>
        <option <?php if ($settings['smtpport'] == '587') { echo "selected"; }?> value="587">587 (TSL)</option> 
        <option <?php if ($settings['smtpport'] == '465') { echo "selected"; }?> value="465">465 (SSL)</option>    
    </select>
    <br><br>
    <b><?php echo _('Note for gmail.com account:'); ?></b><br>
    <p><?php echo _('You have to change the settings to allow less security apps and enable application to access gmail account.'); ?><br>

    <?php echo _('Follow below steps:'); ?><br>
    <?php echo _('1. login to your gmail account (same as you are using in this setting)'); ?><br>

    <?php echo _('2. Go to below link and enable access to less security apps'); ?><br>

    <a href="https://www.google.com/settings/security/lesssecureapps" target="_blank">https://www.google.com/settings/security/lesssecureapps</a></p>
     

  </div>

  <div class="span4">

    <h3><?php echo _('Twitter settings'); ?></h3>
    <label><?php echo _('Consumer key:'); ?></label>
    <input type="text" name="consumerkey" value="<?php echo $settings['consumerkey']; ?>"/>
    <label><?php echo _('Consumer secret:'); ?></label>
    <input type="text" name="consumersecret" value="<?php echo $settings['consumersecret']; ?>"/>
    <label><?php echo _('Access token:'); ?></label>
    <input type="text" name="usertoken" value="<?php echo $settings['usertoken']; ?>"/>
    <label><?php echo _('Access Token secret:'); ?></label>
    <input type="text" name="usersecret" value="<?php echo $settings['usersecret']; ?>"/>
    <br>
    
  </div>
  <div class="span4">

    <h3><?php echo _('Prowl settings'); ?></h3>
    <label><?php echo _('Prowl key:'); ?></label>
    <input type="text" name="prowlkey" value="<?php echo $settings['prowlkey']; ?>" />
    <br>

  </div>
  <div class="span4">

    <h3><?php echo _('NMA settings'); ?></h3>
    <label><?php echo _('NMA key:'); ?></label>
    <input type="text" name="nmakey" value="<?php echo $settings['nmakey']; ?>" />
    <br>

  </div>

    <div class="span4">
    <h3><?php echo _('MQTT settings'); ?></h3>
    <label><?php echo _('MQTT broker IP address:'); ?></label>
    <input type="text" name="mqttbrokerip" value="<?php echo $settings['mqttbrokerip']; ?>" />
    <label><?php echo _('MQTT broker port:'); ?></label>
    <input type="text" name="mqttbrokerport" value="<?php echo $settings['mqttbrokerport']; ?>" />
    <label><?php echo _('MQTT username:'); ?></label>
    <input type="text" name="mqttusername" value="<?php echo $settings['mqttusername']; ?>" />
    <label><?php echo _('MQTT password:'); ?></label>
    <?php
    $salt = $user->get_salt($session['userid']);
    $mqttpassword   = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($settings['mqttpassword']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));
    ?>
    <input type="password" name="mqttpassword" value="<?php echo $mqttpassword; ?>" />
    <br>
  </div>

</div>

    <input type="submit" class="btn btn-danger" value="<?php echo _('Change'); ?>" />
  </form>
