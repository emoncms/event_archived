
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
        <option <?php if ($settings['smtpport'] == '465') { echo "selected"; }?> value="465">465 (SSL)</option>    
    </select>
    <br>

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

</div>

    <input type="submit" class="btn btn-danger" value="<?php echo _('Change'); ?>" />
  </form>
