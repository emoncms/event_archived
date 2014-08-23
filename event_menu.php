<?php

    $domain = "messages";
    bindtextdomain($domain, "Modules/event/locale");
    bind_textdomain_codeset($domain, 'UTF-8');

    $menu_left[] = array('name'=> dgettext($domain, "Events"), 'path'=>"event/view" , 'session'=>"write", 'order' => 2 );