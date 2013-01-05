<h2>Event settings</h2>

<form action="savesettings" method="post">

<div class="row-fluid">
  <div class="span4">

    <h3><?php echo _('Email settings'); ?></h3>
    
    <label><?php echo _('SMTP server'); ?></label>
    <input type="text" name="smtpserver" value="<?php echo $user['smtpserver']; ?>" />
    <br>
    
    <label><?php echo _('SMTP user'); ?></label>
    <input type="text" name="smtpuser"  value="<?php echo $user['smtpuser']; ?>" />
    <br>

    <label><?php echo _('SMTP password'); ?></label>
    <?php
    $salt = get_user_salt($user['userid']);
    $smtppassword   = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($user['smtppassword']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));   // GMAIL password
    ?>
    <input type="password" name="smtppassword"  value="<?php echo $smtppassword; ?>" />
    <br>    

    <select name="smtpport">
        <option <?php if ($user['smtpport'] == '25') { echo "selected"; }?>  value="25">25 (No SSL)</option>
        <option <?php if ($user['smtpport'] == '465') { echo "selected"; }?> value="465">465 (SSL)</option>    
    </select>
    <br>

  </div>

  <div class="span4">

    <h3><?php echo _('Twitter settings'); ?></h3>
    <label><?php echo _('Consumer key:'); ?></label>
    <input type="text" name="consumerkey" value="<?php echo $user['consumerkey']; ?>"/>
    <label><?php echo _('Consumer secret:'); ?></label>
    <input type="text" name="consumersecret" value="<?php echo $user['consumersecret']; ?>"/>
    <label><?php echo _('Access token:'); ?></label>
    <input type="text" name="usertoken" value="<?php echo $user['usertoken']; ?>"/>
    <label><?php echo _('Access Token secret:'); ?></label>
    <input type="text" name="usersecret" value="<?php echo $user['usersecret']; ?>"/>
    <br>
    
  </div>
  <div class="span4">

    <h3><?php echo _('Prowl settings'); ?></h3>
    <label><?php echo _('Prowl key:'); ?></label>
    <input type="text" name="prowlkey" value="<?php echo $user['prowlkey']; ?>" />
    <br>

  </div>
</div>

    <input type="submit" class="btn btn-danger" value="<?php echo _('Change'); ?>" />
  </form>
