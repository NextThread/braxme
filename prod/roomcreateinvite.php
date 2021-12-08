<?php
session_start();
require_once("config-pdo.php");

    //$replyflag = tvalidator("PURIFY",$_POST[replyflag]);
    $providerid = tvalidator("ID",$_POST['providerid']);

    $mode = '';
    if(isset($_POST['mode'])){
        $mode = tvalidator("PURIFY",$_POST['mode']);
    }
    
    
    
    $roomid = '';
    if(isset($_POST['roomid'])){
        $roomid = tvalidator("ID",$_POST['roomid']);
    }
    
    $result = pdo_query("1",
        "select private, external from roominfo where roomid=? "
        ,array($roomid));
    if($row = pdo_fetch($result)){
    
        $private = $row['private'];
        $external = $row['external'];
    }
    
    
    $uniqid2 = substr(uniqid(),4,8);
    $uniqid = str_replace('=','',base64_encode("$uniqid2"));

    $result = pdo_query("1",
        "select handle from roomhandle where roomid=? "
        ,array($roomid));
    if($row = pdo_fetch($result)){
    
        $handle = $row['handle'];
        $handleshort = substr($row['handle'],1);
    }
    
    if($mode == ''){
        $action = 'room';
    } else {
        $action = 's';
    }
    if($external == 'Y'){
        $action = 'home';
    }
    if($handleshort == ''){
        $private = 'Y';
    }
    
    if($private == 'Y'){
    
        $sharelink = "$rootserver/j/$uniqid";
        pdo_query("1"," 
            insert into roominvite (roomid, inviteid, expires, status )
            values (?, '$uniqid', date_add(now(),INTERVAL 2 DAY), 'Y')
              ",array($roomid));
    } else {
        
        $sharelink = "$rootserver/$action/$handleshort";
        
    }
    
echo "$sharelink";
    
?>
