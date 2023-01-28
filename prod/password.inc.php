<?php
require_once("config-pdo.php");
require_once("sysdown.php");

require_once("crypt-pdo.inc.php");
require_once("notifyfunc.php");
require_once("roommanage.inc.php");
require_once("whitelist.inc.php");
require_once 'authenticator/GoogleAuthenticator.php';

/* NOTE: for Open source version - remove references to hgtx. That is for production only */



    $roomhandle = '';
    $roomstorehandle = '';
    $_SESSION['signupprovider']='';
    //$_SESSION['loginid']='admin';
    $_SESSION['imapcount']=0;
    $_SESSION['mobile']='';
    $_SESSION['timeout_seconds']=600;
    $_SESSION['pinlock']='';    
    
    $_SESSION['hostedmode']='false';
    $_SESSION['hostedroomid'] =0;
    $_SESSION['hostedcolorscheme'] ='';
     
    if(true){

        /******************************************
         * 
         * Has User Logged In? Exit Otherwise
         * 
         ******************************************/
        
        
        if(  ( !isset($_SESSION['pid']) && !isset($_POST['pid']) ) || 
             ( !isset($_SESSION['logintoken']) && !isset($_POST['password']) &&
               !isset($_SESSION['pwd_hash'] )
             )
          )
        {
            $enterprise = @$_SESSION['enterprise'];
            echo "<html>";
            echo "<meta http-equiv='refresh' content='0;url=$rootserver/$startupphp'/>";       
            
            echo "</html>";
            exit();
        }

        /******************************************
         * 
         * If No Session, then User just logged in
         * 
         ******************************************/
        if(isset($_SESSION['pid'])){
            $pid = @$_SESSION['pid'];
            $password = '';
        } else {
            //If user just logged in, this is @username, not account
            $pid = $_POST['pid'];
            
        }


        /******************************************
         * 
         * Something Incomplete
         * 
         ******************************************/
        

        if(  $pid =='' ){
        
            if(isset($_SESSION['fails'])){
                $_SESSION['fails']=intval($_SESSION['fails'])+1;
            } else {
                $_SESSION['fails']=0;
            }
            if( intval( $_SESSION['fails'] > 10 )){

                echo "<html>";
                echo "<meta http-equiv='refresh' content='10;url=http://www.hhs.gov/ocr/privacy/'/>";       
                echo "<body>";
                echo "Invalid Login Credentials - Please enter complete Login Info.<br>";
                echo "$_SESSION[returnurl]";
                echo "<br>";
                echo "</body></html>";
                exit();

            } else {

                echo "<html>";
                echo "No User Account";
                //echo "<meta http-equiv='refresh' content='0;url=$rootserver/$startupphp'/>";       
                echo "</html>";
                exit();

            }

        }
        
        /******************************************
         * 
         * We Have Credentials (though unvalidated
         * 
         ******************************************/


        if( isset($_SESSION['pid'])){

            $pid = rtrim(tvalidator("PURIFYHANDLE",$_SESSION['pid']));
            $loginid = rtrim(@tvalidator("PURIFYHANDLE", "$_SESSION[loginid]"));
            $password = "";
            if($loginid == ''){
                $logind = 'admin';
            }

            $already_logged_in = true;
            

        } else {

            $_SESSION['timeoutcheck']=time();

            $pid = rtrim(tvalidator("PURIFYHANDLE","$_POST[pid]"));
            $_SESSION['pid'] = tvalidator("PURIFYHANDLE",$_POST['pid']);
            
            
            $_SESSION['logintoken']=session_id();
            $_SESSION['pwd_hash'] = session_id();
            $_SESSION['loginid'] = tvalidator("PURIFYHANDLE",$_POST['loginid']);
            $_SESSION['init'] = tvalidator("PURIFYHANDLE",$_POST['init']);
            if(!isset($_SESSION['version'])){
                $_SESSION['version']='000';
            }
            if(isset($_POST['version']) && $_POST['version']!=''){
                $_SESSION['version'] = tvalidator("PURIFYHANDLE",$_POST['version']);
            }
            
            $roomhandle = tvalidator("PURIFYHANDLE",$_POST['roomhandle']);
            $roomstorehandle = tvalidator("PURIFYHANDLE",$_POST['roomstorehandle']);
            $timezone = tvalidator("PURIFY",$_POST['timezone']);
            if($timezone!=''){
                pdo_query("1","update provider set timezone=? where handle = ?  ",array($timezone,$pid));
                $_SESSION['timezone']=$timezone;
            }
            

            if($_SESSION['loginid'] == ''){
                $_SESSION['loginid'] = 'admin';
                $loginid = 'admin';
            }

            //Load Existing Device ID
            $_SESSION['deviceid'] = tvalidator("PURIFYHANDLE",$_POST['deviceid']);
            
            //banned user flag
            $hgtx = tvalidator("PURIFYHANDLE",$_POST['hgtx']);
            if($hgtx ==='1'){
                $_SESSION['hgtx']='1';
                
            }
            

            //Anti CSRF
            $_SESSION['remote_addr'] = $_SERVER['REMOTE_ADDR'];
            $loginid = $_SESSION['loginid'];

            $password = rtrim(escape_for_sql( "$_POST[password]"));
            
            //Password in Local Storage
            $clientstoredpassword = tvalidator("PURIFY",$_POST['stored']);
            if($clientstoredpassword!=''){
                $password = OpenSSLDecrypt($clientstoredpassword,$serverencryptionkey);
            }
            
            $jspassword = OpenSSLEncrypt($password,$serverencryptionkey);
            
            echo "
                <script>
                localStorage.hswt = '$jspassword';
                localStorage.removeItem('swt');
                localStorage.pidl = '$pid';
                </script>
            ";
            //echo "<body>Logging in</body>";
            $password = purifytext($password);

            //Save New Device ID
            if($_SESSION['deviceid']==''){

                $deviceid = uniqid();

                echo "
                    <script>
                    localStorage.deviceid = '$deviceid';
                    </script>
                ";
                $_SESSION['deviceid'] = $deviceid;
            }

            $already_logged_in = false;
            
        }


        /******************************************
         * 
         * Convert @username to PID / Convert HANDLE to PID
         * 
         ******************************************/
        $providerid =  ConvertPidToAccount( $pid );
        //From Here on this is now a numeric pid.
        $_SESSION['pid'] = $providerid;
        
        
        AddLoginToRoom($providerid, $roomhandle, $roomstorehandle);

        /******************************************
         * 
         * Password Validation
         * 
         ******************************************/

        if(!$already_logged_in){

            if(!ValidatePassword($providerid, $loginid, $password )){
                
                $_SESSION['pid']='';
                
                PasswordFailed($providerid, $loginid, $password );
                //ValidationFailed - check to see if this is a TOTP token
                exit();
            } else {
                
            }
            InitializeLanguage($providerid);
            InitializeAccountSessionVars($providerid, $loginid);



        }


        if($already_logged_in){
            
            if( $_SESSION['logintoken']!=session_id()){

                echo "Security Token Error ($providerid)<br>";
                exit();
            }
            InitializeAccountSessionVars($providerid, $loginid);


        }

        /******************************************
         * 
         * Password Validated Zone
         * 
         ******************************************/

        $_SESSION['fails']=0;

        //Password is Valid Now
        $result = pdo_query("1", 
           " 
            update staff
            set
            lastaccess = now()
            where providerid= ? and loginid= ? 
            and active='Y'",
            array($providerid, $loginid)     
            
          );
        
        //Password is Valid Now
        $result = pdo_query("1", 
           " 
            update provider
            set
            lastaccess = now()
            where providerid=?  
            and active='Y'  ",
           array($providerid)
          );
        


        /******************************************
         * 
         * Miscellaneous Cleanup
         * 
         ******************************************/

    }
    
    
    function ValidatePassword($providerid, $loginid, $password )
    {
        global $rootserver;
        global $installfolder;
        global $appname;
        global $applogo;
        global $startupphp;
        
        $password_valid = false;
        $enterprise = @$_SESSION['enterprise'];
        $result = pdo_query("1", 
           " 
            select adminright, staffname, pwd_ver, pwd_hash, fails, onetimeflag, auth_hash, encoding
            from staff 
            where providerid=? and loginid=? 
            and pwd_ver = 3 
            and active='Y' ",
           array($providerid, $loginid)     
            
          );
        if(!$result){
            return false;
        }
        if( $row = pdo_fetch($result)){

            $_SESSION['admin'] = $row['adminright'];
            $_SESSION['staffname'] = $row['staffname'];
            $_SESSION['onetimeflag']='';

            
            //Google Authenticator Validation
            if($row['auth_hash']!=''){

                $secret = DecryptText($row['auth_hash'], $row['encoding'], $providerid );
                //$secret = $row['auth_hash'];
                $ga = new PHPGangsta_GoogleAuthenticator();
                $checkResult = $ga->verifyCode($secret, $password, 2);    // 2 = 2*30sec clock tolerance
                if ($checkResult) {

                    $password_valid = true;
                    $_SESSION['onetimeflag']='Y';
                    $_SESSION['chgpassword']='Y';

                }                

            }
            
            //New Password Validation
            if( $password_valid == false && 
                password_verify(purifytext("$password"),"$row[pwd_hash]")){
            
                
                $password_valid = true;
                
                //Password is Valid - Now check for onetimeflag
                if($row['onetimeflag']=='Y'){
                    $_SESSION['onetimeflag']='Y';
                    $_SESSION['chgpassword']='Y';
                    
                    //Convert to New Password Format
                    $random_pwd = session_id();
                    $random_pwd_hash = password_hash("$random_pwd", PASSWORD_DEFAULT);

                    $result = pdo_query("1", 
                       " 
                        update staff
                        set
                        pwd_ver = 3,
                        pwd_hash = ?,
                        onetimeflag = ''
                        where providerid=? and loginid=?
                        and active='Y'  ",
                       array($random_pwd_hash,$providerid,$loginid)
                      );
                }
                
                
            }
        }
        
        if($password_valid == false){
            return $password_valid;
        }

            
        //Cleanup and Logging
        $_SESSION['staff']='';
        if($_SESSION['loginid']!='admin'){
            $_SESSION['staff'] = "$staffname";
        }

        $result = pdo_query("1", "
            update staff set fails = 0 where 
            providerid=? and loginid=?
            ", array($providerid, $loginid)
            );

        //Fingerprint Without IP
        /*
         *  The purpose of fingerprint is for troll control. If you block one user
         *  all users with the same fingerprint get blocked as well.
         *  just a simplistic fingerprint and no IP addresses are collected.
         * 
         */
        $timezone = $_SESSION['timezone'];
        $innerwidth =   $_SESSION['innerwidth'];
        $innerheight =  $_SESSION['innerheight'];
        $pixelratio = $_SESSION['pixelratio'];
 
        //banned user based on IP address currently used
        $ip = "";
        $iphash = WhiteListCheck(false);
        if($iphash!=='internal' && $iphash!=='whitelist'){
            $result = pdo_query("1", 
               " 
                select ban from iphash where ip=? and ban = 'Y'
               ",
               array($iphash)     

              );
            if( $row = pdo_fetch($result)){
                $_SESSION['hgtx']='1';
            }
            $ip = $iphash;
        } 
        
        
        
        $ipclean = WhiteListCheck(true);
        $useragent = tvalidator("PURIFY",$_SERVER['HTTP_USER_AGENT']);
        /* iphash = raw hasn of IP excluding TOR and Internal list or BytzVPN
         * iphash2 = fingerprint with ip
         * iphash3 = no ip but device fingerprint only with timezone
         * 
         */
        
        $iphash = hash("sha256", WhiteListCheck(2));
        $iphash2 = hash("sha256",$ip.$useragent.$timezone);
        $iphash3 = hash("sha256",$useragent.$timezone.$innerwidth.$innerheight.$pixelratio.$ipclean);
        $ipsource = $ipclean;

        $result = pdo_query("1", 
           " 
            select * from banhash where banid = ?
           ", array($iphash2)     
          );
        if( $row = pdo_fetch($result)){
            $password_valid = false;
            return $password_valid;
        }
        
        
        //Simplified Browser Fingerprint to catch trolls
        $result = pdo_query("1"," 
            update provider set iphash=?,iphash2 =?, iphash3=?, ipsource=?, timezone=?
             where providerid = ?",
             array($iphash,$iphash2,$iphash3, $ipsource,$timezone,$providerid)
            );

        GetTimeoutPin($providerid);
        
        return $password_valid;
        
    }
    
    
    function PasswordFailed($providerid, $loginid, $password )
    {
        global $rootserver;
        global $installfolder;
        global $appname;
        global $applogo;
        global $startupphp;
        
        $_SESSION['pwd_hash']='';
        $_SESSION['logintoken']='';
        $_SESSION['password']='';
        $_SESSION['pid']='';
        

        $result = pdo_query("1", 
           " 
            select fails, auth_hash
            from staff 
            where providerid=? and loginid=? 
            and pwd_ver = 3 
            and active='Y' 
           ", array($providerid, $loginid)     
          );
        if( $row = pdo_fetch($result)){
            
            if($row['fails']>10) {
               FailsError( $providerid, $row['fails'], $row['auth_hash'] );
               exit();
            }
            
        }

        
        //Security Breach Level!

        echo "<html><title>Login Message</title>";
        echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
        echo "<head><link rel='styleSheet' href='$rootserver/$installfolder/app.css' type='text/css'></head>";
        echo "<body class='appbody'>";
        echo "<img class='viewlogomsg' src='$applogo' style='height:40px'><br>";
        echo "<br>Username and Password could not be validated.<br>";
        //echo "<br><h2>$_SESSION[replyemail]</h2>";
        echo "<script>
              try {
              localStorage.removeItem('hswt');
              localStorage.removeItem('swt');
              localStorage.removeItem('password');
              localStorage.removeItem('pw');
              } catch (err) {}
              </script>
              ";

        if($_SESSION['mobile']=='Y'){
           echo "Restart the App from Home Screen";
        }  else  {
           echo "<br><br><a href='$rootserver/$startupphp?&h=&v='>Login to your Account</a>";
        }
        echo "<br><br>";


        //session_unset();
        //session_destroy();

        echo "</body></html>";
        
        //Increment Fails
        $result = pdo_query("1", "
            update staff set fails = fails+1 where providerid=? and loginid=? 
            ",array($providerid, $loginid));

        exit();
    }
    
    
    function ConvertPidToAccount( $providerid )
    {
        global $rootserver;
        global $installfolder;
        global $appname;
        global $roomhandle;
        global $version;
        global $startupphp;
        global $applogo;
        
        if($providerid == ''){
            return "";
        }
        
        if( $providerid[0]=="@" && strlen($providerid)>1 ){
        
            $result = pdo_query("1", 
               "select providerid from provider where handle = ? and handle!='@' and active='Y'  ",array($providerid)
              );
            if ($row = pdo_fetch($result)){ 
            
                $providerid = $row['providerid'];
            }
        } else
        if( strpos( (string) $providerid,"@")!==false ){
        
            $result = pdo_query("1", 
               "select providerid from provider where replyemail = ? and replyemail!='@' and active='Y'  ",array($providerid)
              );
            if ($row = pdo_fetch($result)){
            
                $providerid = $row['providerid'];
            }
            
        } else {
        }
        
        $enterprise = @$_SESSION['enterprise'];
        if( !is_numeric($providerid) || intval($providerid)==0){
        
                $_SESSION['pid']='';
                echo "<html class='appbody mainfont'><title>Login Message</title>";
                echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
                echo "<head><link rel='styleSheet' href='$rootserver/$installfolder/app.css' type='text/css'></head>";
                echo "<body style='padding:20px;background-color:whitesmoke'>";
                echo "<br><br><img class='viewlogomsg' src='$applogo' style='height:50px;'><br>";
                echo "<br><b>Username/Password</b> <br><b style='font-size:20px'>$providerid</b><br><b>not found</b><br><br><br>";
                echo "<a href='$rootserver/$startupphp?&h=$roomhandle&v=$version' style='text-decoration:none'><b>Back to Login</b></a>";
                echo "<script>
                      try {
                      localStorage.pid = '';
                      localStorage.loginid = 'admin';                  
                      localStorage.removeItem('swt');
                      localStorage.removeItem('password');
                      localStorage.removeItem('pw');
                      } catch (err) {}
                      </script>
                      ";
                echo "<br>";
                echo "</body></html>";
                exit();

        }
        
        
        return $providerid;
        
    }
    function FailsError( $providerid, $fails, $auth_hash )
    {
        global $rootserver;
        global $installfolder;
        global $appname;
        global $loginid;
        global $applogo;
        
            if( $fails > 10 ){
            
                echo "<html><title>Login Message</title>";
                echo "<meta name='viewport' content='width=device-width, initial-scale=1'>";
                echo "<head><link rel='styleSheet' href='$rootserver/$installfolder/app.css' type='text/css'></head>";
                echo "<body class='appbody'>";
                echo "<img class='viewlogomsg' src='$applogo' style='height:40px'><br>";
                echo "<br>Security Breach -- Too Many Incorrect Logins. <br><br>";
                echo "<script>
                        try {
                        localStorage.removeItem('swt');
                        localStorage.removeItem('pidl');
                        } catch (err) {};
                       </script>
                     ";


                $message = 
                        "Warning: Someone has attempted to Log in to $appname using your\r\n" .
                        "credentials but has entered the incorrect password over 10 times.\r\n\r\n" .
                        "If this is not you, please Log In immediately to $appname\r\n".
                        "and change your password.\r\n\r\n".
                        "If you are the one putting in an incorrect password\r\n".
                        "simply click on the 'Forgot Password?' link to reset to a\r\n".
                        " temporary password";

                $result = pdo_query("1","select replyemail from provider where providerid=?",array($providerid));
                if($row = pdo_fetch($result)){
                    $email = $row['replyemail'];
                }                
                
                if($auth_hash == ''){
                    pdo_query("1", " 
                        update staff set fails = fails-1 where providerid=?
                        and loginid=? 
                        ",array($providerid, $loginid ) );
                } 
                
                if(strstr($email,".account@brax.me")!==false){
                    //No email owner
                    exit();
                }
                /* Send Security Message to Owner */

                $to = "$row[replyemail]";
                $subject = "$appname Security Warning";
                $from = "donotreply@brax.me";
                $headers = "From: 'Brax.Me' <$from>\r\n";
                $mailstatus = mail( $to, $subject, $message, $headers);
                if( $mailstatus ){
                
                    echo "Email Sent to Login's Email Address warning of a Security Breach<br><br>";
                } else {
                
                    echo "Email Failed for $row[replyemail]. Email address is not valid.<br><br>";
                }
                $message = 
                        "Warning: Break-in Attempts to your $appname account\r\n".
                        "Username: $providerid\r\n".
                        "Login: $loginid\r\n".
                        "10+ Failed Logins\r\n".
                        "Authenticator Apps disabled\r\n";

                $to = "techsupport@brax.me";
                $subject = "$appname Break-In Attempt";
                $from = "techsupport@brax.me";
                $headers = "From: '$appname System' <$from>\r\n";
                $mailstatus = mail( $to, $subject, $message, $headers);

                #echo "$_SESSION[returnurl]";
                echo "<br>";
                echo "</body></html>";



                exit();

            }
    }
    
    function InitializeAccountSessionVars($providerid, $loginid)
    {
        global $rootserver;
        
        GetSentKeys($providerid);
        
        $result = pdo_query("1","
               select adminright from staff where loginid=? and providerid = ? ",
                array($loginid, $providerid)
            );
        if ($row = pdo_fetch($result)){ 
            $_SESSION['admin'] = $row['adminright'];
        }
                   
        
        
        $result = pdo_query("1", 
            "
               select provider.restricted, provider.verified, provider.superadmin, 
               provider.techsupport, provider.cookies_sender, provider.inactivitytimeout, 
               provider.replyemail, provider.avatarurl, provider.accountstatus, provider.providername, 
               provider.menustyle, provider.handle, provider.featureemail, 
               provider.enterprise, provider.enterprisehost, provider.industry,
               provider.companyname, provider.active, provider.age, 
               provider.lasttip, provider.invitesource, provider.sponsor,
               provider.wallpaper, provider.language, provider.banid,
               provider.termsofuse, provider.chgpassword, provider.devicecode, provider.blindsound, provider.msglifespan,
               provider.costexempt, provider.roomdiscovery, 
               provider.roomcreator, provider.broadcaster, provider.store, provider.web,
               datediff(now(),provider.createdate) as daysactive,
               provider.profileroomid, provider.colorscheme,
               (select enterprisename from sponsor where sponsor.sponsor = provider.sponsor ) as sponsorname,
               (select colorscheme from sponsor where sponsor.sponsor = provider.sponsor ) as sponsorcolorscheme,
               (select live from sponsor where sponsor.sponsor = provider.sponsor ) as sponsorlive,
               ( select count(*) from sponsor 
                 where sponsor.creator = provider.providerid ) as sponsorcount,
               newbie, joinedvia, provider.allowiot, provider.hardenter, provider.email_service,
               (select accountid from bytzvpn where bytzvpn.providerid = provider.providerid and bytzvpn.status='Y' limit 1) as vpnaccountid
               from provider 
               left join timeout on provider.providerid = timeout.providerid
               where provider.providerid = ? and provider.active='Y' ",
                array($providerid)
            );
        if ($row = pdo_fetch($result)){ 
        
            if($row['active']=='N'){
            
                exit();
            }
            
            pdo_query("1","
                insert ignore into alertrefresh ( deviceid, providerid, lastnotified ) 
                values ( ?, ?, null );
                    ", array($_SESSION['deviceid'],$providerid));
            
            $_SESSION['companyname']='';
            if($row['companyname']!=''){
            
                $_SESSION['companyname']=$row['companyname'];
            }
            $_SESSION['newuser'] ='N';
            $_SESSION['pid']=$providerid;
            $_SESSION['providername']=$row['providername'];
            $_SESSION['menustyle'] = $row['menustyle'];
            $_SESSION['replyemail']=$row['replyemail'];
            $_SESSION['handle']=$row['handle'];
            $_SESSION['accountstatus']=$row['accountstatus'];
            $_SESSION['superadmin']=$row['superadmin'];
            $_SESSION['costexempt']=$row['costexempt'];
            $_SESSION['techsupport']=$row['techsupport'];
            $_SESSION['cookies_sender']=$row['cookies_sender'];
            $_SESSION['timeout_seconds'] = intval($row['inactivitytimeout']);
            $_SESSION['msglifespan'] = $row['msglifespan'];
            $_SESSION['devicecode'] = $row['devicecode'];
            $_SESSION['featureemail'] = $row['featureemail'];
            $_SESSION['banid'] = $row['banid'];
            $_SESSION['roomdiscovery'] = $row['roomdiscovery'];
            $_SESSION['roomcreator'] = $row['roomcreator'];
            $_SESSION['broadcaster'] = $row['broadcaster'];
            $_SESSION['email_service'] = $row['email_service'];
            $_SESSION['store'] = $row['store'];
            $_SESSION['web'] = $row['web'];
            $_SESSION['newbie'] = $row['newbie'];
            $_SESSION['joinedvia'] = $row['joinedvia'];
            $_SESSION['daysactive'] = $row['daysactive'];
            $_SESSION['sponsorcount'] = intval($row['sponsorcount']);
            $_SESSION['allowiot'] = $row['allowiot'];
            $_SESSION['hardenter'] = $row['hardenter'];
            if($row['age']=='') {
                $row['age']='0';
            }
            $_SESSION['age']=$row['age'];
            $_SESSION['lasttip']=$row['lasttip'];
            $_SESSION['invitesource']=$row['invitesource'];
            $_SESSION['language']=$row['language'];
            $_SESSION['wallpaper']=$row['wallpaper'];
            $_SESSION['sponsor']=$row['sponsor'];
            $_SESSION['sponsorname']=$row['sponsorname'];
            $_SESSION['sponsorcolorscheme']=$row['sponsorcolorscheme'];
            CleanUpSponsor($providerid, $row['sponsor'], $row['sponsorname']);
            $_SESSION['colorscheme']=$row['colorscheme'];
            if($row['colorscheme']==''){
                $_SESSION['colorscheme']='std';
            }
            if($row['sponsorcolorscheme']!='' && $row['sponsorcolorscheme']!='std'){
                $_SESSION['colorscheme']=$row['sponsorcolorscheme'];
            }
            $_SESSION['hostedcolorscheme']='';
            $_SESSION['livesupport']='Y';
            if($row['sponsor']!='' && $row['sponsorlive']==''){
                $_SESSION['livesupport'] = 'N';
            }
            
            
            $_SESSION['invitesource']='';
            $_SESSION['industry'] = $row['industry'];
            
            $_SESSION['mobilesize']="";
            $_SESSION['mobiletype']="";            
            $_SESSION['profileroomid'] = $row['profileroomid'];
            if(intval($row['profileroomid'])==0){
                $_SESSION['profileroomid'] = NewProfileRoom($providerid);
            }
            //if( $row[inactivitytimeout]==0)
            //    $_SESSION[timeout_seconds] = 3600;
            //$_SESSION[timeout_short_seconds] = 120;
            //if( $_SESSION[timeout_seconds] < $_SESSION[timeout_short_seconds])
            $_SESSION['timeout_short_seconds'] = $_SESSION['timeout_seconds'];
            $_SESSION['avatarurl']=$row['avatarurl'];
            if($row['avatarurl']=="$rootserver/img/faceless.png"){
               //$_SESSION['avatarurl'] = "$rootserver/img/newbie2.jpg"; 
            }
            if($row['avatarurl']==""){
               $_SESSION['avatarurl'] = "$rootserver/img/newbie.jpg"; 
            }
            $_SESSION['verified']=$row['verified'];
            if( !isset($_SESSION['onetimeflag']) ||  $_SESSION['onetimeflag']!='Y'){
                $_SESSION['chgpassword']=$row['chgpassword'];
            }
            
            $_SESSION['termsofuse']= $row['termsofuse'];
            if($_SESSION['termsofuse']==''){
            
                $_SESSION['termsofuse']='N';
            }

            $_SESSION['ping'] = '';
            $_SESSION['ping2'] = '';
            if($row['blindsound']=='1' ){
                $_SESSION['ping'] = "../img/cork.wav";
                $_SESSION['ping2'] = "../img/ebs.wav";
            }
            
            
            
                $_SESSION['enterprise']='Y';
                
            if($_SESSION['enterprise']=='Y' && $row['enterprise']!=='Y'){
            
                
            }
            $_SESSION['enterprise']=$row['enterprise'];
            $_SESSION['enterprisehost']=$row['enterprisehost'];
            $_SESSION['vpnaccountid']=$row['vpnaccountid'];
            

            if(isset($_POST['mobile'])){
                $_SESSION['mobile'] = @tvalidator("PURIFY",$_POST['mobile']);
            }
            //Initialize Only
            $_SESSION['mobilesize']='Y';
            
           if( $row['verified']!='Y'){
               //Future Warning for unverified email
               
           }
           if( $row['restricted']=='Y'){
               //Slow down trolls
                echo "
                    <script>
                    localStorage.hgtx = '1';
                    </script>
                ";
               
           }   
           
           
            
        }
        
        
        
        
    }
    function CleanUpSponsor($providerid, $sponsor, $sponsorname)
    {
        //Remove Invalid Sponsor Codes
        if($sponsor!='' && $sponsorname == ''){
            $_SESSION['sponsor']='';
            $_SESSION['sponsorname']='';
            $_SESSION['sponsorcolorscheme']='';
            pdo_query("1","update provider set sponsor='' where providerid = ?",array($providerid));
            
        }
        
    }
    

function InitializeLanguage($providerid)
{
    $language = strtolower(tvalidator("PURIFY",$_POST['language']));
    if($language!=''){
        $result = pdo_query("1", "
            update provider set language=? where providerid = ?
            ",array($language, $providerid));
    }
    
}
function AddLoginToRoom($providerid, $roomhandle, $roomstorehandle)
{
    if($roomstorehandle!=''){
        $roomhandle = $roomstorehandle;
    }
    if($roomhandle == '' || $roomhandle == 'app'){
        return;
    }
    //echo "Add Login to room $roomhandle";
    //exit();
    $roomid = 0;
    $result = pdo_query("1", "
        select roomid, 
        ( select owner from statusroom where statusroom.roomid = roomhandle.roomid and 
          statusroom.owner = statusroom.providerid ) as owner 
        from roomhandle where handle='#$roomhandle'
        ",null);
    if( $row = pdo_fetch($result)){
        $roomid = $row['roomid'];
        $owner = $row['owner'];
        AddMember(0, $providerid, $roomid );
    }
    $lastfunc = 'U';
    if($roomstorehandle!=''){
        $result = pdo_query("1", "
            select profileroomid from provider where providerid = ?
            ",array($providerid));
        if( $row = pdo_fetch($result)){
            $lastfunc = 'US';
            SaveLastFunction( $providerid, $lastfunc, $row['profileroomid'] );
        }
    } else {
            SaveLastFunction( $providerid, $lastfunc, $roomid );
    }
}