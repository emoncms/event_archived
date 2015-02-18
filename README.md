event
=====

Setup actions to occur when a feed goes above, below or is equal a set value. Set another feed or send an email.

Prowl, NMA, Curl, Twitter, MQTT and Email methods native to application.

Email currently is biased towards gmail SMTP server.

Requirements

Run the next command at your (linux) shell to ensure that PHP5 scripts can run:
`sudo apt-get install php5-mcrypt php5-curl`

Then login into your EmonCMS modules home directory:
cd /var/www/emoncms/Modules

Close this git with:
sudo git clone https://github.com/emoncms/event.git

Login into your EmonCMS server with admin rights and you should find Event under the new "Extras" menu.
Go to Admin menu and update the database first to commit the new tables
Now you can use the event module

NMA - Notify my android

NMA is a message API for sending the events to your android phone or tablet.
You need to register at: https://www.notifymyandroid.com/ and make an API key.
Second you have to enter the API key at the event settings (at emoncms).
Beware that you have a treshold of only 5 messages a day with a trial account!

MQTT will require access to an MQTT broker. (like: http://mosquitto.org/ for a raspberry pi).
MQTT (Telemetry Transport) is a protocol designed for sending telemetry from devices in situations where bandwidth is very limited. Because of this, is an extremely light-weight protocol, and at a mere 42 pages the protocol specification is light-weight too.
