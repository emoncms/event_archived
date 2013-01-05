<?php

$schema['event_settings'] = array(
  'userid' => array('type' => 'int(11)'),
  'smtpserver' => array('type' => 'varchar(60)'),
  'smtpuser' => array('type' => 'varchar(60)'),
  'smtppassword' => array('type' => 'varchar(40)'),
  'smtpport' => array('type' => 'varchar(3)'),
  'consumerkey' => array('type' => 'varchar(60)'),
  'consumersecret' => array('type' => 'varchar(60)'),
  'usertoken' => array('type' => 'varchar(60)'),
  'usersecret' => array('type' => 'varchar(60)'),
  'prowlkey' => array('type' => 'varchar(60)')
);

$schema['event'] = array(
  'id' => array('type' => 'int(11)', 'Null'=>'NO', 'Key'=>'PRI', 'Extra'=>'auto_increment'),
  'userid' => array('type' => 'int(11)'),
  'eventfeed' => array('type' => 'int(11)'),
  'eventtype' => array('type' => 'int(11)'),
  'eventvalue' => array('type' => 'float'),
  'action' => array('type' => 'int(11)'),
  'setfeed' => array('type' => 'int(11)'),
  'setvalue' => array('type' => 'float'),
  'callcurl' => array('type' => 'text'),
  'lasttime' => array('type' => 'int(11)'),
  'mutetime' => array('type' => 'int(11)'),
  'priority' => array('type' => 'int(3)', 'default'=>0),
  'message' => array('type' => 'text')
);

