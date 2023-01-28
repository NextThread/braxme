<?php
session_start();
set_time_limit ( 30 );
require("validsession.inc.php");
require("nohost.php");
require_once("config-pdo.php");
require_once("crypt-pdo.inc.php");
require_once('simple_html_dom.php');
require_once('internationalization.php');

    /* Turn this on for Moderating Comments */
    /* Note: When turned on - admin cannot tap user to reply in chat */
    /* leave off for normal */
    $enableAdminDeleteMode = false;

    $braxchat = "<img src='../img/braxchat.png' style='position:relative;top:5px;height:30px;width:auto;padding-top:0;padding-right:2px;padding-bottom:0px;' />";
    $braxmedal = "<img class='icon15' src='$iconsource_braxmedal_common' title='Trusted $appname Resource' style='top:0px;bottom:0px;height:14px' />";
    $braxnewbie = "<img class='icon15' src='$iconsource_braxnewbie_common' title='Trusted $appname Resource' style='top:0px;bottom:0px;height:14px' />";

    
    $time1 = microtime(true);
    $menucolor = '#3e4749';
    //$replyflag = tvalidator("PURIFY",$_POST[replyflag]);
    $providerid = tvalidator("ID",$_POST['providerid']);
    $chatid = tvalidator("ID",$_POST['chatid']);
    $togglechat = tvalidator("PURIFY",$_POST['togglechat']);
    $togglemembers = @tvalidator("PURIFY",$_POST['togglemembers']);
    $adminmode = @tvalidator("PURIFY",$_POST['adminmode']);
    $videoactive = @tvalidator("PURIFY",$_POST['videoactive']);
    $audiostreamactive = @tvalidator("PURIFY",$_POST['audiostreamactive']);
    
    
    //COLOR SETUP
    //$backgroundcolor = '#3d8da5';
    $backgroundcolor = $global_background;
    $textcolor = $global_textcolor;
    $textcolor2 =  $global_chatself_color;

    
    
    $limit = "5000";
    $togglemsg = "$menu_hideoldermessages";
    $hideextra = '';
    if($togglechat == "false"){
        
        $limit = "100";
        $hideextra = 'display:none';
        $togglemsg = "$menu_showoldermessages";
    }
    $togglemsgmembers = "Hide Members";
    $hidemembers = '';
    if($togglemembers == "false"){
        
        $hidemembers = 'display:none';
        $togglemsgmembers = "Members";
        
    }
    
    $replyemail = @tvalidator("EMAIL",$_SESSION['replyemail']);
    $force = @tvalidator("PURIFY",$_POST['force']);
    $passkey = @escape_for_sql($_POST['passkey']);
    
    $timezoneoffset = $_SESSION['timezoneoffset'];
    $scroll = "";
    $lastseen = "";
    $chat = "";
    $seen = "";
    $unreadtime = "";
    $unreadtime2 = "";
    $other = "";
    $otheremail = "";
    $script = "";
    $more = "";
    $title1 = "";

    $time2 = microtime(true);
    
    $chatobj = IsValidChat($chatid, $providerid, $passkey);
    
    $owner = $chatobj->owner;
    $archive = $chatobj->archive;
    $title = $chatobj->title;
    $roomdesc = $chatobj->roomdesc;
    $archivemode = $chatobj->archivemode;
    $broadcaster = $chatobj->broadcaster;
    $broadcasterid = $chatobj->broadcasterid;
    $broadcasttitle = $chatobj->broadcasttitle;
    $broadcastmode = $chatobj->broadcastmode;
    $streamingaccount = $chatobj->streamingaccount;
    $hidemode = $chatobj->hidemode;
    $streamid = $chatobj->streamid;
    $broadcast = "";
    $broadcastowner = "";
    $broadcastownermobile = "";
    $radiostationinfo = null;
    $radiocolor = "#eaaa20;color:white";
    $passkey64 = $chatobj->passkey64;

    if($providerid == $chatobj->roomowner){
        $enableAdminDeleteMode = true;
    }
    if($providerid == $chatobj->moderator){
        $enableAdminDeleteMode = true;
    }
    
    $title1 = "";
    if($title!=''){
        
        $title1 ="- $title";
        
    }
    
    if($chatobj->keyhash!=''){
        
        $notactive_e2e ="display:none";
        $script =  "<script>localStorage.setItem( 'chat-$chatid', '$passkey64');</script>";
        
    } else {
        
        $notactive_e2e = "";
        $passkey = "";
        $script = "";
    }

    $time3 = microtime(true);

    CheckUnreadMessages($chatid, $providerid, $force);
    
    $_SESSION['timeoutcheck'] = time();
        
    
    $time4 = microtime(true);
    
    $streaming = false;
    if($chatobj->radiostation=='Y' && $chatobj->broadcastmode!='V' ){
        
        $streaming = CheckLiveStream($streamid);
        if(!$streaming){
            $broadcaster = "";
        }
    }
    if($chatobj->radiostation=='Y' && 
       $chatobj->broadcastmode=='V' && 
       $chatobj->broadcasterid!='' ){
        $streaming = true;
    }
    $chatmode = 'CHAT';
    if($chatobj->radiostation=='Y' ){
        $chatmode = 'LIVE';
    }
    
    
    $otherparty = "";
    $otherparty2 = "";
    $firstparty = "";
    $techsupport = false;
    $technotes = "";
    $avatarblock = "";
    $avatarblocklong = "";
    $displaytwopartyonly = "";
    $listenercount = 0;
    $mobilelabel = "";
    $i = 0;
    
    if($togglemembers=='false' ){
    
        
        $partyobj =  GetOtherPartyName($chatid, $providerid);
        $otherparty2 = $partyobj->otherparty2;
        $otheremail = $partyobj->otheremail;
        $membercount = $partyobj->membercount;
        $listenercount = $partyobj->listenercount;
        $displaytwopartyonly = $partyobj->displaytwopartyonly;
        if($title!=''){
            
            if($chatobj->radiostation =='Y'){
                
                $otherparty2 = "$title ($membercount Members $listenercount Listeners)";
                $mobilelabel = "";
                
            } else {
                
                $mobilelabel = "$title ($membercount Members)";
            }
        } else {
            
            $mobilelabel = "Chat with $otherparty2";
        }
        $avatarblock = $partyobj->avatarblock;
        $avatarblocklong = $partyobj->avatarblocklong;
    
    }
    if($togglemembers!=='false'){
        
        $partyobj = MemberList($chatid, $providerid, $title, $chatobj->keyhash, $streaming);
        $otheremail = $partyobj->otheremail;
        $otherparty2 = $partyobj->otherparty2;
        $otherparty = $partyobj->otherparty;
        $technotes = $partyobj->technotes;
        $listenercount = $partyobj->listenercount;
    }
    
    if($title == '' && $partyobj->membercount == 2){
        
        $title1 = " - ".$partyobj->firstparty;
    }
    
    $allowdelete = true;
    if($owner != $providerid && $partyobj->membercount > 2 ){
        
        $allowdelete = false;
    }
    
    
    $time5 = microtime(true);

    if($chatobj->keyhash == ''){
        
        $lock = "";
        SaveLastFunction($providerid,"C","$chatid");
        
    } else {
        
        $lock = "<img class='icon15' src='../img/password-128-white.png' style='padding-right:2px;padding-bottom:0px;' /> PrivateKey ";
        SaveLastFunction($providerid,"","");
        
    }

    
    /* Issues List
     * 
     * Handling of Blank User (Other is invited mixed in with non-invites)
     * Seen -- handling for multiples
     * What about deletion - who gets to delete?
     * 
     */
    $titlehtml = htmlentities($title);
    $chatparty = "
                    <div class='x' style='background-color:$global_titlebar_alt_color;padding:5px'>
                        <span class='pagetitle3' style='color:white;font-weight:bold;'>
                            <span class='chatsettitleopen' style='cursor:pointer'>
                                $lock Chat $title1
                            </span>
                 ";
    
    if($chatobj->radiostation=='Y'){
    
        $broadcastobj = BroadcastInfo($streaming, $broadcastmode, $broadcaster, $chatid, $streamid, $title, $broadcasterid == $providerid, $broadcasttitle, $listenercount, $chatobj->radiostation );
        $broadcast = $broadcastobj->broadcast;
        $broadcastowner = $broadcastobj->broadcastowner;
        $broadcastownermobile = $broadcastobj->broadcastownermobile;
        $radiostationinfo = $broadcastobj->radiostationinfo;
        
        
        pdo_query("1","update chatmaster set streamid=? where chatid=? ",array($streamid,$chatid));
        
        $chatparty = "
                        <div class='x' style='background-color:$global_titlebar_alt_color;padding:5px'>
                            <span class='pagetitle3' style='color:white;font-weight:bold;'>
                                <span class='chatsettitleopen' style='cursor:pointer'>
                                    $lock Live Streaming $title1
                                </span>
                     ";
        
    }
    
    if($force == true && $streaming ){
        
        $limit = "200";
        if($togglechat == "false"){
            $limit = "50";
        }
        
    }

    
    
    //if($owner == $providerid || $_SESSION['superadmin']=='Y'){
    if($_SESSION['superadmin']=='Y' || $_SESSION['techsupport']=='Y'){
        
        $chatparty .= "
                <span class='chattitlearea' style='display:none'>
                    <input class='chattitle' 
                        placeholder='Chat Title'
                        type='text' maxlength='20' size=20 value='$titlehtml' />
                    &nbsp;&nbsp;  
                    &nbsp;&nbsp;  
                    <input class='chatradio' 
                        type='hidden' maxlength='1' size='1' value=''  />
                    <img class='chatsettitle' data-chatid='$chatid' 
                       src='../img/Arrow-Right-in-Circle-White_120px.png' 
                       style='cursor:pointer;position:relative;top:5px;
                       height:20px;width:auto;' />
                    <br>
                </span>
                  ";
    } else {
        
        $chatparty .= "
                <span class='chattitleareax' style='display:none'>
                    <br>
                    <input class='chattitle' 
                        placeholder='Chat Title'
                        type='text' maxlength='20' size=20 value='$titlehtml' />
                    &nbsp;&nbsp;  
                    &nbsp;&nbsp;  
                    Radio?&nbsp;
                    <input class='chatradio' 
                        placeholder='Radio'
                        type='text' maxlength='1' size='1' value='$chatobj->radiostation'  />
                    &nbsp;&nbsp;  
                    <img class='chatsettitle' data-chatid='$chatid' 
                       src='../img/Arrow-Right-in-Circle-White_120px.png' 
                       style='cursor:pointer;position:relative;top:5px;
                       height:20px;width:auto;' />
                    <br>
                </span>
                  ";
        
    }
    $chatparty .= "
                        </span>
                    </div>
                    &nbsp;&nbsp;<img class='oldchat scrolldown' 
                        src='../img/arrowhead-down-gray-128.png' 
                        style='padding-top:10px;cursor:pointer;
                        width:15px;height:auto;$hideextra' />
                ";
    
    
    if( $other == ''){
        
        $result2 = pdo_query("1",
            "
            select name from invites where providerid=? and chatid=? 
            ",array($providerid,$chatid));
        $row2 = pdo_fetch($result2);
        $other = "$row2[name] (Invited)";
    }

    $count = 0;
    if($togglemembers=='false'){
    
        $chatmsgobj =  ShowChatMessages(
                        $chatid, $providerid, $limit, $passkey, $passkey64, 
                        $chatparty, $togglemsg, $togglemsgmembers, $script, $more, $owner, 
                        $hidemode, $membercount, $chatobj->mute, $chatobj );
        
        $chat = $chatmsgobj->chat;
        $count = $chatmsgobj->count;

    }
    
    if($broadcasterid!='' && $chatobj->radiostation=='Y' && $chatobj->broadcastmode!='V'  && $chatobj->broadcastid != $chatobj->viewerbroadcastid ){
    //if($broadcasterid!='' && $chatobj->radiostation=='Y' && $chatobj->broadcastmode!='V' && $audiostreamactive == 'false' ){
        $chat .= "<div class='audiostream pagetitle2a' 
                style='cursor:pointer;background-color:$backgroundcolor;color:$global_activetextcolor;width:100%;text-align:left;padding:20px'
                data-chatid='$chatid' data-streamid='$streamid' data-mode='START' >   
                <b>Tap here
                <img class='icon20'
                src='$iconsource_braxplaymusic_common' /> to hear the audio broadcast</b></div>";
        
    }
    
    $chat .= MemberInfo($chatobj->radiostation, $chatparty, $hidemembers, $chatid, $otheremail, $chatobj->keyhash, $technotes, $otherparty );


    if($_SESSION['mobilesize']=='Y'){
        
        $chat .= MobileMenu( $providerid, $chatid, $passkey64, $archive, 
                $streaming, $chatobj->radiostation, 
                $broadcastowner, $allowdelete,  
                $otheremail,  $notactive_e2e, $archivemode,
                $broadcastownermobile, $mobilelabel, $togglemsg );
    }
    
    if($_SESSION['mobilesize']!=='Y') {
        
        $chat .= DesktopMenu( $providerid, $chatid, $passkey64, $chatobj->keyhash, $archive, 
                $streaming, $chatobj->radiostation, 
                $broadcastowner, $allowdelete, 
                $otheremail,  $notactive_e2e, $archivemode,
                $avatarblocklong, $otherparty2, $displaytwopartyonly );        

    }

    //$chat .= "$broadcastowner";
    /*
    $time6 = microtime(true);
    $time7 = microtime(true);
    
    
    
    $timediff1 = $time7 - $time1;
    $timediff2 = $time6 - $time1;
    $timediff3 = $time5 - $time1;
    $timediff4 = $time4 - $time1;
    $timediff5 = $time3 - $time1;
    
    $timing = "";
    if( $providerid == $admintestaccount){
        $timing = "$timediff1<br>$timediff2<br>$timediff3<br>$timediff4<br>$timediff5<br>";
    }
    $timing = "";
     * 
     */
    $chatheading = $radiostationinfo;
    $broadcastheading = $broadcast;
    if($broadcastheading =='' || $chatobj->radiostation == 'Q'){
        $chatheading = ChatHeading($broadcastmode, $chatid, "$title1", $owner == $providerid, $hidemode);
    } 
    if($videoactive=='true'){
        //$chatheading = "<span class='nonmobile'>".$chatheading."</span>";
    }
    
    if( $broadcasterid == $providerid && $broadcastmode=='V'){
        $broadcastmode = "B";
    } else 
    if(!$force){// || $broadcasterid == $providerid){
        $broadcastmode = "";
    }
    
    $panel = "";
    if($broadcastmode == 'A' && $streaming){
        $gif = "../img/animated-spring.gif";
        if(strstr($title,"Music")!==false){
            //$gif = "../img/animated-snow.gif";
            $gif = "../img/animated-rain.gif";
        }
        if(strstr($title,"Music Tree")!==false){
            $gif = "../img/animated-jazzpiano.gif";
        }
        if(strstr($title,"Talk")!==false){
            $gif = "../img/animated-brook.gif";
        }
        if(strstr($title,"Talk Too")!==false){
            $gif = "../img/animated-spring2.gif";
        }
        
        if(strstr($title,"Pirate")!==false){
            $gif = "../img/animated-brook-2.gif";
        }
        
        //if($_SESSION['superadmin']=='Y'){
        //if(strstr(strtolower($chatobj->photourl),".gif")!==false){
            $gif = $chatobj->photourl;
        //}
        
        $panel = "
            <div style='text-align:center;vertical-align:center;width:100%;height:100%;background-color:black;color:white'>
            <div class='pagetitle' style='padding:20px;color:white'>$broadcasttitle</div>
            <img src='$gif' style='width:100%;height:auto;margin-auto;' />
            <img class='icon30' src='../img/headphone-white-128.png' />
            </div>
            ";
    }
    if($broadcastmode == 'B' && $streaming){
        $gif = "../img/animated-spring.gif";
        if(strstr($title,"Music")!==false){
            //$gif = "../img/animated-snow.gif";
            $gif = "../img/animated-rain.gif";
        }
        if(strstr($title,"Music Tree")!==false){
            $gif = "../img/animated-jazzpiano.gif";
        }
        if(strstr($title,"Talk")!==false){
            $gif = "../img/animated-brook.gif";
        }
        if(strstr($title,"Talk Too")!==false){
            $gif = "../img/animated-spring.gif";
        }
        
        if(strstr($title,"Pirate")!==false){
            $gif = "../img/animated-spring-2.gif";
        }
        if(strstr(strtolower($chatobj->photourl),".gif")!==false){
            $gif = $chatobj->photourl;
        }
        
        $panel = "
            <div style='text-align:center;vertical-align:center;width:100%;height:100%;background-color:black;color:white'>
            <div class='pagetitle' style='padding:20px;color:white'>$broadcasttitle</div>
            <img src='$gif' style='width:100%;height:auto;margin-auto;' />
            <div class='pagetitle2a' style='padding:20px;color:white'>Audio and Video Broadcast</div>
            </div>
            ";
    }
    
    if(($broadcastmode == 'A' || $broadcastmode == 'B') && !$streaming){
        $broadcastmode = "";
    }
    $chatentry = "
        <div class='refreshchatsession chatscrollsuspended pagetitle3 chatwidth2 gridnoborder' 
            data-chatid='$chatid' data-keyhash='$chatobj->keyhash' 
            style='padding:10px;display:none;color:white;cursor:pointer'>
            <img class='icon15' src='../img/Lock-White_120px.png' />
        </div>

        ";
    $chatentry .= "<span class='chatscrollactive'>";
    
    $chatentry .= "
        <textarea  id='chatmessage' class='inputfocus mainfont chatwidth2' data-detectenter=''  name='chatmessage' x-webkit-speech rows=1 style='display:inline-block;margin-top:10px;margin-left:10px;margin-right:0px;margin-bottom:0px;overflow-x:hidden;padding:7px' 
            placeholder='$menu_message' 
            data-streaming='$streaming' data-passkey64='$passkey64'  data-sms='' data-name='' data-send='Y' data-msgid=''        
        ></textarea>
        <img class='sendchatbutton chatcommenthide2 tapped icon25 chatentrybutton' 
            title='Send Message' 
            alt='Send Message' src='../img/Arrow-Right-in-Circle-White_120px.png' id='sendchatbutton' 
            data-streaming='$streaming' data-passkey64='$passkey64'  data-sms='' data-name='' data-send='Y' data-msgid='' 
            style='display:inline-block;margin-left:15px;;margin-top:10px;margin-bottom:10px' />   
            ";
    
    if($chatobj->radiostation!='Q' || $owner == $providerid){
        
        $chatentry .= "
            
        <img class='chatextra icon25'  title='Show Menu' alt='Show Menu' src='../img/ellipsis-white-128.png' style='display:inline-block;margin-left:10px;margin-top:10px;margin-bottom:10px' />
        <img class='chatextrahide icon25' title='Collapse Menu' src='../img/ellipsis-white-128.png' style='display:inline-block;display:none;margin-left:10px;;margin-top:10px;margin-bottom:10px' />
        <br>
        ";
    }
    if(!$streaming){
        //$chatentry .= "
        //<div class='nonmobile smalltext' style='color:white;padding-top:10px;padding-left:20px'><br><br>&nbsp;&nbsp;&nbsp; Ctrl-Enter to Send</div>
        //";
    }
    
    $chatentry .= "</span>";
    
    $chat = $chat.$broadcastheading;
    
    $arr = array('chat'=> "$chat",
                 'chatheading' => $chatheading,
                 'chatentry' => $chatentry,
                 'video' => $broadcastmode,
                 'panel' => $panel,
                 'scroll'=> "Y",
                 'lastseen'=> "",
                 'error'=>"",
                 'passkey64'=>"$passkey64",
                 'keyhash'=>"$chatobj->keyhash]"
                );
        
    
    echo json_encode($arr);
    


function WrapLinks($text)
{
    global $installfolder;
    //return $text;
    $malwareflag = false;
    $html = new simple_html_dom();

    // load the entire string containing everything user entered here

    $return = $html->load($text);
    $links = $html->find('a');

    foreach ($links as $link) {
        //if(SafeUrl($link->href)== false){
        //    $malwareflag = true;
        //}
        if(isset($link->href) && substr( strtolower($link->href),0,7 )==="http://"){
            $link->href = "https://" . substr($link->href,7);
        }
        if($_SESSION['mobiletype']=='A' || $_SESSION['mobiletype']=='I'){
            $link->target = "_parent";
        }
            
    }
    $newtext = $html->save();
    if( $malwareflag == true){
        $newtext = strip_tags($newtext,"<br><b><p>");
        $newtext .= "<br><br><b style='color:firebrick'>Google has flagged the link(s) above as possible malware. If you wish to access the link, we recommend that you use a TOR Browser.</b>";
    }
    
    
    return $newtext;
}    

function SafeUrl($url)
{
    return true;
    //disabled
    //
    //test http://ianfette.org

    $apikey = "";
    $encoded_url = urlencode($url);
    
    $api = "https://sb-ssl.google.com/safebrowsing/api/lookup?client=CLIENT&key=$apikey&appver=1.1&pver=3.1&url=$encoded_url";    
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $api);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

    $data = curl_exec($ch);
    curl_close($ch);
    
    if($data == "malware"){
        return false;
    } else {
        return true;
    }

    
    //APIKEY
}
function GetTechNotes( $otherid, $chatid )
{
    return "";

}
function ChatCleanForEdit($text)
{
    
    $html = new simple_html_dom();

    // load the entire string containing everything user entered here

    $return = $html->load($text);
    $imgs = $html->find('img');
    $links = $html->find('a');
    
    foreach ($imgs as $img){ 
        $src = $img->src;
        if($src == "../img/reply.png"){
            $img->outertext = ">";
        } else {
            //Editing of Images Not allowed - FAIL THIS
            return false;
        }
    }
    

    foreach ($links as $link){ 
        $href = $link->href;
        $link->outertext = $href;
    }
    $text = $html->save();
    
    $text = str_replace("<br>",chr(10),$text);
    
    return $text;
}    
function IsValidChat($chatid, $providerid, $passkey)
{
    global $global_textcolor;
    global $global_titlebar_color;
    global $menu_delete;
    
    if( intval($chatid) ==0 ){
        
        $arr = array('chat'=> "",
                     'scroll'=> "N",
                     'error' => ""
                    );


        echo json_encode($arr);
        exit();
        
    }
    
    
    
    
    $result = pdo_query("1",
        "
        select chatmaster.keyhash, chatmaster.title, chatmaster.archive, 
        chatmaster.owner, chatmaster.encoding, radiostation,  
        (select 'Y' from notifymute where notifymute.id = chatmaster.chatid and idtype='C' and notifymute.providerid = ? ) as mute,
        broadcastmode, timestampdiff(MINUTE, reservestation, now() ) as reservedtime, 
        provider.providername as broadcaster, 
        (select streamingaccount from provider where providerid=?) as streamingaccount,
        (select broadcastid from broadcastlog where mode='B' and broadcastlog.chatid = chatmaster.chatid and broadcastdate2 is null order by broadcastid desc limit 1 ) as broadcastid,
        (select broadcastid from broadcastlog where mode='V' and broadcastlog.chatid = chatmaster.chatid and broadcastdate2 is null and broadcastlog.providerid = ? order by broadcastid desc limit 1 ) as viewerbroadcastid,
        chatmaster.broadcaster as broadcasterid, chatmaster.radiotitle,
        (select photourl from roominfo where roominfo.roomid = chatmaster.roomid) as photourl,
        (select roomdesc from roominfo where roominfo.roomid = chatmaster.roomid) as roomdesc,
        (select owner from statusroom where statusroom.roomid = chatmaster.roomid and statusroom.providerid = statusroom.owner) as roomowner,
        (select providerid from roommoderator where roommoderator.roomid = chatmaster.roomid and roommoderator.providerid = $providerid) as moderator,
        blocked1.blockee, blocked2.blocker,
        chatmaster.hidemode, chatmaster.question
        from chatmaster 
        left join provider on provider.providerid = chatmaster.broadcaster
        left join blocked blocked1 on blocked1.blockee = provider.providerid and blocked1.blocker = ?
        left join blocked blocked2 on blocked2.blocker = provider.providerid and blocked2.blockee = ?
        where chatmaster.chatid=? and chatid in 
        (select chatid from chatmembers where providerid = ? and chatmaster.chatid = chatmembers.chatid )
        ",array($providerid,$providerid,$providerid,$providerid,$providerid,$chatid,$providerid));
    if(!$row = pdo_fetch($result)){
        
        
        $arr = array('chat'=> "This chat session has been ended. Please exit chat.",
                     'scroll'=> "N",
                     'error' => "notfound"
                    );


        echo json_encode($arr);
        exit();
        
    }
    
    if($row['blockee']!='' || $row['blocker']!=''){
        $arr = array('chat'=> "This broadcast is restricted",
                     'scroll'=> "N",
                     'error' => "restricted"
                    );
        echo json_encode($arr);
        exit();
    }
    
    
    if($row['encoding']!='' && $row['title']!=''){
        $title =  DecryptText( $row['title'], $row['encoding'],"$chatid" );
    } else {
        $title = htmlentities($row['title'],ENT_QUOTES);
    }
    
    
    //This means HYPER SECURE MODE ON
    $passkey64 = "";
    
    
    if($row['keyhash']!='' && $passkey !== '' ){
        $passkey64 = E2EVerify($chatid, $providerid, $row['keyhash'], $passkey );
        PassE2EKeyHandleRequests($chatid, $passkey64, $providerid );
    }
    if($row['keyhash']!='' && $passkey == '' ){
        $arr = array('chat'=> "
            <div class='pagetitle2a' style='color:$global_textcolor;padding:20px'>
                This chat session <b>$title</b> requires a secret key.
                <br><br>
                A key resend request has been sent. Your key will be delivered when a member reenters this chat.
                Please come back later.
                <br><br>
                Or enter the secret key if you know it.
                <br><br>
                <div class='pagetitle3 divbutton setchatsession' data-chatid='$chatid' data-mode='CHAT' data-keyhash='$row[keyhash]' data-error='Y' style='background-color:$global_titlebar_color;color:white' >Enter Secret Key</div> 
                <div class='pagetitle3 divbutton selectchatlist' data-chatid='$chatid' data-mode='CHAT' data-keyhash='$row[keyhash]' data-error='Y' style='background-color:$global_titlebar_color;color:white' >Back</div> 
            </div>
                ",
            'scroll'=> "N",
            'error'=>''
           );

        /* Request a new key */
        PassE2EKeyMakeRequest($chatid, $providerid );
    
        
        
        echo json_encode($arr);
        exit();
            
    }

    
    $array = array();
    $array['keyhash'] = $row['keyhash'];
    $array['passkey64'] = $passkey64;
    $array['title'] = $title;
    $array['radiostation'] = $row['radiostation'];
    if($row['radiostation']=='Y' || $row['radiostation']=='Q'){
        $streamhash = substr(hash("sha1", $chatid),0,8);
        $streamid = "chat$streamhash";
        $array['streamid'] = $streamid;
    } else {
        $array['streamid'] ='';
    }
    $array['archive'] = $row['archive'];
    $array['owner'] = $row['owner'];
    $array['roomowner'] = $row['roomowner'];
    $array['roomdesc'] = $row['roomdesc'];
    $array['moderator'] = $row['moderator'];
    $array['broadcaster'] = $row['broadcaster'];
    $array['broadcasterid'] = $row['broadcasterid'];
    $array['broadcastid'] = $row['broadcastid'];
    $array['viewerbroadcastid'] = $row['viewerbroadcastid'];
    $array['broadcastmode'] = $row['broadcastmode'];
    $array['reservedtime'] = $row['reservedtime'];
    $array['streamingaccount'] = $row['streamingaccount'];
    $array['broadcasttitle'] = stripslashes(base64_decode($row['radiotitle']));
    $array['encoding'] = $row['encoding'];
    $array['archivemode']=$menu_delete;
    $array['mute'] = $row['mute'];
    if($row['archive'] == 'Y') {
        //$array['archivemode'] = 'Archive';
        $array['archivemode'] = $menu_delete;
    }

    if($row['keyhash']!=''){
        $array['archive']='N';
        $array['archivemode']=$menu_delete;
    }
    $array['hidemode'] = $row['hidemode'];
    if($array['streamid']!='' && $array['broadcastmode']!='V' && $array['broadcastmode']!='B'){
        $array['broadcastmode']='A';
    }
    $array['photourl'] = $row['photourl'];
    
    
    return (object) $array;
    
}
function E2EVerify($chatid, $providerid, $keyhash, $passkey )
{
    global $global_titlebar_color;
    
    $notactive_e2e = "";
    $passkey64="";
    if($keyhash!=''){

        $passkey64 = EncryptE2EPasskey($passkey,$providerid);
        //$passkeyJs = base64_encode($passkey);
        //$script =  "<script>localStorage.removeItem( '$chatid' );</script>";
        //if($_SESSION['superadmin']=='Y'){
        //}
        $hash = hash('sha256',"$passkey$chatid");
        
        if($hash!=$keyhash ){
            $arr = array('chat'=> "&nbsp;&nbsp;Incorrect Secret Key. This chat session cannot be decrypted.
                <br><br>
                Please ask a chat member to re-add you to this chat. This will resend the key to your device.
                <br><br>
                <div class='divbutton setchatsession' data-chatid='$chatid' data-keyhash='$keyhash' data-error='Y' style='background-color:$global_titlebar_color;color:white' >Enter Secret Key</div> ",
                         'scroll'=> "N",
                         'error'=> ""
                        );

            //$_SESSION['chatpasskey']='';
            echo json_encode($arr);
            exit();
        }
        $archivemode = 'Delete';
        $archive = 'N';
        $notactive_e2e ="display:none";
        return $passkey64;
    }
    return "";
    
}
function CheckUnreadMessages($chatid, $providerid, $force)
{

    $result = pdo_query("1",
        "
        select
        TIMESTAMPDIFF(
                SECOND, 
                chatmaster.lastmessage,
                chatmembers.lastread
            ) as unreadtime
        from chatmaster 
        left join chatmembers on chatmembers.chatid = chatmaster.chatid
        where chatmaster.chatid = ? and chatmembers.providerid = ?
        ",array($chatid,$providerid));
    
    pdo_query("1",
        "
        update chatmembers set lastactive=now(), lastread=now() where 
        providerid= ? and chatid=? and status='Y'
        ",array($providerid,$chatid));
    if($force=='true'){
        return;
    }
    
    
    
    $scroll = 'Y';
    if($row = pdo_fetch($result)){
        
        
        $u = intval($row['unreadtime']);
        //Force a Scroll if new message
        //$u = intval($unreadtime)*(1);
        
        if( $force == 'false' && $u >= 0 ){ 

            $scroll = 'N';
            $arr = array('chat'=> "$u",
                         'scroll'=> "$scroll",
                         'lastseen'=> "",
                         'error' => "$u"
                        );

            echo json_encode($arr);
            exit();
        }

        
    } else {
        
        if( $force == 'false' ){ 
            
            $scroll = 'N';
            $arr = array('chat'=> "",
                         'scroll'=> "N",
                         'lastseen'=> "",
                         'error' => "$u"
                        );

            echo json_encode($arr);
            exit();
        }
        
    }
}
function GetOtherPartyName($chatid, $providerid)
{
    global $timezoneoffset;
    global $rootserver;
    
        $result = pdo_query("1",
            "
            select provider.providername, provider.providerid as otherid,  
            provider.replyemail as otheremail, provider.avatarurl,
            provider.handle, chatmembers.techsupport, chatmembers.broadcaster,
            (select count(*) from chatmembers c2 where c2.chatid = ? ) as membercount,
            (select count(*) from chatmembers c2 where c2.chatid = ? and c2.broadcaster is not null and c2.broadcaster > 0 ) as listenercount,
            DATE_FORMAT(
               date_add(lastread, interval (?)*(60) MINUTE),
                '%b %d/%y %a  %h:%i:%s%p'
            ) as seen
            from chatmembers
            left join provider on provider.providerid = chatmembers.providerid 
            where chatmembers.chatid = ? and chatmembers.providerid != ?
            and provider.active = 'Y'
            order by lastread desc limit 15
            ",array($chatid,$chatid,$timezoneoffset,$chatid,$providerid));


        $otheremail = "";
        $otherparty2 = "";
        $membercount = 0;
        $listenercount = 0;
        $i = 0;
        $avatarblock = "";
        $avatarblocklong = "";
        $techsupport = "";
        $firstparty = '';
        
        while($row = pdo_fetch($result)){
            
            if($row['avatarurl']=="$rootserver/img/faceless.png"){
                //$row['avatarurl']="$rootserver/img/newbie.jpg";
            }
            if($row['avatarurl']==""){
               $_SESSION['avatarurl'] = "$rootserver/img/newbie.jpg"; 
            }
            
            if($i == 0){
                $otherparty2 =
                "<span class='chatdeleteparty' style='cursor:pointer' 
                    data-chatid='$chatid' 
                    data-providerid='$row[otherid]'>
                        $row[providername]
                </span> ";
                $otheremail = $row['otheremail'];
                if($row['handle']!=''){
                    $otheremail = $row['handle'];
                }
                $membercount = intval($row['membercount']);
                $listenercount = intval($row['listenercount']);
                $firstparty = $row['providername'];
            }
            $i++;
            if($i < 12){
                $avatarblock .= "<img class='togglememberson icon50' src='$row[avatarurl]' />";
            }
            $listener = "";
            if($row['broadcaster']!=''){
                $listener = "(Listening)";
            }
            $avatarblocklong .= "<img title='$row[handle] $listener' class='togglememberson icon50' src='$row[avatarurl]' />";
            if($row['techsupport']=='Y'){
                $techsupport = 'Y';
            }
            
        }
        if($techsupport=='Y'){
            $avatarblock = "";
            $avatarblocklong = "";
            $otherparty2 = "Tech Support";
            $firstparty = "Tech Support"; 
        }
        $avatarblock = "";
        
        
        if($membercount > 2){
            $array['otherparty2'] = $otherparty2."+ ($membercount members)";
        } else {
            $array['otherparty2'] = $otherparty2;
        }
        
        $array['otheremail'] = $otheremail;
        $array['membercount'] = $membercount;
        $array['listenercount'] = $listenercount;
        $array['firstparty'] = $firstparty;
        $array['displaytwopartyonly'] = '';
        if($membercount > 2){
            $array['displaytwopartyonly'] = "display:none";
        }
        $array['avatarblock'] = $avatarblock;
        $array['avatarblocklong'] = $avatarblocklong;
            
        
        return (object) $array;
    
}
function ShowChatMessages($chatid, $providerid, $limit, $passkey, $passkey64, $chatparty, $togglemsg, $togglemsgmembers, $script, $more, $owner, $hidemode, $membercount, $mute, $chatobj  )
{
    global $timezoneoffset;
    global $rootserver;
    global $enableAdminDeleteMode;      
    global $global_separator_color;
    global $global_activetextcolor;
    global $global_activetextcolor_reverse;
    global $global_textcolor2;
    global $global_background;
    global $global_background2;
    global $global_bottombar_color;
    global $backgroundcolor;
    global $textcolor;
    global $textcolor2;
    global $iconsource_braxpen_common;
    global $iconsource_braxarrowright_common;
    global $iconsource_braxarrowleft_common;
    global $iconsource_braxclose_common;
    global $iconsource_braxeraser_common;
    global $iconsource_braxrestore_common;
    global $iconsource_braxedit_common;
    global $menu_mutenotifications;
    global $menu_unmutenotifications;
    global $menu_displayinglast;
    global $menu_messages;
    global $menu_cancel;
    global $menu_save;
    global $menu_edit;
    global $braxmedal;
    global $braxnewbie;
    
    if($_SESSION['superadmin']=='Y'){
        //$hidemode = 'Y';
    }
    $mutemsg = "$menu_mutenotifications";
    if($mute == 'Y'){
        $mutemsg = "Unmute Notifications";
    }
    $chat = "";
    $count = 0;
    $replyicon = "<img class='icon15' src='../img/reply.png' style='z-index:2' title='Reply to' />";
    $roomdesc = $chatobj->roomdesc;

    
    $chat .= "
            $script
            <div class='inputfocuscontent2' style='overflow-y:hidden;background-color:transparent;color:$textcolor;position:relative;top:0px;padding:0px;margin:0;width:100%;'>
                $chatparty
                <br>
                <div class='smalltext' style='display:inline;color:$textcolor;padding-top:5px;padding-left:20px;padding-right:10px'>
                    $menu_displayinglast $limit $menu_messages
                </div>
                
                    ";
    if(  (($membercount <=2 || $owner == $providerid) ) ){
    $chat .= "
                    
                    &nbsp;
                    &nbsp;
                    <div class='quizbutton tapped mainfont' data-chatid='$chatid' data-mode='clear' style='white-space:nowrap;display:inline;padding-left:10px'>
                       <img class='icon20' src='$iconsource_braxeraser_common' title='Temporarily Archive Past Comments' />
                    </div>
                    &nbsp;
                    <div class='quizbutton tapped mainfont' data-chatid='$chatid' data-mode='unclear' style='white-space:nowrap;display:inline;padding-left:10px'>
                       <img class='icon20' src='$iconsource_braxrestore_common' title='Restore All Comments' />
                    </div>
                    ";
    }
    $chat .= "
                <br>
                <div class='mute mainfont' data-chatid='$chatid' data-roomid='' style='display:inline-block;padding-left:20px;padding-right:10px;padding-top:10px;cursor:pointer;color:$global_activetextcolor;' title='Mute Notificsations'>
                    $mutemsg
                </div>
                <span class=''>
                    <div class='togglechat mainfont' style='display:inline-block;padding-left:20px;padding-right:10px;padding-top:10px;cursor:pointer;color:$global_activetextcolor;' title='$togglemsg'>
                        $togglemsg
                    </div>
                </span>
                <br><br>
            ";
    
    


    $lifespanfilter = "";
    if( intval($_SESSION['msglifespan'])>0){
        //$lifespanfilter = " and timestampdiff( MINUTE, chatmessage.msgdate, now() ) < $_SESSION[msglifespan] ";
    }
    $hidemodefilter = "";
    if( $owner != $providerid && $hidemode == 'Y' ){
        //See only yourself and the owner
        $hidemodefilter = " and chatmessage.providerid in ($providerid, $owner ) ";
    }
    //Normal
    $blockedfilter = "and blocked1.blockee is null and blocked2.blocker is null";
    //Test if admin blocked, admin still sees content
    if($_SESSION['superadmin']=='Y'){
        //$blockedfilter = "and blocked2.blocker is null";
    }
    


    $result = pdo_query("1",
        "
        select * from
        (
            select distinct chatmessage.message, chatmessage.encoding, chatmessage.msgid,
            DATE_FORMAT(date_add(chatmessage.msgdate, interval (?)*(60) MINUTE), 
            '%m/%d/%y %h:%i:%s%p') as msgdate, 
            chatmembers.techsupport, chatmessage.flag,
            chatmessage.providerid, chatmessage.chatid,
            provider.providername, provider.avatarurl, chatmaster.owner,
            provider.medal, provider.handle, provider.active,
            datediff(curdate(), provider.createdate) as accountage,

            staff.staffname as name, blocked1.blockee, blocked2.blocker, provider.profileroomid,
            ban.banid

            from chatmessage 
            left join chatmembers on chatmembers.chatid = chatmessage.chatid and chatmembers.providerid = chatmessage.providerid
            left join chatmaster on chatmessage.chatid = chatmaster.chatid 
            left join provider on provider.providerid = chatmessage.providerid 
            left join staff on staff.providerid = chatmessage.providerid and
               staff.loginid = chatmessage.loginid
            left join blocked blocked1 on blocked1.blockee = chatmessage.providerid and blocked1.blocker = ? 
            left join blocked blocked2 on blocked2.blocker = chatmessage.providerid and blocked2.blockee = ?
            left join ban on ban.banid = provider.banid and ban.chatid = chatmaster.chatid
            where chatmessage.chatid = ? and chatmessage.status='Y'  
            $blockedfilter
            $lifespanfilter
            $hidemodefilter
            and (ban.banid is null or (ban.banid = ? and ban.chatid = ?) or '$_SESSION[superadmin]'='Y' )
            and 
            ( 
                (
                chatmessage.providerid not in (select blockee from blocked where (blocker=? or blocker=0) )
                and 
                chatmessage.providerid not in (select blocker from blocked where blockee=? )
                ) or chatmessage.providerid = ? or 'Y'='$_SESSION[superadmin]'
            )
            and chatmessage.loginid is not null

            order by chatmessage.msgid desc limit $limit 
         ) as t order by msgid asc
        ",array($timezoneoffset,$providerid,$providerid,$chatid,$_SESSION['banid'],$chatid,$providerid,$providerid,$providerid));
        

    $count = 0;

    while($row = pdo_fetch($result)){
        
        $msgdate = InternationalizeDate($row['msgdate']);
        
        $dataname = $row['name'];
        
        if($row['providername']!=$row['name']){
            $row['name'] = "$row[providername]";
        }
        $avatarurl = RootServerReplace($row['avatarurl']);
        if( $row['techsupport']=='Y'){
            $row['name']='Tech';
            $avatarurl = "../img/techsupport-128.png";
        }
        if($row['avatarurl']=="$rootserver/img/faceless.png"){
            $avatarurl="$rootserver/img/newbie2.jpg";
        }
        if($row['avatarurl']==""){
            $avatarurl="$rootserver/img/newbie.jpg";
        }
        if(($row['avatarurl']=="$rootserver/img/faceless.png" || $row['avatarurl']=="") && intval($row['accountage']) < 3 ){
            $avatarurl="$rootserver/img/newbie.jpg";
        }
        if($row['active']=='N'){
            $row['name'] = $row['handle']. " [Inactive]";
            $avatarurl="$rootserver/img/newbie.jpg";
            
        }
        if( intval($row['accountage']) < 3 && $row['active']=='Y'){
            $row['name'] = $row['name']. " [New]";
            
        }
        $profileroomid = $row['profileroomid'];
        $action = 'feed';
        if(intval($profileroomid)==0){
            $action = 'userview';
        }
        if($providerid == $row['providerid']){
            $action = '';
            
        }
        $usermedal = '';
        if($row['medal']=='1'){
            $usermedal = $braxmedal;
        }

        $oldchat = "";
        //if( $count < $total && toggleChat ){
        //    $oldchat = 'oldchat';
        //}
        $decode = '(Content not available)';
        if($row['blockee']!=''){
            $decode = '(Blocked)';

        }
            
        if($row['blockee']=='' && $row['blocker']==''){
            $decode = DecryptChat( $row['message'], $row['encoding'],"$chatid","$passkey");
        }
        $decode = WrapLinks($decode);
        if(substr($decode,0,4)=="&gt;"){
            $decode = $replyicon.substr($decode,4);
        }
        if(substr($decode,0,1)==">"){
            $decode = $replyicon.substr($decode,1);
        }

        
        //Admin Delete Mode
        $deleteitem = '';
        $edititem = "";
        $edititem2 = "";
        
        if( $row['providerid']==$providerid ||
                $enableAdminDeleteMode ){
            
        
            $deleteitem = " 
                    <img class='chatedititem chatdeleteitem tapped2 icon15' 
                        title='Delete Post' alt='Delete Post'
                        data-msgid='$row[msgid]' src='$iconsource_braxclose_common' 
                         style='cursor:pointer;position:relative;
                         display:inline-block;
                         width:auto;top:3px;margin-right:30px' />
                ";

            $decodeEdit = ChatCleanForEdit($decode);

            if($decodeEdit !== false){
                
            
                $edititem = "
                <img class='chatedititem tapped2'
                         data-msgid='$row[msgid]'    
                         src='$iconsource_braxedit_common' 
                         style='padding-top:3px;padding-left:15px;padding-right:15px;height:20px;cursor:pointer' />
                         ";
                $edititem2 = "
                <span class='chateditcontent' id='chatedit-$row[msgid]' style='display:none'>
                    <br><br>
                    <div class='chatedititem tapped2' style='display:inline-block;cursor:pointer' 
                        data-msgid='$row[msgid]' 
                         >
                        <img class='icon15' title='Cancel Edit' alt='Cancel Edit' src='$iconsource_braxarrowleft_common' style='top:3px' /> $menu_cancel
                    </div>
                    &nbsp;&nbsp;&nbsp;
                    <div id='sendchatbutton' class='sendchatbutton chateditpost tapped' style='display:inline-block;cursor:pointer' 
                        data-chatid='$chatid' data-msgid='$row[msgid]' 
                        data-sms='' data-name='' data-send='Y' data-passkey64='$passkey64'  >
                        <img class='icon15' title='Save Changes' alt='Save Changes' src='$iconsource_braxarrowright_common' style='top:3px' /> $menu_save
                    </div>
                    &nbsp;&nbsp;$deleteitem
                    <textarea data-msgid='$row[msgid]' id='chatcontent2-$row[msgid]' 
                        class='mainfont chateditingactive' 
                        style='width:100%;height:300px;margin-top:20px'
                        >$decodeEdit</textarea>
                    <br><br><br>
                </span>$more 
                ";
                
            } else {
                
                $edititem = $deleteitem;
            }
        }
        

        if($decode!=''){
            
            if( $row['providerid']==$providerid 
              ){


                $chat .= " 
                        <div class='chatitem $oldchat chatself mainfont' style='background-color:transparent;color:$textcolor2;padding-left:10px;padding-right:10px;margin-top:0px;;'>

                            
                            <div class='bubblesize' style='background-color:transparent'>
                                <div class='circular gridnoborder' style='float:left;height:30px;width:30px;overflow:hidden;background-color:white;margin-right:10px;padding-bottom:0px'>
                                    <img class='$action' src='$avatarurl'  title='Your Profile Photo' alt='Your Profile Photo' style='cursor:pointer;min-height:100%;max-width:100%'
                                        data-providerid='$row[providerid]' data-name='$dataname'    
                                        data-roomid='$row[profileroomid] data-caller='leave'
                                        data-mode ='S' data-passkey64=''
                                     />
                                 </div>
                                <span class=chatselftext style='float:left;color:$textcolor'>$row[name]&nbsp;$usermedal&nbsp;</span> 
                               <div class='smalltext2' style='display:inline-block;height:20px;color:$global_textcolor2;font-family:helvetica;position:relative;top:5px;vertical-align:top'>$msgdate</div>
                              <div class='chatcontent' id='chatcontent-$row[msgid]'>$decode $edititem</div>$edititem2
                            </div>
                        </div>";
                    
                
            } else {

                
                $chat .= "<div class='chatitem $oldchat chatother mainfont' style='background-color:transparent;color:$textcolor;padding-left:10px;;padding-right:10px;margin-top:0px;padding-bottom:0px'>
                    

                            <div class='bubblesize' style='background-color:transparent'>
                                <div class='circular gridnoborder' style='float:left;height:30px;width:30px;overflow:hidden;background-color:$backgroundcolor;margin-right:10px'>
                                    <img class='$action' src='$avatarurl' title='Profile Photo' alt='Profile Photo' style='cursor:pointer;min-height:100%;max-width:100%'
                                        data-providerid='$row[providerid]' data-name='$dataname'    
                                        data-roomid='$row[profileroomid]' data-caller='leave'
                                        data-mode ='S' data-passkey64=''
                                     />
                                 </div>
                                <div class='chatothertext' data-reply='$dataname' 
                                        style='color:$textcolor;float-left;padding-bottom:0px;padding-top:0px;font-weight:normal;cursor:pointer'>
                                         <b style='cursor:pointer'>$row[name]</b>&nbsp;$usermedal&nbsp;
                                    <span class='smalltext2' style='color:$global_textcolor2;font-family:helvetica;'>$msgdate</span>
                                </div>
                                <div class='chatcontent' id='chatcontent-$row[msgid]'>$decode $edititem</div>$edititem2
                            </div>
                        </div>";
            }
            $chat .= "";
        }

        $count++;
    }
    if($roomdesc!=''){
        $chat .= "<div class='smalltext2' id=chatbottom style='padding-top:10px;padding-left:20px;padding-right;20px;background-color:$global_bottombar_color;color:$global_activetextcolor_reverse'><b>Chat Topic: $roomdesc</b> </div>";
    }
    $chat .= "</div>";
    //$chat .= "<div class='smalltext2' id=chatbottom style='float:left;background-color:$global_background;display:inline;color:transparent'>.</div>";
    $array['chat']=$chat;
    $array['count']=$count;
    return (object) $array;

        
}
function MemberList($chatid, $providerid, $title, $keyhash, $streaming )
{
    global $timezoneoffset;
    global $rootserver;
    
    $otherparty = "";
    $otheremail = "";
    $otherparty2 = "";
    $firstparty = "";
    $technotes = "";
    $techsupport = false;
    $lastseen = "";
    $radio = "";
    if($streaming){
        $radio = "Y";
    }
    $result = pdo_query("1",
        "
        select provider.providername, provider.providerid as otherid,  
        provider.replyemail as otheremail, provider.avatarurl,
        provider.handle, chatmembers.techsupport, chatmembers.broadcaster,
        provider.profileroomid,
        chatmaster.owner,
        DATE_FORMAT(
           date_add(chatmembers.lastread, interval (?)*(60) MINUTE),
            '%b %d/%y %a  %h:%i:%s%p'
        ) as seen,
        chatmembers.lastread,
        timestampdiff( HOUR, now(), chatmembers.lastread) as hourdiff,
        (select count(*) from chatmembers c2 where c2.chatid = ? and c2.broadcaster is not null and c2.broadcaster > 0 ) as listenercount
        from chatmembers
        left join chatmaster on chatmaster.chatid = chatmembers.chatid
        left join provider on provider.providerid = chatmembers.providerid 
        where chatmembers.chatid = ?
        and provider.active = 'Y'
        order by chatmembers.lastread desc 
        ",array($timezoneoffset,$chatid,$chatid));

    $i = 0;
    $displayed = 0;
    while($row = pdo_fetch($result)){

        $i++;
        $seen = $row['seen'];
        $listenercount = $row['listenercount'];
        if($row['handle']!='' ){
            $row['otheremail'] = $row['handle'];
        }
        if($otheremail=='' && $row['otherid']!=$providerid){

            $otheremail = $row['otheremail'];
            if($row['handle']!=''){
                $otheremail = $row['handle'];
            }
            if($firstparty == ''){
                $firstparty = $row['providername'];
            }
        }
        if($lastseen!=''){
                $lastseen .= ", ";
        }
        if( $row['techsupport']=='Y'){
            $row['providername']='Tech Support';
            $row['otheremail']='support@brax.me';
            $row['avatarurl']="$rootserver/img/techsupport-128.png";
            $techsupport = true;
        }
        if( $row['otherid']!=$providerid && $i<3){
            $technotes = GetTechNotes($row['otherid'], $chatid);
        }
        $avatarurl = RootServerReplace("$row[avatarurl]");
        if($row['avatarurl']==''){
            $avatarurl = "$rootserver/img/newbie2.jpg";
        }
        if($avatarurl == "$rootserver/img/faceless.png"){
            $avatarurl = "$rootserver/img/newbie2.jpg";
        }
        if( $seen!='' && intval($seen)>0){
            $unread = $seen;
            $unread_scale = 'secs';
            if($unread > 60){
                $unread = round($unread/60, 0);
                $unread_scale = 'min';
            }
            if($unread > 60){
                $unread = round($unread/60, 0);
                $unread_scale = 'hrs';
            }
            $lastseen .= "Unread $unread $unread_scale";
        }
        if( $seen == ""){
            $lastseen .= "$row[providername] ---";
        } else {
            $lastseen .= "$row[providername] - $seen";
        }
        
        $keyresend = "";
        if($keyhash!=''){
            $keyresend = 
                    "
                    <div class='smalltext addchatsession'
                        data-chatid='$chatid' data-providerid='$row[otherid]' data-mode='S'
                        style='cursor:pointer;color:gold;padding-top:0px'>
                        Resend Private Key
                    </div> 
                    ";
        }
        if($row['broadcaster']>0){
            $keyresend = 
                    "
                    <div class='smalltext' style='color:gold'>Listening</div>
                    ";
            
        }
        $diff = -2;
        if(!$streaming){
            $diff = -2160;
        }
        
        if( $row['hourdiff']> $diff && $row['lastread']!='' && $i< 1000){
       
            $deleteaction = "";
                $deleteopacity = "opacity:0.3;";
            if($row['owner']==$providerid || $row['otherid']==$providerid){
                $deleteaction = "chatdeleteparty";
                $deleteopacity = "opacity:1.0;";
            }
            
            $displayed++;
            $otherparty .= " 
                <div style='display:inline-block;height:90px;width:120px;margin:0px;vertical-align:top;text-align:left;padding:20px'> 
                        <img class='$deleteaction smalltext2 icon20' 
                            title='Delete from Chat'
                            src='../img/delete-circle-white-128.png'
                            data-chatid='$chatid' 
                            data-providerid='$row[otherid]' 
                            data-mode='$radio'
                            style='float:left;cursor:pointer;$deleteopacity' 
                             />
                        <div class='circular gridstdborder icon50' 
                            style='; position:relative;top:0px;
                            overflow:hidden;background-color:white;vertical-align:top'>
                            <img class='feed icon50' 
                                src='$avatarurl'
                                data-providerid='$row[otherid]' 
                                data-roomid='$row[profileroomid]'
                                 data-profile='Y'
                                data-mode='S'
                                data-caller='leave'
                                data-passkey64=''
                                style='height:100%;width:auto;cursor:pointer;
                                background:white;;
                                position:relative;top:0px;'

                            />
                        </div>
                        <div class='smalltext2' 
                            style='cursor:pointer;color:white;padding-top:0px'>
                            <span class='smalltext'>$row[providername]</span><br>
                            ($row[otheremail])<br>
                            $seen 
                        </div> 
                        $keyresend
                </div>
                ";
        }


        if($i == 1){
            $otherparty2 =
            "<span class='chatdeleteparty' style='cursor:pointer' 
                data-chatid='$chatid' 
                data-providerid='$row[otherid]'>
                    $row[providername]
                    <span class='smalltext2'>($row[otheremail])
                    </span> 
            </span> ";
        }
        if($i == 3){
            $otherparty2 .= "...";
        }
        if($title!=''){
            $otherparty2 = "$title";
        }
    }
    if($i > 2){
        $otheremail = '';
        $otherparty2 .= "($i Members)";
    }
    if($otherparty == ''){
        $result = pdo_query("1",
            "
            select name from invites where chatid = ? 
            ",array($chatid));


        $otherparty = "";
        while($row = pdo_fetch($result)){
            $otherparty = $row['name']." (Invited)";
        }
        
    }
    if((!$techsupport && $_SESSION['superadmin']!='Y') || $i > 2 
    ){
        $technotes = "";
    }
    
    
    $array['otherparty']= $otherparty."<br><br><span class='smalltext' style='color:gold'>$displayed Recent Visitors</span>";
    $array['otherparty2'] = $otherparty2;
    $array['otheremail'] = $otheremail;
    $array['membercount'] = $i;
    $array['technotes'] = $technotes;
    $array['firstparty'] = $firstparty;
    $array['lastseen'] = $lastseen;
    $array['listenercount'] = $listenercount;
    
    return (object) $array;
}
function BroadcastInfo($streaming, $broadcastmode, $broadcaster, $chatid, $streamid, $title, $owned, $broadcasttitle, $listenercount, $radiostation )
{
    $broadcastowner = "";
    $broadcastownermobile = "";
    $broadcastmobile1 = "";
    $broadcastmobile2 = "";
    $broadcastdiv = "";
    $broadcast = "";
    $video = "";
    $video2 = "";
    $stop = "";
    $play = "";
    $mode = "";
    
    global $global_titlebar_color;
    global $global_streamlive_color;
    global $iconsource_braxstopmusic_common;
    global $iconsource_braxplaymusic_common;
    global $iconsource_braxbell_common;
    global $iconsource_braxlike_common;
    global $iconsource_braxunlike_common;
    global $iconsource_braxvideo_common;
    global $global_background;
    global $global_textcolor;
    

    if($broadcasttitle == ''){
        $broadcasttitle = "is Live";
    }
    
    $radiostationinfo = RadioStationHeader( $broadcastmode, $broadcasttitle,$title, $streamid, $broadcaster, $listenercount,$chatid );
    if( $streaming ){
        
        
        //Get Video Source
        $result = pdo_query("1","select url from chatpopup where chatid=? and url!='' and broadcaster in 
            (select broadcaster from chatmaster where chatid=? ) ",array($chatid,$chatid));
        if($row = pdo_fetch($result)){
            $audiopanelmode = "audiopanel_desktop";
            if($_SESSION['innerwidth']<1000){
                $mode = 'M';
                $audiopanelmode = "audiopanel_mobile";
            }
                
            $video = " 
                    <img class='$audiopanelmode icon25' data-mode='$mode' src='$iconsource_braxplaymusic_common' title='Play Movie' data-chatid='$chatid' style='margin-left:10px;top:3px' />
                     ";
            $video2 = " 
                    <img class='$audiopanelmode icon30' data-mode='$mode' src='$iconsource_braxplaymusic_common' title='Play Movie'  data-chatid='$chatid' style='margin-left:10px;top:3px' />
                    ";
                
        }
        if($broadcastmode!='V'){
            $play = "
                    <img class='icon25 audiostream' 
                          title='Play Audio'
                          data-streamid='$streamid' data-chatid='$chatid'  data-mode='START'
                        src='$iconsource_braxplaymusic_common' style='margin-left:10px;' />
            ";
            $video = "";
            $video2 = "";
        }
        
    }
    
    if( $streaming ){
        
        $radiocolor = "$global_background;color:$global_textcolor";
        
            if($broadcaster == ''){
                $broadcast = "<div style='text-align:center;background-color:$global_streamlive_color;color:white;cursor:pointer' class='setchatsession' data-chatid='$chatid'  title='Refresh'>Audio Stream Source Active</div>";
                //$broadcastmobile1 .= $broadcast;
            } else {
                $broadcast = 
                    " 
                    <div class='icon30' style='min-height:45px;float:left'></div>
                        
                    $play
                    $video

                    <img class='icon25 audiostream' 
                          title='Stop'
                        data-chatid='$chatid' data-mode='STOP' 
                         src='$iconsource_braxstopmusic_common' style='margin-left:15px' />
                         
                    <img class='notifyaudiostream icon25' src='$iconsource_braxbell_common' style='margin-left:15px;' 
                          title='Notify Members'
                       data-chatid='$chatid' data-mode='STREAM' 
                    />
                    <img class='notifyaudiostream icon25' src='$iconsource_braxlike_common' style='margin-left:15px;'
                        title='Like'
                       data-chatid='$chatid' data-mode='LIKE'
                     />
                    <img class='notifyaudiostream icon25' src='$iconsource_braxunlike_common' style='margin-left:15px;' 
                        title='Unlike'
                       data-chatid='$chatid' data-mode='UNLIKE'
                    />
                    <img class='refreshchatsession icon25' src='$iconsource_braxunlike_common' style='opacity:0.0;margin-left:0px;top:3px;' 
                          title='Refresh'
                       data-chatid='$chatid'
                    />
                    <br><br>
                    ";
                
            }
        
            if($broadcaster == '' && $_SESSION['broadcaster']!='N'){
                $broadcastowner = 
                    " 
                        <br>
                        &nbsp;&nbsp;<b>Broadcast Info</b>
                        <br>
                        &nbsp;&nbsp;<input class='chatextraarea chatwidth2 dataentry' id='audiostreamtitle' type='text'  maxlength=40 placeholder='Broadcast Title' title='Enter Broadcast Title Here' />
                        <br><br>&nbsp;
                        <div class='notifyaudiostream divbutton3' style='background-color:$global_titlebar_color;color:white' 
                              data-chatid='$chatid' data-mode='BROADCASTER'  data-action=''>
                           <img class='icon15' src='../img/Microphone-2-White_120px.png' title='Claim the Broadcast' />
                           I am the Broadcaster
                        </div>
                        <br><br>
                ";
                $broadcastownermobile = $broadcastowner;
            } 
            
            if( $owned) { //BOOL
                $broadcast = "<div style='min-height:25px;float:left;padding-top:5px;background-color:$radiocolor;width:100%'>
                    ";

                if($broadcastmode=='V'){
                    $broadcast .= 
                        "
                        <img class='notifyaudiostream icon25' src='../img/close-broadcast-128.png' style='margin-left:10px;top:3px;' 
                              title='Stop Video Broadcast'
                           data-chatid='$chatid' data-mode='ENDBROADCAST'
                        />
                        ";
                } else {
                    
                }
                
                $broadcast .= 
                    "
                        $play
                        $video
                        <img class='icon25 audiostream' 
                              title='Stop Monitoring of Broadcast'
                            data-chatid='$chatid' data-mode='STOP' 
                             src='$iconsource_braxstopmusic_common' style='margin-left:10px;top:3px' />
                        <img class='notifyaudiostream icon25' src='$iconsource_braxbell_common' style='margin-left:15px;top:3px;' 
                              title='Notify Members'
                           data-chatid='$chatid' data-mode='STREAM'
                        />
                        <img class='notifyaudiostream icon25' src='$iconsource_braxlike_common' style='margin-left:15px;top:3px;'
                              title='Like'
                           data-chatid='$chatid' data-mode='LIKE'
                         />
                        <img class='notifyaudiostream icon25' src='$iconsource_braxunlike_common' style='margin-left:15px;top:3px;' 
                              title='Unlike'
                           data-chatid='$chatid' data-mode='UNLIKE'
                        />
                        ";
                $broadcast .= 
                    "
                        <br><br><div class='audiopanelbroadcaster' data-chatid='$chatid' data-mode='$mode' style='display:inline'></div>
                    ";
                $broadcast .= "</div>";
                
                $broadcastowner = 
                    " 
                        <br>
                        <hr class='formobile' />
                        &nbsp;&nbsp;<b>Change Broadcast Info</b><br>
                        &nbsp;&nbsp;<input class='chatwidth2' id='audiostreamtitle' type='text' size=80 maxlength=40 placeholder='New Broadcast Title' style='max-width:250px' title='Enter new broadcast title here' />
                            <img class='notifyaudiostream icon25' src='../img/Arrow-Right-in-Circle-White_120px.png' 
                                title='Save New Title'  
                                  data-chatid='$chatid' data-mode='BROADCASTER' data-action='TITLE' />
                        
                        &nbsp;&nbsp;
                        <br>
                ";
                $broadcastownermobile = 
                    " 
                        <br>
                        <hr class='formobile' />
                        &nbsp;&nbsp;<b>Change Broadcast Info</b><br>
                        &nbsp;&nbsp;
                        <input class='chatwidth2' id='audiostreamtitle' type='text' size=80 maxlength=40 
                            placeholder='New Broadcast Title' style='max-width:250px' 
                            title='Enter new broadcast title here' />
                            
                            <img class='notifyaudiostream icon25' src='../img/Arrow-Right-in-Circle_120px.png' 
                                title='Save New Title'  
                                  data-chatid='$chatid' data-mode='BROADCASTER' data-action='TITLE' />
                        
                        &nbsp;&nbsp;
                ";
                if($broadcastmode == 'V'){
                    
                    $stop = "
                        <br><br>&nbsp;&nbsp;
                        <div class='notifyaudiostream divbuttontext' 
                             data-mode='ENDBROADCAST' data-chatid='$chatid' title='Stop Video Broadcast' 
                             style='color:white;background-color:$global_titlebar_color'>Stop Video Broadcast
                        </div>
                        <br><br>
                        ";
                    
                }
                $broadcastowner .= $stop;
                $broadcastownermobile .= $stop;
                
            } else {
                if( $broadcastmode == 'V'   && 
                       
                         
                    $_SESSION['superadmin']=='Y'){
                    $broadcastowner .= "
                        <br>
                        &nbsp;&nbsp;<div class='notifyaudiostream divbuttontext' data-mode='ENDBROADCAST' data-chatid='$chatid' style='color:white;background-color:$global_titlebar_color'>Moderator Stop</div>
                        <br><br>
                        ";
                    
                }
                
            }
            
    } else {
        
    /* STREAMING IS OFF
     * 
     */
        
        if($streamid !='' && $radiostation!='Q'){
            
            AutoEndBroadcast($chatid);
            
            $broadcast = "
                    <div class='gridnoborder smalltext' 
                        style='min-height:25px;cursor:pointer;padding-bottom:10px;background-color:$global_background;color:$global_textcolor;vertical-align:center;
                        width:100%'>

                        <div class='notifyaudiostream mainfont' style='float:left;margin-left:30px'
                            title='Broadcast'
                            data-action='VIDEO'
                            data-mode='BROADCASTER'
                            data-chatid='$chatid'
                           data-chatid='$chatid' data-mode='LIVE' >
                            <img class='icon25' 
                                src='$iconsource_braxvideo_common' style='position:relative;top:5px' /> &nbsp;Stream Video
                        </div>


                       <br><br>
                    </div>";

        }
    }    
    if($_SESSION['broadcaster']!='N'){
        $broadcastdiv = "<div class='broadcastdiv' style='background-color:$global_background;min-height:25px;margin:0px;'>$broadcast</div>";
    }
    //} else {
    //    $broadcastdiv = "<span class='nonmobile'><div style='min-height:1px;margin:0px;'>$broadcast</div></span>";
    //}
    
    $array['broadcastowner'] = $broadcastowner;
    $array['broadcastownermobile'] = $broadcastownermobile;
    $array['radiostationinfo'] = $radiostationinfo;
    $array['broadcast'] = $broadcastdiv;
    $array['broadcastmobile1'] = $broadcastmobile1;
    $array['broadcastmobile2'] = $broadcastmobile2;

    return (object) $array;
        
}
function RadioStationHeader( $broadcastmode, $broadcasttitle,$title, $streamid, $broadcaster, $listenercount, $chatid )
{
    global $global_titlebar_color;
    global $global_titlebar_alt_color;
    global $global_menu2_color;
    global $global_background;
    global $global_textcolor;
    
    $radiocolor = "$global_menu2_color;color:white";
    //$radiocolor = "whitesmoke;color:black";


    $radiostationinfo = "
            <div class='gridnoborder smalltext  selectchatlist' 
                data-chatid='$chatid' data-mode='LIVE' 
                style='padding:5px;background-color:$radiocolor;width:100%;margin:auto;height:30px;
                text-align:center;cursor:pointer;position:relative;top:0px;cursor:pointer' >
                <img class='icon20' 
                      title='Back'
                     src='../img/Arrow-Left-in-Circle-White_120px.png' style='float:left;margin-left:10px' />
                
                $title - Mount ID: $streamid
            </div>";

    if($broadcaster!=''){
        $radiostationinfo = "
            <img class='icon20 selectchatlist' 
                  title='Back'
                data-chatid='$chatid' data-mode='LIVE' 
                 src='../img/Arrow-Left-in-Circle-White_120px.png' style='float:left;margin-left:10px' />
            <div class='gridnoborder smalltext' 
                style='padding:5px;background-color:$radiocolor;width:100%;height:30px;
                margin:auto;text-align:center'>
                <span class='togglememberson tapped' style='cursor:pointer;position:relative;top:0px'>
                    <b>$broadcaster - $title</b>
                    $broadcasttitle 
                    <span class='smalltext' title='Active Listener Count' style='color:white'>($listenercount)</span>
                </span>
                <br>
             </div>";
        if($broadcastmode == 'V'){
            //hide the heading on Mobile video so there's more space for the video
            $radiostationinfo = "<span class='nonmobile'>".$radiostationinfo."</span>";
        }
    }
    return $radiostationinfo;
    
}
function ChatHeading($broadcastmode, $chatid, $title, $owned, $hidemode)
{

    global $global_menu2_color;
    global $global_activetextcolor;
    global $global_titlebar_alt_color;
    global $global_textcolor;
    global $backgroundcolor;
    global $global_textcolor;
    global $icon_braxchat2;
    global $menu_chat;
    global $iconsource_braxarrowleft_common;
    global $iconsource_braxarrowright_common;
    global $iconsource_braxeraser_common;
    global $iconsource_braxrestore_common;
    
    $video = "";
    $video2 = "";
    $stop = "";
    $videobuttons = "";
    if($owned){
        $videobuttons = "<br>";
    }
    
    $heading = 
            "<div class='gridnoborder smalltext selectchatlist' 
                    data-chatid='$chatid' data-mode='CHAT' 
                style='padding:10px;background-color:transparent;color:$global_textcolor;width:100%;margin:auto;
                text-align:left:pointer;position:relative;top:0px' >
                <img class='icon20' 
                      title='Back'
                     src='$iconsource_braxarrowleft_common' style='margin-left:10px' />
                     &nbsp;&nbsp;&nbsp;<span class='mainfont' style='color:$global_textcolor'>
                     
                    $menu_chat $title
            </div>";

    
    
    return $heading;
    
}
function MemberInfo($radiostation, $chatparty, $hidemembers, $chatid, $otheremail, $keyhash, $technotes, $otherparty )
{
    $operation = "Chat";
    if($radiostation=='Y'){
        $operation = "Live Streaming Channel";
    }
    
    $chat  = "
            <div class='oldchat' style='padding:0px;margin:0;color:white;background-color:black;$hidemembers'>
                $chatparty
                <div style='padding:20px'>
                    <span class='pagetitle2' style='color:white'>
                        $operation Members
                    </span>
                    
                    <span class='smalltext oldchat' style='color:white;$hidemembers'>
                        <br>
                        <br><br>
                        
                        <div class='setchatsession mainfont' 
                                data-otherid='$otheremail' data-chatid='$chatid' data-keyhash='$keyhash'
                            style='display:inline;cursor:pointer;color:white;text-align:right'>
                            Back to $operation
                        </div>
                    </span> 
                    <br><br>
                    $otherparty 
                    <br><br>
                    <span class='smalltext oldchat' style='color:white;$hidemembers'>
                        <br>
                        <br><br>
                        <div class='setchatsession mainfont' 
                                data-otherid='$otheremail' data-chatid='$chatid' data-keyhash='$keyhash'
                            style='display:inline;cursor:pointer;color:white;text-align:right'>
                            Back to $operation
                        </div>
                    </span> 

                    <br><br>
                </div>
            </div>
            $technotes

            ";
    return $chat;
    
}
function MobileMenu( 
        $providerid, $chatid, $passkey64, $archive, 
        $streaming, $radiostation, 
        $broadcastowner, $allowdelete,  
        $otheremail,  $notactive_e2e, $archivemode,
        $broadcastownermobile, $mobilelabel, $togglemsg )
{
    
        global $global_background;
        global $global_titlebar_color;
        global $menu_sharefile;
        global $menu_sharephoto;
        global $menu_uploadphoto;
        global $menu_addparty;
        global $menu_refresh;
        global $menu_takephoto;
        global $menu_members;
    
        $chat = "";
        if(!isset($_SESSION['sizing'])){
            return;
        }
        
        if($_SESSION['sizing'] < 450){
            $iconwidth = '320px';
        } else {
            $iconwidth = '50px';
        }
        
        $deletechat = "";
        if($allowdelete){
            
            $buttondelete = "
                <table class='gridnoborder smalltext endchatbutton tapped' id='endchatbutton' 
                    data-archive='$archive' data-chatid='$chatid' 
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped'  
                        src='../img/delete-circle-128.png' 
                        style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                        $archivemode 
                    </td>
                </tr>
                </table>
            ";
        } else {
            
            if($radiostation=='Y'){
                
                $deletemode = 'R';
                $deletetext = 'Station';
                
            } else {
                
                $deletemode = 'L';
                $deletetext = 'Chat';
            }
            
            $buttondelete = "
                <table class='gridnoborder smalltext chatdeleteparty tapped' 
                    title='Leave $deletetext'
                    data-chatid='$chatid' 
                    data-providerid='$providerid' 
                    data-mode='$deletemode'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img  class='icon30 tapped' 
                            src='../img/delete-circle-128.png'  
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                        Leave $deletetext
                    </td>
                </tr>
                </table>
            ";
            
        }
        
        $menucolor = "c3c3c3";
        
        $buttonrefresh = "
                <table class='gridnoborder smalltext refreshchatsession tapped' 
                    title='Refresh'
                    data-chatid='$chatid'                    
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped' 
                            src='../img/refresh-circle-128.png' 
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td style='padding-right:10px;padding-left:10px;vertical-align:center'>
                        $menu_refresh
                    </td>
                </tr>
                </table>
                    ";
        $buttonaddparty = "
                <table class='gridnoborder smalltext addchatbutton mainbutton tapped' 
                    title='Add Party to Chat'
                    data-chatid='$chatid' data-providerid=$providerid
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped' 
                            src='../img/add-circle-128.png' 
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                        $menu_addparty
                    </td>
                </tr>
                </table>
                    ";
        $buttonsharephoto = "
                <table class='gridnoborder smalltext photoselect tapped' id='photoselect' 
                    title='Share Photo from My Photos'
                    data-target='#chatmessage3' 
                    data-src=''
                    data-album=''
                    data-mode='X'
                    data-filename=''
                    data-caller='chat'
                    data-passkey64='$passkey64'                             
                    id='photoselect'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 inputfocus'
                                src='../img/brax-photo-round-black-128.png' 
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                             $menu_sharephoto
                    </td>
                </tr>
                </table>
            

                    ";
        $buttonsharefile = "
                <table class='gridnoborder smalltext doclib tapped' 
                        data-roomfolderid='0' data-sort=''
                        data-target='#chatmessage' data-link='' data-folderid='0'
                        data-folder='' data-mode='' data-page=1 data-caller='chat'  
                        data-passkey64='$passkey64'                             
                        id='fileselect'
                        title='Share File from My Files'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped' 
                            src='../img/brax-doc-round-black-128.png' 
                            style='cursor:pointer;position:relative;top:0px' />
                            
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                        $menu_sharefile
                    </td>
                </tr>
                </table>

                    ";
        $buttonupload = "
                <table class='gridnoborder smalltext uploadphoto2 tapped' 
                    data-target='#chatmessage3' 
                    data-chatid='$chatid'
                    data-src=''
                    data-album=''
                    data-mode='X'
                    data-filename=''
                    data-caller='chat'                                     
                    id='photoselect'
                    title='Upload Photo to Chat'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 inputfocus' id='photoselect' 
                                src='../img/upload-circle-128.png' 
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                            $menu_uploadphoto
                    </td>
                </tr>
                </table>
                    ";
        $buttonmembers = "
                <table class='gridnoborder smalltext togglememberson tapped' 
                    title='Members'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped'  
                            src='../img/people-circle-128.png'
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                            $menu_members
                    </td>
                </tr>
                </table>
           

                    ";
        $buttonpin = "
                <table class='gridnoborder smalltext chatpin selectchatlist tapped' 
                    title='Pin' data-chatid='$chatid' data-mode='CHAT'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped'  
                            src='../img/pin-black-512.png'
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                            Pin/Unpin
                    </td>
                </tr>
                </table>
           

                    ";
        $buttonsave = "
                <table class='gridnoborder smalltext chatpin selectchatlist tapped' 
                    title='Pin' data-chatid='$chatid' data-mode='SAVED'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped'  
                            src='../img/tasks-circle-128.png'
                            style='cursor:pointer;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                            Save
                    </td>
                </tr>
                </table>
           

                    ";
        
        
        //Removing these functions during a broadcast to save space for smaller phone
        $buttontakephoto = "";
        $buttontoggle = "";
        if($broadcastowner == ''){
            
            /*
            $buttontakephoto = "
                <table class='gridnoborder smalltext camera tapped' 
                    title='Take Photo for Chat'
                   data-caller='chat'
                   data-otherid='$otheremail' data-chatid='$chatid'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped' 
                            src='../img/Camera-Circle-Gray-128.png'
                            style='cursor:pointer;$notactive_e2e;;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-left:10px;vertical-align:center'>
                        $menu_takephoto
                    </td>
                </tr>
                </table>
                

                    ";
             * 
             */
            $buttontoggle = "
                <table class='gridnoborder smalltext togglechat tapped' 
                    title='$togglemsg'
                     data-chatid='$chatid'                                 
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped' 
                            src='../img/Arrow-Right-in-Circle_120px.png'
                            style='cursor:pointer;;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                            $togglemsg
                    </td>
                </tr>
                </table>
                    ";
        } else {
            $buttonpin = "";
        }
        $buttonstream = "";
        if(($radiostation=='Y') 
                && !$streaming  && $_SESSION['broadcaster']!='N' ){
                
            $buttonstream = "
                <table class='gridnoborder smalltext notifyaudiostream tapped' 
                    title='Broadcast'
                    data-action='VIDEO'
                    data-mode='BROADCASTER'
                    data-chatid='$chatid'
                    style='vertical-align:top;color:black;padding-top:10px;height:40px'>
                <tr>
                    <td>
                        <img class='icon30 tapped' 
                                src='../img/Arrow-Right-in-Circle_120px.png'
                            style='cursor:pointer;;position:relative;top:0px' />
                    </td>
                    <td class='mainfont' style='padding-right:10px;padding-left:10px;vertical-align:center'>
                        Stream Video 
                    </td>
                </tr>
                </table>

                ";
        }
        
        $chat  .= "
        <div class=''
            style='display:none;padding:0;margin:auto;
            border:0;font-size:12px;background-color:white;border-color:gray'>
            <span class=''>
                <span class='chatphoto' style='display:none;color:black'>Image Link</span>
                <input id='chatmessage3' class='chatphoto inputfocus chatwindow2 mainfont' 
                    type='url' name='chatmessage3' style='display:none;height:15px' size='50' />
            </span>
        </div>
            ";

        $chat  .= "
            
            <div class='inputfocuscontent' 
                style='margin-top:10px;padding-left:0px;padding-top:0px;
                padding-bottom:0px;margin:0;font-size:12px;
                vertical-align:top'>
                <div class='chatextraarea gridstdborder shadow' style='background-color:whitesmoke;border-color:gray;paddding-left:5px;padding-top:20px;display:none;text-align:left;color:black'>     
                    <table style='width:100%'>
                        <tr>
                            <td style='padding-left:10px'>
                        $buttonaddparty
                        $buttonmembers 
                        $buttonstream 
                        $buttondelete
                        $buttontoggle
                            </td>
                            <td style='padding-right:10px'>
                        $buttonsharephoto 
                        $buttonsharefile
                        $buttonupload 
                        $buttonpin
                        $buttonsave
                            </td>
                        </tr>
                    </table>
                        
                ";
        
        
        $chat .= "
                    $broadcastownermobile
                    <br>
                    <br>
                </div>
            </div>
                ";
        
        return $chat;
}

function DesktopMenu( $providerid, $chatid, $passkey64, $keyhash, $archive, 
        $streaming, $radiostation, 
        $broadcastowner, $allowdelete, $otheremail,  $notactive_e2e, $archivemode,
        $avatarblocklong, $otherparty2, $displaytwopartyonly )
{
        global $global_menu2_color;
        global $global_background;
        global $menu_sharefile;
        global $menu_sharephoto;
        global $menu_uploadfile;
        global $menu_uploadphoto;
        global $menu_addparty;
        global $menu_station;
        global $menu_chat;
        global $menu_leave;
        global $menu_refresh;
        global $menu_pin;
        
        $chat = "<div style='verticsl-align:top'>";
    
        $deletechat = "";
        if($allowdelete){
            
            $deletechat = "
                <div class='smalltext2 chatbutton tapped' >
                    <div class='endchatbutton tapped' data-archive='$archive' data-chatid='$chatid' title='Delete Chat' id='endchatbutton' name='endchatbutton' style='display:inline'>
                          <img class='icon25' src='../img/Close_120px.png' style='top:0px' />
                    </div>
                    <br>$archivemode
                    <br><br>
                </div>
            ";
        } else {
            
            if($radiostation=='Y'){
                
                $deletemode = 'R';
                $deletetext = $menu_station;
                
            } else {
                
                $deletemode = 'L';
                $deletetext = $menu_chat;
            }
            
            $deletechat = "
                <div class='smalltext2 chatbutton tapped' >
                    <div class='chatdeleteparty tapped' 
                        Title='Leave $deletetext'
                        data-chatid='$chatid' data-mode='$deletemode'
                        data-providerid='$providerid' 
                        style='display:inline'>
                          <img class='icon25' src='../img/Close_120px.png' style='top:0px' />
                    </div>
                    <br>$menu_leave
                    <br>$deletetext<br>
                </div>
                ";
            
        }
        $chat  .= "
            <div class='gridstdborder chatextraarea' style='vertical-align:top;position:relative;top:0px;display:none;background-color:$global_menu2_color;color:white'>
                <img class='chatextrahide2' src='../img/arrowhead-down-white-128.png' style='height:15px;float:right;margin-right:10px;cursor:pointer' />
                <span class='smalltext'>
                    &nbsp;&nbsp;$otherparty2
                </span><br>
                $avatarblocklong
                <br><br>
                $broadcastowner
                <br>
                
             </div>
            
            <div class='' style='background-color:whitesmoke;color:black;position:relative;top:0px;display:block;width:100%;height:auto;padding-left:10px;padding-right:10px;padding-bottom:0;margin:0;border:0;font-size:12px;vertical-align:top'>
                <span class='nonmobile' style='display:none'>
                    <span class='chatphoto' style='display:none;color:black'>Image Link<br></span>
                    <input id='chatmessage3' class='chatphoto inputfocus chatwidth2 mainfont' type='url' name='chatmessage3' style='display:none;height:15px' size='50' />
                </span>
                ";
        $chat .= "
                <div class='chatextraarea' style='display:none'>
                    <br>
                    <div class='smalltext2 chatbutton tapped' >
                        <div class='addchatbutton mainbutton tapped'  name='selectchatbutton' 
                            title='Add Chat Party'
                            data-chatid='$chatid' data-providerid=$providerid
                            style='display:inline'>
                              <img class='icon25' src='../img/Add-User_120px.png' style='top:0px' />
                        </div>
                        <br>$menu_addparty<br>
                    </div>
                    $deletechat

                    <div class='smalltext2 chatbutton tapped' >
                        <img class='photoselect inputfocus icon25' id='photoselect' src='../img/brax-photo-round-black-128.png' 
                             style='top:0px' title='Share Photo' alt='Webpage Link' 
                             data-target='#chatmessage3' 
                             data-src=''
                             data-album=''
                             data-mode='X'
                             data-filename=''
                             data-passkey64='$passkey64'                             
                             data-caller='chat'                                     
                             id='photoselect'
                             />
                             <br>
                             $menu_sharephoto
                             <br>
                    </div>

                    <div class='smalltext2 chatbutton tapped' >
                        <img id='fileselect' class='doclib icon25'  src='../img/brax-doc-round-black-128.png' 
                             style='top:0px' 
                             title='Share File' alt='Webpage Link' 
                            data-roomfolderid='0' data-sort=''
                            data-target='#chatmessage' data-link='' data-folderid='0'
                            data-folder='' data-mode='' data-page=1 data-caller='chat'  
                            data-passkey64='$passkey64'                             
                             />
                             <br>
                          $menu_sharefile
                              <br>
                    </div>
                    <div class='smalltext2 chatbutton tapped' style='$notactive_e2e'>
                            <img class='uploadphotofromselect icon25' src='../img/upload-circle-128.png'
                                style='top:0px' 
                                title='Upload Photo'
                            id='uploadfiletoother' 
                            data-caller='chat'
                            data-otherid='$otheremail' data-chatid='$chatid'
                            />
                            <br>
                            $menu_uploadphoto
                    </div>
                    <div class='smalltext2 chatbutton tapped' style='$displaytwopartyonly;' >
                            <img class='uploadfilefromchat icon25' src='../img/upload-circle-128.png'
                                style='top:0px' 
                            title='Upload File'
                            id='uploadfiletoother' 
                            data-caller='chat'
                            data-otherid='' data-chatid='$chatid'
                            data-passkey64='$passkey64'                             
                            />
                            <br>
                            $menu_uploadfile
                    </div>
                    <div class='smalltext2 chatbutton tapped' style='$displaytwopartyonly;' >
                            <img class='chatemailnotify icon25' src='../img/mail-circle-128.png'
                                style='top:0px' 
                                title='Email Notify'
                            data-otherid='$otheremail' data-chatid='$chatid'
                            />
                        <br>Email
                        <br>Notify
                    </div>
                    ";
        if($_SESSION['enterprise']=='Y' || $_SESSION['superadmin']=='Y'){
            /*
        $chat .= "
                    <div class='smalltext2 chatbutton tapped' style='' >
                            <img class='chatformrequest icon25' src='../img/credentials-128.png'
                                style='top:0px' 
                                title='Form Request'
                            data-passkey64='$passkey64'  
                            data-keyhash='$keyhash'    
                            data-otherid='' 
                            data-chatid='$chatid'
                            />
                        <br>Form
                        <br>Request
                    </div>
                    ";
             * 
             */
        }
        $chat .= "
                    
                    <div class='smalltext2 chatbutton tapped' style='' >
                            <img class='refreshchatsession icon25' src='../img/Refresh_120px.png'
                                style='top:0px' 
                                title='Refresh'
                            data-otherid='$otheremail' data-chatid='$chatid' data-keyhash='$keyhash'
                            />
                        <br>$menu_refresh
                        <br>
                        <br>
                    </div>
                ";
        if(( $radiostation=='Y'  ) && !$streaming ){
            
                $chat .= "

                    <div class='smalltext2 chatbutton tapped' style='' >
                            <img class='notifyaudiostream icon25' src='../img/Video-Tripod_120px.png'
                                style='top:0px' 
                                title='Broadcast Video'
                            data-mode='BROADCASTER'
                            data-action='VIDEO'
                            data-chatid='$chatid' 
                            />
                        <br>Stream
                        <br>Video
                        <br>
                    </div>
                    ";
        }
        $chat .= "

            <div class='smalltext2 chatbutton selectchatlist tapped' style='' >
                    <img class='chatpin icon25' src='../img/pin-512.png'
                        style='top:0px'  data-mode='CHAT'
                        title='Pin/Unpin Chat'
                    data-chatid='$chatid' 
                    />
                <br>Pin/
                <br>Unpin
                <br>
            </div>
            ";

                $chat .= "

            <div class='smalltext2 chatbutton selectchatlist tapped' style='' data-mode='' >
                    <img class='chatpin icon25 ' src='../img/tasks-circle-128.png'
                        style='top:0px'  data-mode='SAVED'
                        title='Save Chat'
                    data-chatid='$chatid' 
                    />
                <br>Save
                <br>
                <br>
            </div>
            ";

                
        
        $chat .= "
                <br>
                <br>
                </div>
            </div>
            ";
        $chat .= "</div>";
        
        return $chat;
}
function AutoEndBroadcast($chatid)
{

    //Look for Un-Ended Audio Broadcast
    $result = pdo_query("1",
        "select broadcastid from broadcastlog  
         where broadcastdate2 is null and 
         chatid = ? order by broadcastid desc limit 1
        ",array($chatid)
        );
    if($row = pdo_fetch($result)){
        
        pdo_query("1",
            "
            update broadcastlog
            set broadcastdate2 = now(),
            elapsed = time_to_sec(timediff( now(), broadcastdate ))
            where broadcastid = $row[broadcastid]
            and mode = 'B' and broadcastdate2 is null
            ",null);
            
            
    }
    
    pdo_query("1","
        update chatmaster set broadcaster = null, broadcastmode = null, live='N', radiotitle='' where chatid = ? 
    ",array($chatid));
    
}