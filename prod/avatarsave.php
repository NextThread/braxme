<?php
session_start();
require_once("aws.php");
require_once("config-pdo.php");

require_once("htmlhead.inc.php");
require_once("password.inc.php");
require_once("signupfunc.php");


?>
<script>
        $(document).ready( function() {
        });
</script>
</head>
<?php

    $providerid = tvalidator("ID","$_SESSION[pid]");

    $avatarurl = tvalidator("PURIFY","$_POST[avatarurl]");
    
    $avatarurl = HttpsWrapper($avatarurl);

    if( $avatarurl!=''){
        
        $result = pdo_query("1", 
                " update provider set avatarurl='$avatarurl',t_avatarurl='' where providerid=$providerid ",null
                );
        SaveThumbnail($avatarurl, $providerid);
        echo "Saved";
    }
        

    
function SaveThumbnail($filename, $providerid)
{
    $targetfolder = "upload-zone/files/";
    if(!is_writable ( $targetfolder )  ){          

        echo "Can't write to $targetfolder";
        return;
    }

    $uniqid = uniqid("",false);

    $f = explode("_",$filename);
    $e = explode(".",$filename);
    $ext = strtolower($e[count($e)-1]);
 
    //New File Name format Prefix_Prefix2_uniqueid
    $image = "";
    if( strstr(strtolower($filename),"sharedirect.php")!==false &&
        (
        strstr(strtolower($filename),"a=")!==false                
        )
      ){
        $braxfilename = explode("a=",$filename);
        //Alias
        if($braxfilename[1]!==''){
            $result2 = pdo_query("1",
            "
                select filename,filetype from photolib where alias='$braxfilename[1]'
            ",null);

            if($row2 = pdo_fetch($result2)){
                $a_filename = $row2['filename'];             
                $a_ext = $row2['filetype']; 
                $url = getAWSObjectUrl($a_filename);
                if($a_ext == 'jpg'){
                    $image = imagecreatefromjpeg($url);
                } else
                if($a_ext == 'png'){
                    $image = imagecreatefrompng($url);
                } else
                if($a_ext == 'gif'){
                    $image = imagecreatefromgif($url);
                }
                $ext = $a_ext;
            }
        }
    } else        
    if( strstr(strtolower($filename),"sharedirect.php")!==false &&
        (
        strstr(strtolower($filename),".jpg")!==false ||               
        strstr(strtolower($filename),".jpeg")!==false                
        )
      ){
        $braxfilename = explode("=",$filename);
        $url = getAWSObjectUrl($braxfilename[1]);
        $image = imagecreatefromjpeg($url);
        $ext = "jpg";
    } else
    if( strstr(strtolower($filename),"sharedirect.php")!==false &&
        (
        strstr(strtolower($filename),".png")!==false                
        )
      ){
        $braxfilename = explode("=",$filename);
        $url = getAWSObjectUrl($braxfilename[1]);
        $image = imagecreatefrompng($url);
        $ext = "png";
    } else
    if(strstr(strtolower($filename),".png")!==false){
        $image = imagecreatefrompng($filename);
    } else
    if(strstr(strtolower($filename),".jpg")!==false){
        $image = imagecreatefromjpeg($filename);
    } else
    if(strstr(strtolower($filename),".jpeg")!==false){
        $image = imagecreatefromjpeg($filename);
    } else
    if(strstr(strtolower($filename),".gif")!==false){
        $image = "";
    }
    $tempfilename= $providerid."_avatar_".$uniqid.".$ext";

    if($image==''){
        pdo_query("1","
            update provider set t_avatarurl = '$rootserver/img/newbie2.png'
                where providerid=? 
                ",array($providerid));

    }
    if($image!==''){
        $image = imagescale($image,250,250);
        imagejpeg($image, $targetfolder.$tempfilename);

        putAWSObject($tempfilename, $targetfolder.$tempfilename);
        $awsurl = getAWSObjectUrl($tempfilename);
        pdo_query("1","
            update provider set t_avatarurl = ?
                where providerid=? 
                ",array($awsurl, $providerid));
        
        $album = "Profile Photo";
        $alias = uniqid("T4AZ", true);
        $result = pdo_query("1", 
                "
                    insert into photolib
                    ( providerid, album, filename, folder, filesize, filetype, title, createdate, alias, owner )
                    values
                    ( ?, ?, ?, ?,?, ?,?, now(),?, ? ) 
                 ",array(
                     $providerid, $album, $tempfilename, $targetfolder,$filesize, $ext,"", $alias, $providerid  

                 ));
    }
        

}
?>
