<?php
session_start();
require_once("config-pdo.php");

$url = @tvalidator("PURIFY",$_GET['u']);
if(substr(strtolower($url),0,7)!=='http://' ){
    $url = "http://".$url;
}
header("Location: $url"); 
exit();
header("Content-Type: html");
header("Content-Disposition: inline; filename='$url'");

echo file_get_contents("$url");
exit();


