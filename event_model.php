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

function event_add($userid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setemail,$setvalue,$callcurl,$message,$mutetime,$priority)
{
  db_query("INSERT INTO event (`userid`,`eventfeed`, `eventtype`, `eventvalue`, `action`, `setfeed`, `setemail`, `setvalue`, `lasttime`, `callcurl`, `mutetime`, `priority`, `message`) VALUES ('$userid','$eventfeed','$eventtype','$eventvalue','$action','$setfeed','$setemail','$setvalue','0','$callcurl','$mutetime','$priority','$message')");
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


// Set all event settings in one save
function set_event_settings($userid,$prowlkey,$consumerkey,$consumersecret,$usertoken,$usersecret,$smtpserver,$smtpuser,$smtppassword,$smtpport) 
{
  $result = db_query("SELECT userid  FROM event_settings WHERE `userid` = '$userid'");
  $row = db_fetch_array($result);

  if (!$row) 
  {
    db_query("INSERT INTO event_settings (`userid`) VALUES ('$userid')");
  }
  else
  {
    db_query("UPDATE event_settings SET prowlkey = '$prowlkey', consumerkey = '$consumerkey', consumersecret = '$consumersecret', usertoken = '$usertoken', usersecret = '$usersecret', smtpserver = '$smtpserver', smtpuser = '$smtpuser', smtppassword = '$smtppassword', smtpport = '$smtpport' WHERE userid='$userid'");
  }
} 

/*
function set_user_prowlkey($userid, $prowlkey, $message)
{
  db_query("UPDATE event_settings SET prowlkey = '$prowlkey', message = '$message' WHERE userid='$userid'");
}

function set_user_twitter($userid, $consumerkey,$consumersecret,$usertoken,$usersecret)
{
  db_query("UPDATE event_settings SET consumerkey = '$consumerkey', consumersecret = '$consumersecret', usertoken = '$usertoken', usersecret = '$usersecret' WHERE userid='$userid'");
}

function set_user_smtp($userid, $smtpserver, $smtpuser, $smtppassword, $smtpport)
{
  db_query("UPDATE event_settings SET smtpserver = '$smtpserver', smtpuser = '$smtpuser', smtppassword = '$smtppassword', smtpport = '$smtpport' WHERE userid='$userid'");
}
*/

function get_event_settings($userid) {
  $result = db_query("SELECT *  FROM event_settings WHERE `userid` = '$userid'");
  $row = db_fetch_array($result);
  return $row;  
}

function get_user_smtp($userid) {
  $result = db_query("SELECT smtpserver, smtpuser, smtppassword, smtpport  FROM event_settings WHERE `userid` = '$userid'");
  $row = db_fetch_array($result);
  return $row;  
}

function get_user_twitter($userid) {
  $result = db_query("SELECT consumerkey, consumersecret, usertoken, usersecret FROM event_settings WHERE `userid` = '$userid'");
  $row = db_fetch_array($result);
  return $row;  
}

function get_user_prowl($userid) {
  $result = db_query("SELECT prowlkey FROM event_settings WHERE `userid` = '$userid'");
  $row = db_fetch_array($result);
  return $row;
}

function check_feed_event($feedid,$updatetime,$feedtime,$value,$row=NULL) {

    global $session;
    $userid = $session['userid'];

    $result = db_query("SELECT * FROM event WHERE eventfeed = $feedid");

    // check type
    while ($row = db_fetch_array($result)) {
        if ($row['lasttime']+$row['mutetime'] > time() ) {
            return 0;    
        }
        
        $sendAlert = 0;
        switch($row['eventtype']) {
            case 0:
                // more than
                if ($value > $row['eventvalue']) {
                    $sendAlert = 1;
                }
                break;
            case 1:
                // less than
                if ($value < $row['eventvalue']) {
                    $sendAlert = 1;
                }
                break;
            case 2:
                // equal to
                if ($value == $row['eventvalue']) {
                    $sendAlert = 1;
                }
                break;
            case 3:
                // inactive
                // not sure this can be called as no feed updated
                //if (((time()-$row['lasttime'])/3600)>24) {}
                break;
            case 4:
                // updated
                $sendAlert = 1;
                break;
            case 5:
                // increased by
                $feedname = 'feed_'.$feedid;
                $resultprev = db_query("SELECT * FROM $feedname ORDER BY `time` DESC LIMIT 1,1");
                $rowprev = db_fetch_array($resultprev);
                //echo "INC == ".$value." > ".$rowprev['data']."+".$row['eventvalue'];
                if ($value > ($rowprev['data']+$row['eventvalue'])) {
                    $sendAlert = 1;
                    }
                break;
            case 6:
                // decreased by
                $feedname = 'feed_'.$feedid;
                $resultprev = db_query("SELECT * FROM $feedname ORDER BY `time` DESC LIMIT 1,1");

                $rowprev = db_fetch_array($resultprev);

                //echo "DEC == ".$value."<". $rowprev['data']."-".$row['eventvalue'];
                if ($value < ($rowprev['data']-$row['eventvalue'])) {
                    $sendAlert = 1;
                    }
                break;
        }
        
        63+125 = 188
        if new val > 188 alert

        // event type
        if ($sendAlert == 1) {
            switch($row['action']) {
                case 0:
                    // email
                    require_once(realpath(dirname(__FILE__)).'/../event/scripts/phpmailer/class.phpmailer.php');
                    $smtp = get_user_smtp($userid);

                    $mail             = new PHPMailer();
                    
                	$body = str_replace('{value}', $value, $row['message']);

                    if (empty($body)) { $body = "No message body"; }
                    //$body             = eregi_replace("[\]",'',$body);
                    
                    $mail->IsSMTP(); // telling the class to use SMTP
                    $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                                               // 1 = errors and messages
                                                               // 2 = messages only
                    $mail->SMTPAuth   = true;                  // enable SMTP authentication
                    $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier

                    $mail->Host       = $smtp['smtpserver'];     // sets GMAIL as the SMTP server
                    $mail->Port       = $smtp['smtpport'];       // set the SMTP port for the GMAIL server
                    $mail->Username   = $smtp['smtpuser'];       // GMAIL username
                    $salt = get_user_salt($row['userid']);

                    $mail->Password   = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($smtp['smtppassword']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));   // GMAIL password
                    
                    $mail->SetFrom($smtp['smtpuser'], 'emoncms');
                    
                    //$mail->AddReplyTo("user2@gmail.com', 'First Last");
                    
                    $mail->Subject    = "emoncms update";
                    
                    //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
                    
                    $mail->MsgHTML($body);
                    
                    $address = $smtp['smtpuser'];
                    $mail->AddAddress($address, "emoncms");
                    
                    //$mail->AddAttachment("images/phpmailer.gif");      // attachment
                    //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
                    
                    if(!$mail->Send()) {
                      echo "Mailer Error: " . $mail->ErrorInfo;
                    } else {
                      echo "Message sent!";
                    }

                    break;
                case 1:
                    // set feed
                    $setfeed = $row['setfeed'];
                    $setvalue = $row['setvalue'];
                    db_query("UPDATE feeds SET value = '$setvalue', time = '$updatetime' WHERE id='$setfeed'");
                                            break;
                case 2:
                    // call url
                    $ch = curl_init();
                    // set URL and other appropriate options
                    curl_setopt($ch, CURLOPT_URL, $row['callcurl']);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    // grab URL and pass it to the browser
                    curl_exec($ch);
                    // close cURL resource, and free up system resources
                    curl_close($ch);
                    break;
                case 3:
                    // Twitter
                    require_once(realpath(dirname(__FILE__)).'/../event/scripts/twitter/oAuth/tmhOAuth.php');
                    $twitter = get_user_twitter($userid);
                      
                    // Set the authorization values
                    // In keeping with the OAuth tradition of maximum confusion, 
                    // the names of some of these values are different from the Twitter Dev interface
                    // user_token is called Access Token on the Dev site
                    // user_secret is called Access Token Secret on the Dev site
                    // The values here have asterisks to hide the true contents 
                    // You need to use the actual values from Twitter
                    $writeconnection = new tmhOAuth(array(
                        'consumer_key' => $twitter['consumerkey'],
                        'consumer_secret' => $twitter['consumersecret'],
                        'user_token' => $twitter['usertoken'],
                        'user_secret' => $twitter['usersecret'],
                    )); 
                    
                    $body = str_replace('{value}', $value, $row['message']);
                    if (empty($body)) { $body = "No message body"; }

                    // Make the API call
                    $writeconnection->request('POST', 
                        $writeconnection->url('1/statuses/update'), array('status' => $body));

                    if ($writeconnection->response['code'] != 200) {
                      error_log("Twitter error:".$writeconnection->pr(htmlentities($writeconnection->response['response'])));
                    }                            
                    break;                      
                case 4:
                    // Prowl
                    require_once realpath(dirname(__FILE__)).'/scripts/prowlphp/ProwlConnector.class.php';
                    require_once realpath(dirname(__FILE__)).'/scripts/prowlphp/ProwlMessage.class.php';
                    require_once realpath(dirname(__FILE__)).'/scripts/prowlphp/ProwlResponse.class.php';
                    $prowl = get_user_prowl($userid);

                    $oProwl = new ProwlConnector();
                    $oMsg 	= new ProwlMessage();

                	$oProwl->setIsPostRequest(true);
                	$oMsg->setPriority($row['priority']);
                	
                	$oMsg->addApiKey($prowl['prowlkey']);
                
                	$oMsg->setEvent('emoncms event');
                	
                	// These are optional:
                	$message = str_replace('{value}', $value, $row['message']);
                	$oMsg->setDescription($message);
                	$oMsg->setApplication('emoncms application');
                	
                	$oResponse = $oProwl->push($oMsg);
                	
            		if ($oResponse->isError()) {	
                        	error_log("Prowl error:".$oResponse->getErrorAsString());
                    }

                    break;
            }
        // update the lasttime called
        db_query("UPDATE event SET lasttime = '".time()."' WHERE id='".$row['id']."'");                
        }
    }
}
