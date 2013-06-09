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

class Event
{
    private $mysqli;

    public function __construct($mysqli)
    {
        $this->mysqli = $mysqli;
    }

    /*
     $this->mysqli->query("INSERT INTO table (`field`) VALUES ('$value')");
     $this->mysqli->query("SELECT * FROM table WHERE `field` = '$value'");
     $this->mysqli->query(UPDATE table SET field = '$value' WHERE `field` = '$value');

    */

    public function set_lasttime($userid,$id,$time)
    {
      $this->mysqli->query("UPDATE event SET `lasttime` = '$time' WHERE `userid` = '$userid' AND `id` = '$id' ");
    }

    public function add($userid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setemail,$setvalue,$callcurl,$message,$mutetime,$priority)
    {
      $this->mysqli->query("INSERT INTO event (`userid`,`eventfeed`, `eventtype`, `eventvalue`, `action`, `setfeed`, `setemail`, `setvalue`, `lasttime`, `callcurl`, `mutetime`, `priority`, `message`) VALUES ('$userid','$eventfeed','$eventtype','$eventvalue','$action','$setfeed','$setemail','$setvalue','0','$callcurl','$mutetime','$priority','$message')");
    }

    public function delete($userid,$id)
    {
     $this->mysqli->query("DELETE FROM event WHERE `userid` = '$userid' AND `id` = '$id'");
    }

    public function eventlist($userid)
    {
      $list = array();
      $result = $this->mysqli->query("SELECT * FROM event WHERE `userid` = '$userid'");
      while ($row = $result->fetch_array())
      {
        $list[] = $row;
      }
      return $list;
    }


    // Set all event settings in one save
    public function set_settings($userid,$prowlkey,$consumerkey,$consumersecret,$usertoken,$usersecret,$smtpserver,$smtpuser,$smtppassword,$smtpport,$nmakey)
    {
      $result = $this->mysqli->query("SELECT userid  FROM event_settings WHERE `userid` = '$userid'");
      $row = $result->fetch_array();

      if (!$row)
      {
        $this->mysqli->query("INSERT INTO event_settings (`userid`) VALUES ('$userid')");
      }
      else
      {
        $this->mysqli->query("UPDATE event_settings SET prowlkey = '$prowlkey', consumerkey = '$consumerkey', consumersecret = '$consumersecret', usertoken = '$usertoken', usersecret = '$usersecret', smtpserver = '$smtpserver', smtpuser = '$smtpuser', smtppassword = '$smtppassword', smtpport = '$smtpport', nmakey = '$nmakey' WHERE userid='$userid'");
      }
    }

    /*
    public function set_user_prowlkey($userid, $prowlkey, $message)
    {
      $this->mysqli->query("UPDATE event_settings SET prowlkey = '$prowlkey', message = '$message' WHERE userid='$userid'");
    }

    public function set_user_twitter($userid, $consumerkey,$consumersecret,$usertoken,$usersecret)
    {
      $this->mysqli->query("UPDATE event_settings SET consumerkey = '$consumerkey', consumersecret = '$consumersecret', usertoken = '$usertoken', usersecret = '$usersecret' WHERE userid='$userid'");
    }

    public function set_user_smtp($userid, $smtpserver, $smtpuser, $smtppassword, $smtpport)
    {
      $this->mysqli->query("UPDATE event_settings SET smtpserver = '$smtpserver', smtpuser = '$smtpuser', smtppassword = '$smtppassword', smtpport = '$smtpport' WHERE userid='$userid'");
    }
    */

    public function get_settings($userid) {
      $result = $this->mysqli->query("SELECT *  FROM event_settings WHERE `userid` = '$userid'");
      $row = $result->fetch_array();
      return $row;
    }

    public function get_user_smtp($userid) {
      $result = $this->mysqli->query("SELECT smtpserver, smtpuser, smtppassword, smtpport  FROM event_settings WHERE `userid` = '$userid'");
      $row = $result->fetch_array();
      return $row;
    }

    public function get_user_twitter($userid) {
      $result = $this->mysqli->query("SELECT consumerkey, consumersecret, usertoken, usersecret FROM event_settings WHERE `userid` = '$userid'");
      $row = $result->fetch_array();
      return $row;
    }

    public function get_user_prowl($userid) {
      $result = $this->mysqli->query("SELECT prowlkey FROM event_settings WHERE `userid` = '$userid'");
      $row = $result->fetch_array();
      return $row;
    }

