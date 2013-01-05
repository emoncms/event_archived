<?php
/**
* post_tweet.php
* Posting a tweet with OAuth
* Latest copy of this code: 
* http://140dev.com/twitter-api-programming-tutorials/hello-twitter-oauth-php/
* @author Adam Green <140dev@gmail.com>
* @license GNU Public License
*/

$tweet_text = urldecode($argv[1]);
print "Posting...\n";
$result = post_tweet($tweet_text);
print "Response code: " . $result . "\n";

function post_tweet($tweet_text) {

  // Use Matt Harris' OAuth library to make the connection
  // This lives at: https://github.com/themattharris/tmhOAuth
  require_once('oAuth/tmhOAuth.php');
      
  // Set the authorization values
  // In keeping with the OAuth tradition of maximum confusion, 
  // the names of some of these values are different from the Twitter Dev interface
  // user_token is called Access Token on the Dev site
  // user_secret is called Access Token Secret on the Dev site
  // The values here have asterisks to hide the true contents 
  // You need to use the actual values from Twitter
    $writeconnection = new tmhOAuth(array(
    'consumer_key' => $row['consumerkey'],
    'consumer_secret' => $row['consumersecret'],
    'user_token' => $row['usertoken'],
    'user_secret' => $row['usersecret'],
  )); 

  // Make the API call
  $writeconnection->request('POST', 
    $writeconnection->url('1/statuses/update'), 
    array('status' => $tweet_text));
  
  return $writeconnection->response['code'];
}
?>
