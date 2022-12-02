<?php
session_start();
require_once("config-pdo.php");
//require_once ("SmsInterface.inc");
require_once("sendmail.php");
require_once("notifyfunc.php");
require_once("crypt-pdo.inc.php");
require_once("aws.php");

/*
 * Implementation Notes
 * Step 1 is forgotreq.php which prompts for username or handle
 * Step 2 is forgotemail which then sends email or text for reset request
 * Step 3 is frset which is the actual reset to the password (temporary password)
 */

    $_SESSION['returnurl']="<a href='login.php'>Login</a>";
    $temp = uniqid();
    $_SESSION['temporarypassword'] = substr( $temp, 5, 8 );
    $session = session_id();
    
    $userid = @tvalidator("PURIFY", "$_POST[pid]");
    $loginid = @tvalidator("PURIFY", "$_POST[l]");
    $ip = tvalidator("PURIFY",$_SERVER['REMOTE_ADDR']);
    //$ip = "test";
    

    $providerid = "0";
    //Validate the User
    if( strpos( (string) $userid,"@")!==false ){
    
        $result = pdo_query("1", 
           "select providerid, verified, replyemail, handle from 
            provider where (
            replyemail = ? or handle=?) 
            and active='Y'  ",array($userid,$userid)
          );
        if($row = pdo_fetch($result)){
            $providerid = $row['providerid'];
            $replyemail = $row['replyemail'];
            $handle = $row['handle'];
            
            
        } else {
            $providerid = "0";
            $replyemail = "";
            $handle = "";
            echo "User not found";
            exit();
        }
        
    } else {
        echo "Enter an email or @braxusername";
        exit();
    }
    

    //Validation Checks
    if( $providerid == "0" ){
    
        echo "Invalid Subscriber";
        exit();
    }
    if( $loginid == "" ){
        $loginid = "admin";
    }
    
    $result = pdo_query("1", 
            "
             select count(*) as count from forgotlog where datediff(createdate, now())= 0
             and ip = ? 
            ",array($ip)
          );
    if($row = pdo_fetch($result) ){
        if( intval($row['count'])  > 500 ){
            echo "Daily Limit of Forgot Password Utility Reached";
            exit();
        }
    }
    
    $smssent = false;
    $braxemail = "";
    
    $result = pdo_query("1", "
        select userid from braxmail where braxmail.providerid = ? 
      ",array($providerid));


    if ($row = pdo_fetch($result)){

        //NOT FOR OPEN SOURCE
        if($row['userid']!==''){
            $braxemail = $row['userid']."@braxmail.net";
        }
    }
    
    
    /*
     * This part is crashing
    $result = pdo_query("1", "
        select providerid, 
        (select sms from sms where provider.providerid = sms.providerid ) as smsencrypted,
        (select encoding from sms where provider.providerid = sms.providerid ) as smsencoding,
        (select userid from braxmail where braxmail.providerid = provider.providerid) as braxemail
        from provider where providerid=? and active='Y'  
      ",array($providerid));


    if ($row = pdo_fetch($result)){

        $sms = "";
        if($row['smsencrypted']!=''){
            $sms = DecryptText($row['smsencrypted'],$row['smsencoding'],$row['providerid']);
        }
        
        if( strlen($sms) > 2){
            $smsmessage = "$appname Password Reset $rootserver/$installfolder/frset.php?pid=$providerid&l=$loginid&s=$session ";
            SmsAlert( $providerid, $smsmessage, $sms );
            $smssent = true;
        }
        
        //NOT FOR OPEN SOURCE
        $braxemail = "";
        if($row['braxmail']!==''){
            $braxemail = $row['braxemail']."@braxmail.net";
        }
    }
    echo "test";
    exit();
    */
    if( strstr($replyemail, ".account@brax.me")!==false && $braxemail==''){
        //SMS sent so no need to bother with invalid email
        if($smssent){
            echo "Password Reset sent via text";
            exit();
        }
            
        echo "This user has no email address assigned and no mobile phone. You cannot reset this password";
        exit();
        
    }

    
    //Those with a Braxmail Subscription are presumed to be verified
    
    $result = pdo_query("1", 
            
            "SELECT provider.replyemail, staff.email, provider.verified, sms.sms
                from staff 
                left join provider on provider.providerid = staff.providerid
                left join sms on provider.providerid = sms.providerid
                where staff.providerid = ? and staff.loginid = ?  
                and (
                  staff.email in 
                     ( select email from verification where verifieddate is not null 
                       and staff.email = verification.email
                     )
                   or
                  '$braxemail'!=''
                )
            ",array($providerid,$loginid)
    );
    
    if ($row = pdo_fetch($result)){
    
                
                $_SESSION['message'] = 
                        "Did you forget your $appname password? You made a request for a One-Time-Use Password. ".
                        "If you did not make this request, you can ignore this message. ".
                        "\r\n\r\n" .
                        "If you made the request, please click the link below to receive the single use password. ".
                        "Once you log in, change your password immediately.\r\n\r\n".
                        "<br><br>
                         <a href='$rootserver/$installfolder/frset.php?pid=$providerid&l=$loginid&x=$session'>
                             Send me a One-Time-Use Password
                         </a>";
                
                //$to = "$row[email]";
                //Modified to take from Main account for now
                //the other way was enterprise multi-login use
                $to = "$row[replyemail]";
                $subject = "$appname Security Message";
                $message = "$_SESSION[message]";
                $from = "donotreply@brax.me";
                $headers = "From: '$appname' <$from>\r\n";
                
                SendMail("0", "$subject", "$message", "$message", "$to", "$to" );
                if($braxemail!==''){
                    SendMail("0", "$subject", "$message", "$message", "$braxemail", "$braxemail" );
                }
                
                if($smssent == false ){
                    echo "Email sent with a One-Time-Use Password Request.";
                } else {
                    echo "Email and Text sent with a One-Time-Use Password Request.";
                    
                }
    }
    else {
                if(!$smssent){
                    echo "Valid Email ($providerid) not Found. ".
                         "You likely did not verify your email address thus we are unable to confirm your identity. ".
                         "If you forgot your password, you will need to set up a new account. ". 
                         "You can contact Tech Support using your new new account for additional help. ";
                } else {
                    echo "Password Reset sent via text";
                    
                }
        
    }
    function SmsAlert( $providerid, $textmessage, $sms )
    {
        global $rootserver;
        global $installfolder;
        
        //Exclude Invalid Phone Numbers
        
        //If US Phone Number not 10 digit - error
        if(strlen($sms)!=10 && $sms[0]!='+' ){
            return false;
        }
        if(strlen($sms)!=12 && $sms[0]='+' && $sms[1]='1' ){
            return false;
        }
        
        if($textmessage=='' || $sms == '') {
            return false;
        }


        $message = stripslashes($textmessage);
       
        if($sms[0]!='+') {
            $sms = "+1".$sms;
        }
        $notifytype = 'FG';
        
        publishSMSNotification( "BraxMe", $message, $sms );
        
        pdo_query("1","insert into smslog (providerid, recipientid, sms, sentdate, source) "
                . "values (?, ?, ?, now(),? )",
                array(
                    $providerid,$providerid,$sms,$notifytype
                ));
        return true;
    }        
?>