<?php
session_start();
require_once("config-pdo.php");
require_once("profanity.php");
$userid = @tvalidator("PURIFY",$_REQUEST['userid']);
$mode = @tvalidator("PURIFY",$_REQUEST['mode']);

if($userid == ''){
    echo "Userid is blank";
    exit;
}
if($mode=='W'){
    ModerationOneDayDelete($userid);
    echo "$result";
}
if($mode=='H'){
    $result = ModerationHardRestrict($userid);
    echo "$result";
    exit();
}
if($mode=='P'){
    $result = ModerationProfileRestrict($userid);
    echo "$result";
    exit();
}
if($mode=='S'){
    $result = ModerationShadowBan($userid);
    echo "$result";
    exit();
}
if($mode=='R'){
    $result = ModerationRestrict($userid);
    echo "$result";
    exit();
}
if($mode=='I'){
    $result = Inactivate($userid);
    echo "$result";
    exit();
}
if($mode=='A'){
    $result = Activate($userid);
    echo "$result";
    exit();
}
if($mode=='IP'){
    $result = ModerationIpRestrict($userid);
    echo "$result";
    exit();
}
if($mode=='ND'){
    $result = ModerationFixNotifyBug($userid);
    echo "$result";
    exit();
}

echo "OK $mode-";

exit();