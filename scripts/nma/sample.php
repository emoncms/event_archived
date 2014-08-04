<?php
/**
 *
 * User: paul
 * Date: 31/03/12
 * Time: 04:55
 * To change this template use File | Settings | File Templates.
 */
require dirname(__FILE__).'/nmaApi.class.php';

$nma = new nmaApi(array('apikey' => '2091a3e66a408e6a7ac9081927e85c98ff586c7c0d13fdff'));



if($nma->verify()){
    if($nma->notify('My Test', 'New Gizmo', 'Kinda cool, php to my droid... nice http://openenergymonitor.org/emon/sites/default/files/imagecache/avatar/pictures/picture-2125.jpg')){
        echo "Notifcation sent!";
    }
}


