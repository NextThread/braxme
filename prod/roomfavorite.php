<?php 
session_start();
require("validsession.inc.php");
include("config-pdo.php");

/* This Routine is for performing automated processes after doing termsofuse agree */


$mode = @tvalidator("PURIFY", $_POST['mode'] );
$providerid = @tvalidator("ID",$_SESSION['pid']);
$roomid = @tvalidator("PURIFY", $_POST['roomid'] );

if($mode == 'A'){
    pdo_query("1","
        insert ignore into roomfavorites (providerid, roomid ) values (?,? )
             ",array($providerid,$roomid));
}
if($mode == 'D'){
    pdo_query("1","
        delete from roomfavorites where providerid = ? and roomid = ?
             ",array($providerid,$roomid));
}
