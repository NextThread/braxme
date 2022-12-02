<?php

require_once('localsettings/secure/localsettings.php');

    if($batchruns !='Y') {

        if(!isset($_SERVER['HTTPS'])) {
            //echo "HTTPS access required";
            exit();
        }
        if(BotCheck()){
            exit();
        }
    }

require_once('htmlpurifier-4.15.0-standalone/HTMLPurifier.standalone.php');
require('colorscheme.php');

    $purifierconfig = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifierconfig);

    
    function pdo_sql_connect( $connectnum, $sqlurl, $usr, $pwd, $database  )
    {
        global $sql_cert;
        global $sql_key;
        global $sql_ca;
        global $sql_globalcert;

        $dsn = "mysql:host=$sqlurl:3306;dbname=$database;charset=utf8mb4";
        
        if($sql_globalcert!=''){
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,

                /* Amazon AWS Version */
                //PDO::MYSQL_ATTR_SSL_CA => $sql_globalcert,
                //PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
        } else {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,

                PDO::MYSQL_ATTR_SSL_KEY    => $sql_key,
                PDO::MYSQL_ATTR_SSL_CERT   => $sql_cert,
                PDO::MYSQL_ATTR_SSL_CA     => $sql_ca,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];
            
        }
        $pdo = null;
        try {
             $pdo = new PDO($dsn, $usr, $pwd, $options);
        } catch (\PDOException $e) {
             echo "Exception Connection: $connectnum";
             throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }        
        return $pdo;
        
    }

    /* Connect to all the Databases and initialize Database Objects */

    $dbconnect1 = pdo_sql_connect( "1", $_SESSION['sqlurl'], $_SESSION['sqlusr'], $_SESSION['sqlpwd'], $_SESSION['database'] );
    //$dbconnect2 = pdo_sql_connect( "2", $_SESSION['sqlurl2'], $_SESSION['sqlusr2'], $_SESSION['sqlpwd2'], $_SESSION['database2'] );
    //$dbconnect3 = pdo_sql_connect( "3", $_SESSION['sqlurl3'], $_SESSION['sqlusr3'], $_SESSION['sqlpwd3'], $_SESSION['database3'] );
    $dbconnect4 = pdo_sql_connect( "4", $_SESSION['sqlurl4'], $_SESSION['sqlusr4'], $_SESSION['sqlpwd4'], $_SESSION['database4'] );
    //$dbconnect6 = pdo_sql_connect( "6", $_SESSION['sqlurl6'], $_SESSION['sqlusr6'], $_SESSION['sqlpwd6'], $_SESSION['database6'] );
    $dbconnect_news = pdo_sql_connect( "news", $_SESSION['sqlurl_news'], $_SESSION['sqlusr_news'], $_SESSION['sqlpwd_news'], $_SESSION['database_news'] );


/*******************
 * BRAXPRODUCTION SHARD 1
 *******************/
    function pdo_query( $connect, $query, $varlist ){
        
        global $dbconnect1;
        global $dbconnect2;
        global $dbconnect3;
        global $dbconnect4;
        global $dbconnect6;
        global $dbconnect_news;
        
        $db_pdo['1'] = $dbconnect1;
        $db_pdo['2'] = $dbconnect2;
        $db_pdo['3'] = $dbconnect3;
        $db_pdo['4'] = $dbconnect4;
        $db_pdo['6'] = $dbconnect6;
        $db_pdo['news'] = $dbconnect_news;
        
        if(!isset($db_pdo[$connect])){
            echo "$query<br>";
            echo "No Connection Specified in Query<br>";
            exit();
        }
        
        if(!isset($varlist)){
            $varlist = null;
        }
        
        $stmt = $db_pdo[$connect]->prepare($query);
        $stmt->execute($varlist);
        if(!$stmt){
            //echo "$query<br>";
            //echo "Error ";
            //exit();
        }
        return $stmt;

    }
    
    function pdo_fetch($stmt ){
        if(!$stmt)
        {
            return false;
        }
        //if($stmt->rowCount() == 0){
            //return false;
        //}
        
        $row =  $stmt->fetch(PDO::FETCH_ASSOC);
        return $row;
    }
    function pdo_fetch_row($stmt ){
        
        //if($stmt->rowCount() == 0){
        //    return false;
        //}
        
        $row =  $stmt->fetch(PDO::FETCH_NUM);
        return $row;
        
    }
    function pdo_fetch_array($stmt ){
        //if($stmt->rowCount() == 0){
            //return false;
        //}
        
        return $stmt->fetchAll();
        
    }
    
    



    
/*******************
 * BRAXPRODUCTION SHARD 2
 *******************/

    