    public function get_user_nma($userid) {
      $result = $this->mysqli->query("SELECT nmakey FROM event_settings WHERE `userid` = '$userid'");
      $row = $result->fetch_array();
      return $row;
    }


    public function check_feed_event($feedid,$updatetime,$feedtime,$value,$row=NULL) {

        global $user,$session,$feed;
        $userid = $session['userid'];

        $result = $this->mysqli->query("SELECT * FROM event WHERE eventfeed = $feedid");

        // check type
        while ($row = $result->fetch_array()) {

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
                    $resultprev = $this->mysqli->query("SELECT * FROM $feedname ORDER BY `time` DESC LIMIT 1,1");
                    $rowprev = $resultprev->fetch_array();
                    //echo "INC == ".$value." > ".$rowprev['data']."+".$row['eventvalue'];
                    if ($value > ($rowprev['data']+$row['eventvalue'])) {
                        $sendAlert = 1;
                        }
                    break;
                case 6:
                    // decreased by
                    $feedname = 'feed_'.$feedid;
                    $resultprev = $this->mysqli->query("SELECT * FROM $feedname ORDER BY `time` DESC LIMIT 1,1");

                    $rowprev = $resultprev->fetch_array();

                    //echo "DEC == ".$value."<". $rowprev['data']."-".$row['eventvalue'];
                    if ($value < ($rowprev['data']-$row['eventvalue'])) {
                        $sendAlert = 1;
                        }
                    break;
            }

            // event type
            if ($sendAlert == 1) {
                switch($row['action']) {
                    case 0:
                        // email
                        require_once(realpath(dirname(__FILE__)).'/../event/scripts/phpmailer/class.phpmailer.php');
                        $smtp = $this->get_user_smtp($userid);

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

                        $mail->Host       = $smtp['smtpserver'];      // sets GMAIL as the SMTP server
                        $mail->Port       = $smtp['smtpport'];         // set the SMTP port for the GMAIL server
                        $mail->Username   = $smtp['smtpuser'];       // GMAIL username
                        $salt = $user->get_salt($userid);

                        $mail->Password   = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($smtp['smtppassword']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));   // GMAIL password

                        $address = $smtp['smtpuser'];
                        $mail->SetFrom($address, 'emoncms');

                        //$mail->AddReplyTo("user2@gmail.com', 'First Last");

                        $mail->Subject    = "emoncms update on feed -> " . $feed->get_field($row['eventfeed'],'name');;

                        //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

                        $mail->MsgHTML($body);

                        $mail->AddAddress($address, "emoncms");

                        //$mail->AddAttachment("images/phpmailer.gif");      // attachment
                        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment

                        if(!$mail->Send()) {
                          echo "Mailer Error: " . $mail->ErrorInfo;
                          error_log("Mailer Error: " . $mail->ErrorInfo);
                        } else {
                          echo "Message sent!";
                          error_log("Message sent");
                        }

                        break;
                    case 1:
                        // set feed
                        $setfeed = $row['setfeed'];
                        $setvalue = $row['setvalue'];
                        $this->mysqli->query("UPDATE feeds SET value = '$setvalue', time = '$updatetime' WHERE id='$setfeed'");
                                                break;
                    case 2:
                        // call url
                        $ch = curl_init();
                        // set URL and other appropriate options
                        curl_setopt($ch, CURLOPT_URL, $row['callcurl']);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

                        // grab URL and pass it to the browser
                        curl_exec($ch);
                        // close cURL resource, and free up system resources
                        curl_close($ch);
		                error_log("Curl Log:".$row['callcurl']);

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

                    	$message = htmlspecialchars(str_replace('{value}', $value, $row['message']));
                    	$oMsg->setEvent($message);


                    	// These are optional:
                    	$message = 'event at '.date("Y-m-d H:i:s",time());
                    	$oMsg->setDescription($message);
                    	$oMsg->setApplication('emoncms');

                    	$oResponse = $oProwl->push($oMsg);

                		if ($oResponse->isError()) {
                            	error_log("Prowl error:".$oResponse->getErrorAsString());
                        }

                        break;
                }
            // update the lasttime called
            $this->mysqli->query("UPDATE event SET lasttime = '".time()."' WHERE id='".$row['id']."'");
            }
        }
    }
}
