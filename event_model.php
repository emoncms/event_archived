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
    private $redis;
    
    public function __construct($mysqli,$redis)
    {
        $this->mysqli = $mysqli;
        $this->redis = $redis;
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

    public function update($userid,$id,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setemail,$setvalue,$callcurl,$message,$mutetime,$priority)
    {
      $sql = "UPDATE    emoncms.event SET eventfeed = $eventfeed, eventtype = $eventtype, eventvalue = $eventvalue, action = $action, setfeed = $setfeed, setemail = '$setemail', setvalue = $setvalue,  callcurl = '$callcurl', mutetime = $mutetime, priority = $priority, message = '$message' WHERE `userid` = '$userid' AND `id` = '$id' ";
      error_log('Mysql Query: ' + $sql);
      $result = $this->mysqli->query($sql);
      if (!$result){
        error_log('Event Update: Mysql Error: ' + $this->mysqli->error);
      }
    }

    public function add($userid,$eventfeed,$eventtype,$eventvalue,$action,$setfeed,$setemail,$setvalue,$callcurl,$message,$mutetime,$priority)
    {
      $sql = "INSERT INTO event (`userid`,`eventfeed`, `eventtype`, `eventvalue`, `action`, `setfeed`, `setemail`, `setvalue`, `lasttime`, `callcurl`, `mutetime`, `priority`, `message`, `disabled`) VALUES ('$userid','$eventfeed','$eventtype','$eventvalue','$action','$setfeed','$setemail','$setvalue','0','$callcurl','$mutetime','$priority','$message','0')";
      error_log('Mysql Query: ' + $sql);
      $result = $this->mysqli->query($sql);
      if (!$result){
        error_log('Event Add: Mysql Error: ' + $this->mysqli->error);
      }
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
    public function set_status($userid, $id, $status)
    {
      $this->mysqli->query("UPDATE event SET disabled = '$status' WHERE userid='$userid' and id = $id");
    }

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

    public function test($userid,$id,$feedid)
    {
          global $feed;
          $t = time();
          $f = $feed->get($feedid);
          if($f){
            $this->check_feed_event($id,$t,$t,$f['value'],null,true);
        }else{
            return("Wrong input parameters");
        }

    }


    public function check_feed_event($feedid,$updatetime,$feedtime,$value,$row=NULL,$test=false) {

        global $user,$session,$feed;
        $userid = $session['userid'];

        $sqlFeed = "SELECT * FROM event WHERE `userid` = '$userid'";
        if ($test){
            $sqlFeed = $sqlFeed. " and id = $feedid";
        }else{
            $sqlFeed = $sqlFeed. " and (`disabled` <> 1 or `disabled` IS NULL) and (eventfeed = $feedid or eventtype=3)";
        }

        $result = $this->mysqli->query($sqlFeed);

        // check type
        while ($row = $result->fetch_array()) {

            if ($row['lasttime']+$row['mutetime'] > time() && !$test) {
                continue;
            }
            if ($test){
               $sendAlert = 1;
            }else{
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
                        $feedData = $feed->get($row['eventfeed']);
                        //error_log("Feeddata: " .$feedData->time);
                        $t = time()- strtotime($feedData['time']);
                        //error_log("t: " .$t);
                        if ($t > $row['eventvalue']){
                           $sendAlert = 1;
                        }
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
                    case 7:
                        // manual update
                        // Check if event.lasttime is less than feed.time
                        $feedData = $feed->get($feedid);
                        if ($feedData['time'] > $row['lasttime']){
                           $sendAlert = 1;
                        }
                }

            }
            
            $feedData = $feed->get($row['eventfeed']);
        	$message = $row['message'];
        	$message = str_replace('{feed}', $feedData['name'], $message);
            $message = str_replace('{value}', $value, $message);
        	$message = htmlspecialchars($message);
            if (empty($message)) { $message = "No message body"; }

            if($test){
                $message = 'TEST - '.$message;
            }

            // event type
            if ($sendAlert == 1) {
                switch($row['action']) {
                    case 0:
                        // email
                        require_once(realpath(dirname(__FILE__)).'/../event/scripts/phpmailer/class.phpmailer.php');
                        $smtp = $this->get_settings($userid);

                        $mail             = new PHPMailer();

                        $mail->IsSMTP(); // telling the class to use SMTP
                        $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
                                                                   // 1 = errors and messages
                                                                   // 2 = messages only
                        $mail->SMTPAuth   = true;                  // enable SMTP authentication
                        if ($smtp['smtpport'] == 465) 
				{$mail->SMTPSecure = "ssl";}                 // sets the prefix to the server

                        $mail->Host       = $smtp['smtpserver'];      // sets GMAIL as the SMTP server
                        $mail->Port       = $smtp['smtpport'];         // set the SMTP port for the GMAIL server
                        $mail->Username   = $smtp['smtpuser'];       // GMAIL username
                        $salt = $user->get_salt($userid);

                        $mail->Password   = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $salt, base64_decode($smtp['smtppassword']), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));   // GMAIL password

                        $address = $smtp['smtpuser'];
                        $mail->SetFrom($address, 'emoncms');

                        //$mail->AddReplyTo("user2@gmail.com', 'First Last");

                        
                        $mail->Subject    = $message;
                        //$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

                        $mail->MsgHTML($message);

                        $dest = $address;
                        if ($row['setemail'] != ''){
                            $dest = $row['setemail'];
                        }
                        // Allows multiple recipients for the event email. Seperate by semi-colon ;
                        if (strpos($dest,';') !== false) {
                            $addresses = explode(';', $dest);
                            foreach ($addresses as &$addressee) {
                            	$mail->AddAddress($addressee, "emoncms");
                            }
                        }
                        else {
                            $mail->AddAddress($dest, "emoncms");
                        }
                        

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
                        
                        $this->redis->hMset("feed:lastvalue:$setfeed", array('value' => $setvalue, 'time' => $updatetime));
                        // $this->mysqli->query("UPDATE feeds SET value = '$setvalue', time = '$updatetime' WHERE id='$setfeed'");
                                                break;
                    case 2:
                        // call url
                        $explodedUrl = preg_split('/[?]+/', $row['callcurl'],-1);
                        if (count($explodedUrl) > 1){
                           $explodedUrl[1] =  str_replace(' ', '%20', str_replace('{value}', $value, str_replace('{feed}', $feedData->name, $explodedUrl[1])));
                        }
                        $ch = curl_init();
                        $body = $explodedUrl[0] . '?' . $explodedUrl[1];
                        // set URL and other appropriate options
                        curl_setopt($ch, CURLOPT_URL, $body);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 1);

                        // grab URL and pass it to the browser
                        if(curl_exec($ch) === false){
                            error_log("Curl Error:".curl_error($ch));
                        }
                        // close cURL resource, and free up system resources
                        curl_close($ch);
		                error_log("Curl Log:".$body);


                        break;
                    case 3:
                        // Twitter
                        require_once(realpath(dirname(__FILE__)).'/../event/scripts/twitter/twitter-api-php/TwitterAPIExchange.php');
                        $twitter = $this->get_user_twitter($userid);

                        // Twitter disallow duplicate tweets within an unspecified and variable time per account
                        // so add the feed time to make each tweet unique.
                        $message = $message.' at '.date("H:i:s", $feedtime);;

                        // Set the OAauth values
                        $settings = array(
                            'oauth_access_token' => $twitter['usertoken'],
                            'oauth_access_token_secret' => $twitter['usersecret'],
                            'consumer_key' => $twitter['consumerkey'],
                            'consumer_secret' => $twitter['consumersecret']
                        );

                        // Make the API call
                        $url = 'https://api.twitter.com/1.1/statuses/update.json';
                        $requestMethod = 'POST';
                        $postfields = array(
                            'status' => $message );
                        $tweet = new TwitterAPIExchange($settings);
                        echo $tweet->buildOauth($url, $requestMethod)
                                     ->setPostfields($postfields)
                                     ->performRequest();
                        break;
                    case 4:
                        // Prowl
                        require_once realpath(dirname(__FILE__)).'/scripts/prowlphp/ProwlConnector.class.php';
                        require_once realpath(dirname(__FILE__)).'/scripts/prowlphp/ProwlMessage.class.php';
                        require_once realpath(dirname(__FILE__)).'/scripts/prowlphp/ProwlResponse.class.php';
                        $prowl = $this->get_user_prowl($userid);

                        $oProwl = new ProwlConnector();
                        $oMsg 	= new ProwlMessage();

                    	$oProwl->setIsPostRequest(true);
                    	$oMsg->setPriority($row['priority']);

                    	$oMsg->addApiKey($prowl['prowlkey']);

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
                    case 5:
                        // NMA
                        require_once realpath(dirname(__FILE__)).'/scripts/nma/nmaApi.class.php';

                        $nmakey = $this->get_user_nma($userid);

                        $nma = new nmaApi(array('apikey' => $nmakey['nmakey']));

                        $priority = $row['priority'];

                        if($nma->verify()){
                            $nma->notify('EmonCMS '.$message, 'EmonCMS', $message, $priority);
                        }


                        break;
                }
            // update the lasttime called
            if(!$test){
                $this->mysqli->query("UPDATE event SET lasttime = '".time()."' WHERE id='".$row['id']."'");
            }


            }
        }
    }
}