/*******************
 * BRAXPRODUCTION SHARD 3
 *******************/
    
/*******************
 * BRAXPRODUCTION SHARD 6
 *******************/
    
    
/*******************
 * BRAXPRODUCTION SHARD NEWS
 *******************/


/*********************
 * COMMON FUNCTIONS
 *********************/


    function purify_string($string)
    {
        global $purifier;
        
        if(TrapJs($string)){
            return "";
        }

        if( isset($string)){

            return $purifier->purify( $string);
        } else {
            return "";
        }

    }
    function tvalidator($type,$string)
    {
        global $purifier;
        
        if(!isset($string)){
            return;
        }
        
        if(TrapJs($string)){
            return "";
        }
        if($type == 'ID'){
            if(FindJS($string)){
                return "";
            }

            return filter_var(intval($string), FILTER_VALIDATE_INT);//, array("options" => array("min_range" => 0,"max_range" => 9999999999)) );
            
        }
        if($type == 'EMAIL'){
            if(FindJS($string)){
                return "";
            }
            return filter_var($string, FILTER_SANITIZE_EMAIL);
        }
        if($type== 'ASCII'){
            //if not ASCII return blank
            if( ( bool ) ! preg_match( '/[\\x80-\\xff]+/' , $string )){
                return "";
            }            
        }
        if($type== 'PURIFY'){
            return $purifier->purify( $string);
        }
        if($type== 'PURIFYHANDLE'){
            if(FindJS($string)){
                return "";
            }
            
            return $purifier->purify( $string);
        }

    }
    function escape_for_sql($string)
    {
        
            if( isset($string)){
                    //$tmp = urldecode($string);
                    $tmp = str_replace("\n","\\n",$string);
                    return $tmp;
            } else {
                return "";
            }

    }

    function SaveLastFunction( $providerid, $func, $parm1 )
    {
        if($providerid == ''){
            return;
        }
        if(!isset($_SESSION['loginid'])){
            return;
        }
        if(isset($_SESSION['deviceid'])){
            $deviceid = tvalidator("PURIFY",$_SESSION['deviceid']);
            //$devicecode = @$_SESSION['devicecode'];
        } else {
            $deviceid = "";
        }

        pdo_query("1",
                "delete from lastfunc where providerid= ? and (deviceid='' or deviceid=?
                 or  datediff(now(),funcdate) > 1 ) ", array($providerid, $deviceid)
                );
        pdo_query("1","insert into lastfunc (providerid, deviceid, func, parm1, funcdate )
                    values (?, ?, ?, ?,now() )",
                    array( $providerid, $deviceid, $func,$parm1 ) 
                  );
        if( $func == 'R')
        {
            $parm1 = intval($parm1);
            pdo_query("1","update provider set lastroomid = ? where providerid=? ", array($parm1, $providerid ));
        }
        pdo_query("1","
            update staff set lastaccess=now() where providerid= ? and loginid=? ",
                array($providerid, $_SESSION['loginid'])
        );

    }

    function GetLastFunction( $providerid, $timelimit )
    {

        if($providerid == '') {
            $arr['elapsed']='';
            $arr['lastfunc']='';
            $arr['parm1']='';
            return (object) $arr;
        }
        if(isset($_SESSION['deviceid'])){
            $deviceid = tvalidator("PURIFY",$_SESSION['deviceid']);
        } else {
            $deviceid = '';
        }

        $result = pdo_query("1","
            select timestampdiff(SECOND, funcdate, now()) as elapsed, func, parm1 from lastfunc where providerid= ?
                and deviceid=? order by funcdate desc",
                array($providerid, $deviceid)
               );


        if( $row = pdo_fetch($result) )
        {
            $elapsed = intval($row['elapsed']);
            if($elapsed < $timelimit || intval($timelimit)===0 )
            {
                $arr['elapsed']="$row[elapsed]";
                $arr['lastfunc']="$row[func]";
                $arr['parm1']="$row[parm1]";
                return (object) $arr;
            }
        }
        $arr['elapsed']='';
        $arr['lastfunc']='';
        $arr['parm1']='';
        return (object) $arr;
    }

    function LogDebug( $providerid, $event )
    {
        //Disable except during testing
        return;
        if($providerid == ''){
            $providerid = 0;
        }
        $event = tvalidator("PURIFY",$event);
        pdo_query("1","insert into debuglog (providerid, logdate, event ) values (?, now(), ? ) ",array($providerid,$event));

    }
    function InternetTooSlow()
    {
        //if( intval($_SESSION['iscore'])< 2 ){
        //    return true;
        //}
        return false;
    }
    function ActiveInformationRequest($providerid)
    {
        
        $result = pdo_query("1"," 
            select *
            from credentialformtrigger
            where providerid = ? and status='N'
                ", array($providerid));
        if($row = pdo_fetch($result)){

            return 'Y';
        }
        return 'N';

    }
    
    function EncryptE2EPasskey($passkey,$salt)
    {
        if($passkey==''){
            return $passkey;
        }
        $passkey64 = OpenSSLEncrypt($passkey,$salt);
        return $passkey64;
    }
    function DecryptE2EPasskey($passkey64,$salt)
    {
        if($passkey64==''){
            return $passkey64;
        }
        $passkey = OpenSSLDecrypt($passkey64,$salt);
        return $passkey;
    }
    function FindJs($string)
    {
        //$test = rawurldecode($string);
        if(strstr(strtolower($string),"javascript:")!==false){
            return true;
        }
        /* validate that the value does not contain potential javascript */
        if(strstr(strtolower($string),";")!==false){
            return true;
        }
        if(strstr(strtolower($string),":")!==false){
            return true;
        }
        if(strstr(strtolower($string),")")!==false){
            return true;
        }
        if(strstr(strtolower($string),"(")!==false){
            return true;
        }
        if(strstr(strtolower($string),"-")!==false){
            return true;
        }
        return false;
    }
    function TrapJs($string)
    {
        //$test = rawurldecode($string);
        if(strstr(strtolower($string),"javascript:")!==false){
            return true;
        }
        /*
         *  Identify JS injection via Escaping quotes
         */
        
        if(strstr(strtolower($string),"';")!==false){
            return true;
        }
        if(strstr(strtolower($string),"\";")!==false){
            return true;
        }
        if(strstr(strtolower($string),"`;")!==false){
            return true;
        }
        return false;
    }
    
    function BotCheck() {

      if (isset($_SERVER['HTTP_USER_AGENT']) 
              && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'])) {
          
        return TRUE;
        
      } else {
          
        return FALSE;
        
      }

    }
    function HttpsWrapper($link)
    {
        global $installfolder;
        //return $text;
        if(strstr(strtolower($link),"https://")!==false){
            return $link;
        }
        $shortlink = $link;
        if(substr( strtolower($link),0,7 )!="http://"){
            $shortlink = substr($link,7);
                
        } 
        
        $wrapper = "https://" . $shortlink;

        return $wrapper;
    }    
    function CheckLiveStream($streamid)
    {
            $ch = curl_init("https://audio.brax.live:8443/$streamid");

            if($ch!== false ){

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                curl_setopt($ch, CURLOPT_TIMEOUT, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                $response = curl_exec($ch);

                //close connection
                curl_close($ch);
            } else {
                $response = "";
            }

            if($response === '' || strstr($response, 'could not be found')!== false ){

                return false;
            }

            return true;

    }
    function GetTimeoutPin($providerid)
    {
        $_SESSION['pin']='';
        $result = pdo_query("1","select pin, encoding from timeout where providerid = ? ", array($providerid));
        if( $row = pdo_fetch($result)){
            $_SESSION['pin'] = $row['pin'];
        }
        if(intval($_SESSION['timeout_seconds'])==0){
            $_SESSION['pin'] = "";
        }

    }
    function TimeOutCheck()
    {
        if(!isset($_SESSION['pinlock'])){
            return false;
        }
        if(!isset($_SESSION['pin'])){
            return false;
        }

        if(
           $_SESSION['pinlock']!='Y' && 
           $_SESSION['pin']!='' && 
           intval($_SESSION['timeout_seconds'])>0){

                $t = time();
                $t2 = $_SESSION['timeoutcheck'];
                if($t - $t2 > $_SESSION['timeout_seconds'] ){
                    //GetTimeoutPin($_SESSION['pid']);
                    //echo "$_SESSION[pin]";
                    return true;

                }
        }
        return false;
        
    }
    function ServerTimeOutCheck()
    {
        if(!isset($_SESSION['pid']) || $_SESSION['pid']=='') //Invalid Session
        {
            $_SESSION['reset']='Y';
            return true;
        }
        return false;
    }
    function StripEmojis($text)
    {
        
        return preg_replace('/([0-9#][\x{20E3}])|[\x{00ae}\x{00a9}\x{203C}\x{2047}\x{2048}\x{2049}\x{3030}\x{303D}\x{2139}\x{2122}\x{3297}\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1FFFF}][\x{FE00}-\x{FEFF}]?/u', '', $text);
        
    }
    function RootServerReplace($url)
    {
        global $rootserver;
        
        return str_replace("https://brax.me","$rootserver", $url);
        
    }
    function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }
    
