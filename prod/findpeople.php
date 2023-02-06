<?php
session_start();
require("nohost.php");
require_once("config-pdo.php");
require_once("internationalization.php");

if(ServerTimeOutCheck()){
    
    $arr = array('list'=> "T", 'mode'=> "");
    echo json_encode($arr);
    exit();
    
}
require("validsession.inc.php");


    $providerid = @tvalidator("ID",$_POST['providerid']);
    $mode = @tvalidator("PURIFY",$_POST['mode']);
    $find = rtrim(@tvalidator("PURIFY",$_POST['find']));
    
    if($mode == 'LIVE'){      
        $mode = "P1";
    }
    
    SaveLastFunction($_SESSION['pid'],"W", 0);

    $result = pdo_query("1","select enterpriselist, partitioned from sponsor where sponsor='$_SESSION[sponsor]' ",null);
    $enterprise = $_SESSION['enterprise'];
    $partitioned = "";
    if($row = pdo_fetch($result)){
        //$enterprise = $row['roomdiscovery'];
        $partitioned = $row['partitioned'];
    }

    /*****************************
     * 
     * 
     *    MAIN
     * 
     */
    if($_SESSION['sponsor'] != '' && $partitioned=='Y'
        && $mode == ''){
        $mode = 'P6';
    }
    if($_SESSION['sponsor'] != '' && $enterprise=='N' && $mode == '' && $partitioned == 'Y'
        ){
        $mode = 'P5';
    }
    if($mode == '' ){
        $mode = 'P1';
    }

        
    $list = "
        <div class='gridnoborder suspendchatrefresh' style='padding:0px;margin:0;background-color:transparent'>
            ";


    $list .= Title();
    $list .= "
            <div class='gridnoborder' style='padding:0px;;text-align:left;background-color:$global_background2'>
            ";
    $list .= Buttons($mode);
    $list .="   
            </div>
            <div style='padding-left:30px;padding-right:30px;padding-top:0px;padding-bottom:50px;margin:0px;text-align:left;background-color:transparent'>
            <br>
        ";
    
    $list .= PublicList($find, $mode);    
    $list .= EnterpriseList($find, $mode);    
    $list .= ContactList($find, $mode);    
    $list .= ActivityList( $mode);    
    $list .= FriendList( $mode);    
    $list .= FollowersList( $mode);    
    $list .= NewList( $mode);    
    $list .= BannedList( $mode);    
    $list .= BlockedList( $mode);    

    $list .="   
            </div>
        </div>";

    /* $mode is configured/reset in Buttons() */

    $arr = array('list'=> "$list", 'mode'=> "$mode");
    echo json_encode($arr);
    exit();


    
    
    
    

    function Title()
    {
        global $appname;
        global $global_titlebar_color;
        global $icon_braxpeople2;
        global $menu_people;
        
        $backgroundcolor = $global_titlebar_color;
        $list = "
                <div class='gridnoborder' style='background-color:$global_titlebar_color;color:white;padding-left:20px;padding-right:20px;padding-bottom:3px;margin:0;' >
                    <span style='opacity:.5'>
                    $icon_braxpeople2
                    </span>
                    <span class='pagetitle2a' style='color:white'><b>$menu_people</b></span> 
                </div>
            ";
        $list = "";
        return $list;
        
    }
    function Buttons($mode)
    {
        $roomdiscovery = "Y";
        $result = pdo_query("1","
                   select roomdiscovery from provider where providerid = $_SESSION[pid]
                       ",null);
        if($row = pdo_fetch($result)){
            $roomdiscovery = $row['roomdiscovery'];
        }

        $community = "";
        $enterprise = "";
        $partitioned = "";
        $result = pdo_query("1","select enterpriselist, communitylist, partitioned
                   from sponsor where sponsor='$_SESSION[sponsor]' ",null);
        if($row = pdo_fetch($result)){
            $enterprise = $row['enterpriselist'];
            $community = $row['communitylist'];
            $partitioned = $row['partitioned'];
        }
        
        $sponsor = ucfirst($_SESSION['sponsorname']);
        global $global_menu_color;
        global $global_activetextcolor;
        global $global_textcolor;
        global $iconsource_braxchat_common;
        global $global_background;
        global $menu_public;
        global $menu_community;
        global $menu_top;
        global $menu_chats;
        global $menu_friends;
        global $menu_followers;
        global $menu_following;
        
        if($partitioned=='Y'){
            $sponsor = 'Staff';
        }
        
        /*
        $button_chat = "
                    <div class='selectchatlist pagetitle2a' data-mode='CHAT' style='display:inline-block;width:150px;cursor:pointer;padding:5px;padding-bottom:20px;color:$global_activetextcolor' title='Back to Chats List'>
                        $menu_chats
                    </div>
                    ";
        */
        
        $button_public = "
                    <div class='meetuplist pagetitle2a' data-mode='P1' style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        $menu_public
                    </div>
                    ";
        $button_community = "
                    <div class='meetuplist pagetitle2a' data-mode='P5' style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        $menu_community 
                    </div>
                    ";
        $button_sponsor = "
                    <div class='meetuplist pagetitle2a' data-mode='P6'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        $sponsor 
                    </div>
                    ";
        $button_activity = "";
        /*
        $button_activity = "
                    <div class='meetuplist pagetitle2a' data-mode='P2'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        $menu_top
                    </div>
                    ";
         * 
         */
        $button_friends = "
                    <div class='meetuplist pagetitle2a' data-mode='P7'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        $menu_friends
                    </div>
                    ";
        $button_following = "
                    <div class='meetuplist pagetitle2a' data-mode='P8'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        $menu_following
                    </div>
                    ";
        $button_new = "
                    <div class='meetuplist pagetitle2a' data-mode='P10'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        New
                    </div>
                    ";
        $button_banned = "
                    <div class='meetuplist pagetitle2a' data-mode='P9'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        Trolls
                    </div>
                    ";
        $button_blocked = "
                    <div class='meetuplist pagetitle2a' data-mode='P11'  style='display:inline-block;width:150px;cursor:pointer;padding-left:20px;padding-top:10px;padding-bottom:10px;color:$global_textcolor'>
                        Blocked
                    </div>
                    ";
        
        
        /****************/
        $button_chat2 = "
                    <div class='selectchatlist pagetitle3' data-mode='CHAT' style='float:left;margin-right:20px;margin-left:20px;cursor:pointer;padding:3px;color:$global_textcolor' title='Back to Chats List'>
                        <img class='icon15' src='$iconsource_braxchat_common' style='top:3px' />
                        $menu_chats
                    </div>
                    ";
        
        
        $button_public2 = "
                    <div class='meetuplist pagetitle3' data-mode='P1' style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-left:30px;margin-right:20px' title='List people from the public list'>
                        $menu_public
                    </div>
                    ";
        $button_community2 = "
                    <div class='meetuplist pagetitle3' data-mode='P5' style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people from your community'>
                        $menu_community 
                    </div>
                    ";
        $button_sponsor2 = "
                    <div class='meetuplist pagetitle3' data-mode='P6'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px'  title='List people from the enterprise'>
                        $sponsor 
                    </div>
                    ";
        $button_activity = "";
        $button_activity2 = "";
        
        /*
        $button_activity2 = "
                    <div class='meetuplist pagetitle3' data-mode='P2'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people with recent activity from your community'>
                        $menu_top
                    </div>
                    ";
         * 
         */
        $button_friends2 = "
                    <div class='meetuplist pagetitle3' data-mode='P7'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people with recent activity from your community'>
                        $menu_friends
                    </div>
                    ";
        $button_following2 = "
                    <div class='meetuplist pagetitle3' data-mode='P8'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people with recent activity from your community'>
                        $menu_following
                    </div>
                    ";
        $button_new2 = "
                    <div class='meetuplist pagetitle3' data-mode='P10'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people with recent activity from your community'>
                        New
                    </div>
                    ";
        $button_banned2 = "
                    <div class='meetuplist pagetitle3' data-mode='P9'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people with recent activity from your community'>
                        Trolls
                    </div>
                    ";
        $button_blocked2 = "
                    <div class='meetuplist pagetitle3' data-mode='P11'  style='cursor:pointer;padding:10px;color:$global_activetextcolor;float:left;margin-right:20px' title='List people with recent activity from your community'>
                        Blocked
                    </div>
                    ";
        
        
        if($_SESSION['superadmin']!='Y'){
            $button_banned = '';
            $button_banned2 = '';
            $button_new = '';
            $button_new2 = '';
            $button_blocked = '';
            $button_blocked2 = '';
            //$button_activity = "";
            //$button_activity2 = "";
        }
        
        /******
         * 
         * 
         *    MOBILE
         * 
         */
        $list = "
            <div class='pagetitle2a formobile'
                style='padding-left:10px;width:100%;
                padding-right:10px;padding-top:10px;padding-bottom:0px;background-color:transparent'>
                ";
        
        if( $roomdiscovery == 'N'){
            $button_activity = "";
            $button_activity2 = "";
        }
        if($roomdiscovery == 'N'){
            $button_public = "";
            $button_public2 = "";
        }
        if($_SESSION['enterprise']!='Y' && $community == 'N'){
            $button_community = "";
            $button_community2 = "";
        }
        
        if($_SESSION['sponsor']!='' && $partitioned == 'N'){
            $list .= "
                    $button_public
                    $button_community
                    $button_following
                    $button_friends
                    $button_activity
                    $button_new
                    $button_banned
                    $button_blocked
                        
                ";
        } else 
        if($enterprise == 'Y' && ($partitioned == 'Y' || $roomdiscovery == 'N')){
            $list .= "
                    $button_sponsor
                    $button_community
                    $button_public
                    $button_following
                    $button_friends
                    $button_new
                    $button_banned
                    $button_blocked
                        
                ";
        } else 
        if($enterprise == 'N'  && ($partitioned == 'Y' || $roomdiscovery == 'N') ){
            $list .= "
                    $button_sponsor
                    $button_community
                    $button_public
                    $button_following
                    $button_friends
                    $button_new
                    $button_banned
                    $button_blocked
                ";
        } else {
            
            $list .= "
                    $button_public
                    $button_community
                    $button_following
                    $button_friends
                    $button_activity
                    $button_new
                    $button_banned
                    $button_blocked
                ";
        }
        
        /******
         * 
         * 
         *    NON MOBILE
         * 
         */
        
        $list .= "
            </div>
            <div class='pagetitle3 nonmobile'
                style='padding-left:20px;width:100%;
                padding-right:10px;padding-top:10px;padding-bottom:0px;'>
                ";
        
        if($_SESSION['sponsor']!=''  && $roomdiscovery == 'Y'){
            $list .= "
                    $button_public2
                    $button_sponsor2
                    $button_community2
                    $button_following2
                    $button_friends2
                    $button_activity2
                    $button_new2
                    $button_banned2
                    $button_blocked2
                ";
        } else 
        if($enterprise == 'Y'  && ($partitioned == 'Y' || $roomdiscovery == 'N') ){
            $list .= "
                    $button_public2
                    $button_sponsor2
                    $button_community2
                    $button_following2
                    $button_friends2
                    $button_new2
                    $button_banned2
                    $button_blocked2
                ";
        } else 
        if($enterprise == 'N'  && ($partitioned == 'Y' || $roomdiscovery == 'N') ){
            $list .= "
                    $button_sponsor2
                    $button_community2
                    $button_public2
                    $button_following2
                    $button_friends2
                    $button_new2
                    $button_banned2
                    $button_blocked2
                ";
        } else {
            $list .= "
                    $button_public2
                    $button_community2
                    $button_following2
                    $button_friends2
                    $button_activity2
                    $button_new2
                    $button_banned2
                    $button_blocked2
                ";
        } 
        $list .= "
            <br><br>
            </div>
            ";
        return $list;
        
    }
    

    
    function PublicList($find, $mode)
    {
        global $appname;
        global $rootserver;
        global $global_textcolor;
        global $global_textcolor_reverse;
        global $global_background;
        global $global_bottombar_color;
        global $global_activetextcolor_reverse;
        global $iconsource_braxarrowright_common;
        global $iconsource_braxfind_common;
        global $iconsource_braxmedal_common;
        global $menu_public;
        global $menu_handle;
        
        
        $providerid = $_SESSION['pid'];
        
        $peoplelimit = 200;
        $result = pdo_query("1",
                "select peoplelimit from provider where providerid = ?",array($_SESSION['pid']));
        if($row = pdo_fetch($result)){
            $peoplelimit = $row['peoplelimit'];
            if($peoplelimit < 100){
                $peoplelimit = 100;
            }
            if($peoplelimit > 5000){
                $peoplelimit = 5000;
            }
        }
        
        
        $list = "
            <span class='meetuppublicshow' style='display:none;background-color:transparent;color:$global_textcolor'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    $menu_public
                    <br>
                    <!--
                    <img id='findpeoplebyname' class='icon30 showhidden' src='$iconsource_braxfind_common' title='Find People' />
                    -->        
                    <input class='showhiddenarea inputline dataentry mainfont meetuppublicfind' id='meetuppublicfind' name='meetuppublicfind' type='text' size=20 value=''              
                        placeholder='$menu_handle'
                        style='max-width:200px;background-color:transparent;padding-left:5px;;color:$global_textcolor'/>
                        <img id='meetuplistbutton1' class='showhiddenarea icon20 meetuplist' data-mode='P1' src='$iconsource_braxfind_common' title='Start Search'
                        style='top:8px' >
                </div>
                <br>
            </span>
            ";
        if($mode == 'P6'){
            $list = "";
        }
        if($mode!='P1' && $mode!=''){
            return $list;
        }

        if($_SESSION['mobilesize']=='Y'){
            $peoplelimit = "100";
        }
        $activequery = " order by providername limit $peoplelimit";        
        $listheader = "";
        if($find == '@%'){
            $activequery = " order by provider.createdate desc limit 50 ";
        } else
        if($find == ''){
            $activequery = " order by provider.lastactive desc, provider.createdate desc limit $peoplelimit ";
            $listheader = "
                <div class='pagetitle3' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                <br>
                </div>
                ";
        }
        
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, publishprofile, replyemail, provider.profileroomid,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, provider.score,
            (select 'Y' from followers where followers.providerid = provider.providerid and followers.followerid = $providerid ) as followed
            from provider
            where  (active='Y' or 'Y' = '$_SESSION[superadmin]') and termsofuse is not null and 
            (
                (
                    (providername like ? or handle like ? or publishprofile like?)
                    and 
                    (   
                        provider.publish='Y' 
                        or 
                        provider.providerid  in (select targetproviderid from contacts where providerid = ? )
                        or
                        provider.providerid in (select friendid from friends where providerid = ? )
                        or
                        provider.providerid in (select followerid from followers where providerid = ? )
                        or
                        provider.providerid in (select providerid from followers where followerid = ? )
                        or 
                        ( provider.sponsor = '$_SESSION[sponsor]' and '$_SESSION[sponsor]'!='' )               
                    )
                ) or (
                    handle = ? or handle = ?
                )
            )
            $activequery
            
            ",array(
                "%".$find."%","%".$find."%","%".$find."%",$providerid,$providerid,$providerid,$providerid,$find,"@".$find
               )
        );
            
        $count = 0;
        if($_SESSION['roomdiscovery']=='N' && $find == ''  ){
            $list .= "<div class='meetupcontactlistarea pagetitle2a' style='color:$global_textcolor;padding:20px;max-width:200px;margin:auto'>
                        <div class='circular3 gridnoborder' style=';overflow:hidden;margin:auto'>
                            <img class='' src='../img/agent.jpg' style='width:100%;height:auto' />
                        </div>
                        <div class='tipbubble' style='color:$global_textcolor_reverse;background-color:$global_bottombar_color;padding:30px'>
                            You are in a private space. The public list is not displayed.                        
                        </div>
                    </div>";
            return $list;
        } 
        $joined = "";
        while($row = pdo_fetch($result)){
            
            if($count == 0){
                $list .= $listheader;
                $listheader = "";

            }
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            }
            if($find=='@%'){
                $joined = "<br>".$row['joined'];
            }

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
             
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }
            $following = '';
            if($row['followed']=='Y'){
                $following = "<div class='smalltext' style='border-radius:5px;text-align:center;background-color:$global_textcolor;color:$global_background;margin-top:5px'>Following</div>";
            }

            $list .= "
                <div class='meetuppublicshow rounded stdlistbox $shadow' 
                    style=';display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;color:$global_textcolor;
                    margin-bottom:10px;margin-right:5px;
                    word-wrap:break-word;overflow:hidden;'>
                            <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;width:100%;height:60%;overflow:hidden'
                             data-providerid='$row[providerid]' data-name='$row[providername]'    
                             data-roomid='$row[profileroomid]'
                             data-profile='Y'
                             data-caller='find'
                             data-mode ='S' data-title='' data-passkey64='' 
                             >
                                <div class='gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;height:90%'  title='User Photo'>
                                    <img class='' src='$avatar' style='height:auto;width:100%;'/>
                                </div>
                            </div>
                                <div class='smalltext' style='padding-left:10px;padding-right:10px'>
                               $row[providername]<br><span class='smalltext' style='color:$global_textcolor'>$id $joined
                                <br> $following </span>
                                </div>
                </div>
                ";
        }    
        
        if($count == 0){
            $list .= "<div class='meetuppublicshow' style='padding:20px;color:$global_textcolor'>Not found in Public List</div>";
        } else {
            if($find=='' && $_SESSION['mobilesize']!=='Y'  ){
                $list .= "
                    <br><br>
                    <div class=smalltext style='padding-left:20px;color='$global_textcolor'>
                    Items Display LImit: <input id=peopledisplaylimit class='peopledisplaylimit dataentry' type=numeric value=$peoplelimit style='width:50px' />&nbsp;&nbsp; 
                     <img class='icon20 setpeopledisplaylimit'  src='$iconsource_braxarrowright_common' />
                    </div>
                    ";
                
            }
            if($find=='' && $_SESSION['mobilesize']=='Y'  ){
                $list .= "<div class='meetuppublicshow' style='padding:20px;color:$global_textcolor'>This list is huge so we just show you a sample on mobile. Search by name to limit the results.</div>";
                
            }
        }


        
        return $list;
    }    
    function ContactList($find, $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_textcolor_reverse;
        global $global_activetextcolor_reverse;
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxarrowright_common;
        global $iconsource_braxfind_common;
        global $iconsource_braxmedal_common;
        global $menu_community;
        global $menu_handle;
        
        $peoplelimit = 200;
        $result = pdo_query("1",
                "select peoplelimit from provider where providerid = ?",array($_SESSION['pid']));
        if($row = pdo_fetch($result)){
            $peoplelimit = $row['peoplelimit'];
            if($peoplelimit < 100){
                $peoplelimit = 100;
            }
            if($peoplelimit > 5000){
                $peoplelimit = 5000;
            }
        }
        
        
        $list = "
            <span class='meetupcontactlistarea' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    $menu_community
                    <br>
                                
                    <img class='icon20 showhidden' src='$iconsource_braxfind_common' title='Find Room' />
                    <input class='showhiddenarea inputline dataentry mainfont meetupcontactlistfind' id='meetupcontactlistfind' name='meetuppublicfind' type='text' size=20 value=''              
                        placeholder='$menu_handle'
                        style='display:none;max-width:200px;background-color:transparent;padding-left:5px;;color:$global_textcolor'/>
                        <img class='showhiddenarea icon20 meetuplist' data-mode='P5' src='$iconsource_braxarrowright_common' title='Start Search'
                        style='display:none;top:8px' >

                </div>
            </span>
            ";
        if($mode!='P5'){
            return $list;
        }
        
        
        $listheader = "";
        if($find == ''){
            $order = " order by provider.createdate desc limit $peoplelimit  ";
            $listheader = "
                <div class='pagetitle3' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                <br>
                Newly Updated Profiles - <span style='color:gray'>More Available - Use Search</span>
                </div>
                ";
            
        } else {
            $order = " order by provider.providername limit 50";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            DATE_FORMAT(provider.createdate, '%m/%d/%y') as joined, blocked1.blockee, provider.profileroomid, provider.score,
            (select 'Y' from followers where followers.providerid = provider.providerid and followers.followerid = $_SESSION[pid] ) as followed
            from provider
            left join blocked blocked1 on blocked1.blockee = provider.providerid and blocked1.blocker =?
            where  provider.active='Y' and termsofuse is not null
            and  
            (
                provider.providerid  in (select targetproviderid from contacts where providerid = ? )
                or
                provider.providerid in (select friendid from friends where providerid = ? )
                or
                provider.providerid in (select followerid from followers where providerid = ? )
                or
                provider.providerid in (select providerid from followers where followerid = ? )
                or 
                ( provider.sponsor = '$_SESSION[sponsor]' and '$_SESSION[sponsor]'!='')
                or
                ( provider.publish = 'Y' and
                ? != '' and ( provider.handle = ? or provider.handle = ?)
                )
            )
                
            and
            (provider.providername like ? or provider.handle like ?)
            and provider.providername!=''
            $order
                ",array(
                    $providerid,$providerid, $providerid, $providerid, $providerid, $find, $find,"@".$find,
                    "%".$find."%","%".$find."%"
                ));

                /*
                or 
                provider.providerid in (select providerid from groupmembers where groupid in 
                        (
                        select groupid from groupmembers where providerid = $providerid
                        )
                    )
                 */
        
        $count = 0;
        while($row = pdo_fetch($result)){
            
            if($count == 0){
                
                $list .= $listheader;
                $listheader = "";
            }
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            
            $blocked = "";
            if($row['blockee']!=''){
                $blocked = "<div class='smalltext'  style='color:firebrick;cursor:pointer'>Blocked</div>";
            }

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
            $following = '';
            if($row['followed']=='Y'){
                $following = "<div class='smalltext' style='border-radius:5px;text-align:center;background-color:$global_textcolor;color:$global_background;margin-top:5px'>Following</div>";
            }
            
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }
            $joined = InternationalizeDate($row['joined']);

            $list .= "
                <div class='meetupcontactlistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:white;margin-bottom:10px;
                    text-align:left;word-wrap:break-word;background-color:$global_background;
                    overflow:hidden;'>
                    <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;padding:15px;height:80%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'    
                     data-roomid='$row[profileroomid]'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:90%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            $row[providername]<span class='smalltext' style='color:$global_textcolor'>
                             <br>$joined<br>$id<br>
                             $blocked
                            $following</span>
                    </div>
                </div>
                ";
        }    
        if($count == 0 && $find == ''){
            $list .= "<div class='meetupcontactlistarea pagetitle2a' style='color:$global_textcolor;padding:20px;max-width:300px;margin:auto'>
                        <div class='circular3 gridnoborder' style=';overflow:hidden;margin:auto'>
                            <img class='' src='../img/agent.jpg' style='width:100%;height:auto' />
                        </div>
                        <div class='tipbubble pagetitle3' style='background-color:$global_bottombar_color;padding:30px;color:$global_textcolor_reverse'>
                    You have no contacts at the moment. As you connect with others, you will get added to communities and this list will build. Search for people you know in the Public list.</div>
                        </div>";
        }


        
        return $list;
    }        
    
    function EnterpriseList($find, $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxarrowright_common;
        global $iconsource_braxfind_common;
        global $iconsource_braxmedal_common;
        global $menu_handle;
        
        $sponsor = strtoupper($_SESSION['sponsorname']);
        //$sponsor = ucfirst($_SESSION['sponsorname']);
        $sponsorname = "STAFF";
        
        $list = "
            <span class='meetupenterpriselistarea' style=';color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    $sponsorname CONTACT LIST
                    <br>
                    
                    <img class='icon20 showhidden' src='$iconsource_braxfind_common' title='Find Room' />
                    <input class='showhiddenarea inputline dataentry mainfont meetupenterpriselistfind' id='meetupenterpriselistfind' name='meetuppublicfind' type='text' size=20 value=''              
                        placeholder='$menu_handle'
                        style='display:none;max-width:200px;background-color:transparent;padding-left:5px;;color:$global_textcolor'/>
                        <img class='showhiddenarea icon20 meetuplist' data-mode='P6' src='$iconsource_braxarrowright_common' title='Start Search'
                        style='display:none;top:8px' >
                    
                                
                </div>
            </span>
            ";
        if($mode!='P6'){
            return "";
        }
        
        
        if($find == ''){
            $order = " order by provider.providername asc limit 500 ";
        } else {
            $order = " order by provider.providername limit 500";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            provider.positiontitle,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, blocked1.blockee, profileroomid, provider.score
            from provider
            left join blocked blocked1 on blocked1.blockee = provider.providerid and blocked1.blocker = ?
            where  provider.active='Y' and termsofuse is not null
            
            and 
            (
                provider.sponsor = '$_SESSION[sponsor]' 
                and provider.enterprise = 'Y' 
                or
                provider.providerid in 
                    (select providerid from sponsorlist where sponsor='$_SESSION[sponsor]')
                or
                ( publish = 'Y' and 
                ? != '' and ( provider.handle = ? or provider.handle = ?)
                )
             )
            and
            (provider.providername like ? or provider.handle like ?)
            and provider.providername!=''
            $order
                ",array($providerid,$find,$find,"@".$find,"%".$find."%","%".$find."%"));
        
        
        $count = 0;
        while($row = pdo_fetch($result)){
            
            if($count == 0){

            }
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            
            $blocked = "";
            if($row['blockee']!=''){
                $blocked = "<div class='smalltext'  style='color:firebrick;cursor:pointer'>Blocked</div>";
            }

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
            
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }

            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }
             
            $list .= "
                <div class='meetupenterpriselistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;margin-bottom:10px;
                    text-align:left;word-wrap:break-word;;
                    overflow:hidden;'>
                    <div class='$profileaction rounded' style='cursor:pointer;background-color:$global_background;color:$global_textcolor;padding:15px;height:80%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'  
                     data-roomid='$row[profileroomid]'
                     data-profile='Y'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:90%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            $row[providername]<span class='smalltext' style='color:$global_textcolor'>
                             <br>$id<br>
                             $row[positiontitle]
                             <br>
                             $blocked
                            $row[publishprofile]</span>
                    </div>
                </div>
                ";
        }    
        if($count == 0){
            $list .= "<div class='meetupenterpriselistarea' style='padding:20px;color:$global_textcolor'>Enterprise members not found</div>";
        }


        
        return $list;
    }            
    function ActivityList( $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_textcolor_reverse;
        global $global_activetextcolor_reverse;
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxmedal_common;
        global $iconsource_braxwinner_common;
        global $menu_top;
        global $menu_handle;
        
        $list = "
            <span class='meetuprecentshow' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    $menu_top
                    <br>
                                
                </div>
            </span>
            ";
        if($mode!='P2'){
            return "";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, blocked1.blockee, 
            DATE_FORMAT(provider.lastactive, '%b %d/%y') as lastactive, blocked1.blockee,
            provider.lastactive as lastactive2, provider.profileroomid, provider.score,
            (select 'Y' from followers where followers.providerid = provider.providerid and followers.followerid = $_SESSION[pid] ) as followed
            from provider
            left join blocked blocked1 on blocked1.blockee = provider.providerid and blocked1.blocker = ?
            where  provider.active='Y' and termsofuse is not null
            and  
            (
                provider.providerid  in (select targetproviderid from contacts where providerid = ? )
                    
                or 
                
                provider.publish = 'Y'
            )
            order by provider.score desc, provider.createdate desc limit 200
                ",array($providerid,$providerid));
        $count = 0;
        while($row = pdo_fetch($result)){
            
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            if($count == 0){

            }
            
            $blocked = "";
            if($row['blockee']!=''){
                $blocked = "<div class='smalltext'  style='color:firebrick;cursor:pointer'>Blocked</div>";
            }
            $following = '';
            if($row['followed']=='Y'){
                $following = "<div class='smalltext' style='border-radius:5px;text-align:center;background-color:$global_textcolor;color:$global_background;margin-top:5px'>Following</div>";
            }

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxwinner_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
             
             if(intval($row['lastactive2'])==0){
                 $row['lastactive']= $row['joined'];
             }
             
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }

            $list .= "
                <div class='meetupcontactlistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;margin-bottom:10px;margin-right:5px;
                    text-align:left;word-wrap:break-word;
                    overflow:hidden;'>
                    
                    <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;padding:15px;height:80%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'    
                     data-roomid='$row[profileroomid]'
                     data-profile='Y'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:90%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            $row[providername]<span class='smalltext' style='color:$global_textcolor'>
                             <br>$id<br>
                            <br>$row[publishprofile]<br>
                            $following
                            <br>
                             $blocked
                            </span>
                    </div>
                </div>
                ";
        }    
        if($count == 0){
            $list .= "<div class='meetupcontactlistarea pagetitle3' style='color:$global_textcolor_reverse;background-color:$global_bottombar_color;padding:20px;max-width:300px;margin:auto'>
                        <div class='circular3 gridnoborder' style=';overflow:hidden;margin:auto'>
                            <img class='' src='../img/agent.jpg' style='width:100%;height:auto' />
                        </div>
                    This list will build as members in your community build reputations.</div>";
            
        }

        
        return $list;
    } 
    function FriendList( $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_textcolor_reverse;
        global $global_background;
        global $global_bottombar_color;
        global $global_activetextcolor_reverse;
        global $iconsource_braxmedal_common;
        global $menu_friends;
        global $menu_handle;
        global $global_activetextcolor;
        global $iconsource_braxclose_common;    
        
        $peoplelimit = 200;
        $result = pdo_query("1",
                "select peoplelimit from provider where providerid = ?",array($_SESSION['pid']));
        if($row = pdo_fetch($result)){
            $peoplelimit = $row['peoplelimit'];
            if($peoplelimit < 100){
                $peoplelimit = 100;
            }
            if($peoplelimit > 5000){
                $peoplelimit = 5000;
            }
        }
        
        
        $list = "
            <span class='meetuprecentshow' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    $menu_friends
                    <br>
                                
                </div>
            </span>
            ";
        if($mode!='P7'){
            return "";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, blocked1.blockee, 
            DATE_FORMAT(provider.lastactive, '%b %d/%y') as lastactive, blocked1.blockee,
            provider.lastactive as lastactive2, provider.profileroomid, provider.score,
            friends.level
            from friends
            left join provider on provider.providerid = friends.friendid
            left join blocked blocked1 on blocked1.blockee = provider.providerid and blocked1.blocker = $providerid
            where  provider.active='Y' 
            and friends.providerid = ?
            order by provider.providername asc limit $peoplelimit
                ",array($providerid));
        $count = 0;
        while($row = pdo_fetch($result)){
            
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            if($count == 0){

            }
            
            $blocked = "";
            if($row['blockee']!=''){
                $blocked = "<div class='smalltext'  style='color:firebrick;cursor:pointer'>Blocked</div>";
            }

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
             
             if(intval($row['lastactive2'])==0){
                 $row['lastactive']= $row['joined'];
             }
             
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }

            $level = ucfirst(strtolower($row['level']));   
            if($level == 'Friend'){
                $level = '';
            }
            $list .= "
                <div class='meetupcontactlistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;margin-bottom:10px;margin-right:5px;
                    text-align:left;word-wrap:break-word;
                    overflow:hidden;'>
                    
                    <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;padding:15px;height:65%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'    
                     data-roomid='$row[profileroomid]'
                     data-profile='Y'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:90%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            $row[providername]<span class='smalltext' style='color:$global_textcolor'>
                             <br>$id<br>
                             <br><b>$level</b><br>
                            </span>
                            <br>
                    </div>
                    <br><center><span class='managefriends' data-friendid='$row[providerid]' data-mode='D' style='cursor:pointer;color:$global_activetextcolor'><img class='icon15' src='$iconsource_braxclose_common' /></span></center>
                </div>
                ";
        }    
        if($count == 0){
            $list .= "
                    <div class='circular3 gridnoborder' style=';overflow:hidden;margin:auto'>
                        <img class='' src='../img/agent.jpg' style='width:100%;height:auto' />
                    </div>
                    <div class='meetupcontactlistarea pagetitle3 tipbubble' style='color:$global_textcolor_reverse;background-color:$global_bottombar_color;padding:20px;max-width:300px;margin:auto'>
                        <div class='tipbubble' style='color:$global_textcolor_reverse;background-color:$global_bottombar_color;padding:30px'>
                            Add friends to build this list. Friends can have additional access to your photos.
                        </div>
                     </div>";
            
            
        }

        
        return $list;
    }    
    
    function FollowersList( $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_textcolor_reverse;
        
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxmedal_common;
        global $menu_friends;
        global $menu_following;
        global $menu_handle;
        global $global_activetextcolor;
        global $global_activetextcolor_reverse;
        global $iconsource_braxclose_common;    
        
        $peoplelimit = 200;
        $result = pdo_query("1",
                "select peoplelimit from provider where providerid = ?",array($_SESSION['pid']));
        if($row = pdo_fetch($result)){
            $peoplelimit = $row['peoplelimit'];
            if($peoplelimit < 100){
                $peoplelimit = 100;
            }
            if($peoplelimit > 5000){
                $peoplelimit = 5000;
            }
        }
        
        
        $list = "
            <span class='meetuprecentshow' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    $menu_following
                    <br>
                                
                </div>
            </span>
            ";
        if($mode!='P8'){
            return "";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, blocked1.blockee, 
            DATE_FORMAT(provider.lastactive, '%b %d/%y') as lastactive, blocked1.blockee,
            provider.lastactive as lastactive2, provider.profileroomid, provider.score,
            followers.level
            from followers
            left join provider on provider.providerid = followers.providerid
            left join blocked blocked1 on blocked1.blockee = provider.providerid and blocked1.blocker = ?
            where  provider.active='Y' 
            and followers.followerid = ?
            order by provider.providername asc limit $peoplelimit
                ",array($providerid,$providerid));
        $count = 0;
        while($row = pdo_fetch($result)){
            
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            if($count == 0){

            }
            
            $blocked = "";
            if($row['blockee']!=''){
                $blocked = "<div class='smalltext'  style='color:firebrick;cursor:pointer'>Blocked</div>";
            }

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
             
             if(intval($row['lastactive2'])==0){
                 $row['lastactive']= $row['joined'];
             }
             
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }
            $level = '';
            if($row['level'] == 'I'){
                $level = 'Incognito';
            }
            $list .= "
                <div class='meetupcontactlistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;margin-bottom:10px;margin-right:5px;
                    text-align:left;word-wrap:break-word;
                    overflow:hidden;'>
                    
                    <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;padding:15px;height:65%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'    
                     data-roomid='$row[profileroomid]'
                     data-profile='Y'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:90%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            <span class='smalltext' style='color:$global_textcolor'>
                                $row[providername]
                                
                             <br>$id<br>
                             <b>$level</b><br>
                            </span>
                            <br>
                    </div>
                    <br><center><span class='managefriends' data-friendid='$row[providerid]' data-mode='UF' style='cursor:pointer;color:$global_activetextcolor'><img class='icon15' src='$iconsource_braxclose_common' /></span></center>
                </div>
                ";
        }    
        if($count == 0 ){
            $list .= "<div class='meetupcontactlistarea pagetitle2a' style='color:$global_textcolor;padding:20px;max-width:300px;margin:auto'>
                        <div class='circular3 gridnoborder' style=';overflow:hidden;margin:auto'>
                            <img class='' src='../img/agent.jpg' style='width:100%;height:auto' />
                        </div>
                        <div class='tipbubble pagetitle3' style='background-color:$global_bottombar_color;padding:30px;color:$global_textcolor_reverse;margin:auto'>
                        You can follow people and limit notifications to those you follow. Enable this in My Account Settings.
                        </div>";
        }

        
        return $list;
    }            
    function BannedList( $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxmedal_common;
        global $menu_friends;
        global $menu_handle;
        global $global_activetextcolor;
        global $iconsource_braxclose_common;    
        
        $list = "
            <span class='meetuprecentshow' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    Banned
                    <br>
                                
                </div>
            </span>
            ";
        if($mode!='P9'){
            return "";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, 
            DATE_FORMAT(provider.lastactive, '%b %d/%y') as lastactive,
            provider.lastactive as lastactive2, provider.profileroomid, provider.score,
            provider.banid, provider.iphash2
            from provider  where (banid!='' or 
            (select count(*) from provider p2 where p2.iphash2 = provider.iphash2 and active='Y') > 1 
            ) and active='Y' and (ipsource not in ('whitelist','internal') and ipsource is not null and ipsource!='' )
            order by provider.lastaccess desc limit 1000
                ",null);
        $count = 0;
        while($row = pdo_fetch($result)){
            
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            if($count == 0){

            }
            
            $blocked = "";

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
             
             if(intval($row['lastactive2'])==0){
                 $row['lastactive']= $row['joined'];
             }
             
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }
            $level = '';
            $banid = substr($row['banid'],0,10);
            $list .= "
                <div class='meetupcontactlistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;margin-bottom:10px;margin-right:10px;
                    text-align:left;word-wrap:break-word;
                    overflow:hidden;'>
                    
                    <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;padding:15px;height:65%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'    
                     data-roomid='$row[profileroomid]'
                     data-profile='Y'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:95%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            <span class='smalltext' style='color:$global_textcolor'>
                                $row[providername]
                                
                             <br>$id<br>
                             <b>$row[joined]</b><br>
                                 $banid
                            </span>
                            <br>
                    </div>
                    <br><center><span class='managefriends' data-friendid='$row[providerid]' data-mode='XBAN' style='cursor:pointer;color:$global_activetextcolor'><img class='icon15' src='$iconsource_braxclose_common' /></span></center>
                </div>
                ";
        }    
        
        return $list;
    }            
                 
    function NewList( $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxmedal_common;
        global $menu_friends;
        global $menu_handle;
        global $global_activetextcolor;
        global $iconsource_braxclose_common;    
        
        $list = "
            <span class='meetuprecentshow' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    Banned
                    <br>
                                
                </div>
            </span>
            ";
        if($mode!='P10'){
            return "";
        }
        
        $result = pdo_query("1","
            select 
            provider.providername, provider.alias, provider.avatarurl, provider.t_avatarurl, provider.companyname,
            provider.providerid, provider.handle, provider.publishprofile, provider.replyemail,
            DATE_FORMAT(provider.createdate, '%b %d/%y') as joined, 
            DATE_FORMAT(provider.lastactive, '%b %d/%y') as lastactive,
            provider.lastactive as lastactive2, provider.profileroomid, provider.score,
            provider.banid, provider.iphash2, provider.appname, provider.joinedvia
            from provider  where active='Y' and handle not like '@braxdemo%'
            order by provider.createdate desc limit 200
                ",null);
        $count = 0;
        while($row = pdo_fetch($result)){
            
            $count++;
            $id = $row['handle'];
            if($id == ''){
                $id = $row['replyemail'];
            } else {
                $row['replyemail']='';
            }
            if($count == 0){

            }
            
            $blocked = "";
            //if($row['blockee']!=''){
            //    $blocked = "<div class='smalltext'  style='color:firebrick;cursor:pointer'>Blocked</div>";
            //}

             $avatar = RootServerReplace($row['avatarurl']);
             $t_avatar = RootServerReplace($row['t_avatarurl']);
             if($t_avatar!==''){
                 $avatar = $t_avatar;
             }
             if($avatar == "$rootserver/img/faceless.png" || $avatar == ''){
                 $avatar = "$rootserver/img/newbie2.jpg";
             }
             if(intval($row['score'])>0){
                 $row['publishprofile']= "<span class='smalltext2' title='Reputation Score'><img class='icon15' src='$iconsource_braxmedal_common' style='margin-top:5px;opacity:.4;margin-right:5px'/>$row[score]</span>";
             } else {
                 $row['publishprofile']= "";
                 
             }
             
             if(intval($row['lastactive2'])==0){
                 $row['lastactive']= $row['joined'];
             }
             
            $profileaction = 'feed';
            if(intval($row['profileroomid'])==0){
                $profileaction = 'userview';
            }
            
            global $icon_darkmode;
            $shadow = "shadow gridstdborder";
            $extrastyle = "";
            if($icon_darkmode){
                $shadow = "";
                $extrastyle = "filter:brightness(120%)";
            }
            $level = '';
            if($row['appname']=='Brax.Me'){
                $row['appname']='';
            }
            
            $list .= "
                <div class='meetupcontactlistarea rounded stdlistbox $shadow' 
                    style='display:inline-block;vertical-align:top;$extrastyle;
                    text-align:left;background-color:$global_background;margin-bottom:10px;margin-right:10px;
                    text-align:left;word-wrap:break-word;
                    overflow:hidden;'>
                    
                    <div class='$profileaction' style='cursor:pointer;color:$global_textcolor;padding:15px;height:65%;overflow:hidden'
                     data-providerid='$row[providerid]' data-name='$row[providername]'    
                     data-roomid='$row[profileroomid]'
                     data-profile='Y'
                    data-caller='find'
                     data-mode ='S' data-title='' data-passkey64='' 
                     >
                        <div class='circular2 gridnoborder' style='overflow:hidden;background-color:$global_bottombar_color;max-height:95%' title='User Photo'>
                            <img class='' src='$avatar' style='height:auto;width:100%;'/>
                        </div>
                            <span class='smalltext' style='color:$global_textcolor'>
                                $row[providername]
                                
                             <br>$id<br>
                             <b>$row[joined]</b><br>
                            $row[appname] $row[joinedvia]
                            </span>
                            <br>
                    </div>
                    <br><center><span class='managefriends' data-friendid='$row[providerid]' data-mode='XBAN' style='cursor:pointer;color:$global_activetextcolor'><img class='icon15' src='$iconsource_braxclose_common' /></span></center>
                </div>
                ";
        }    
        
        return $list;
    }            
      
    function BlockedList( $mode)
    {
        global $appname;
        global $providerid;
        global $rootserver;
        global $global_textcolor;
        global $global_background;
        global $global_bottombar_color;
        global $iconsource_braxmedal_common;
        global $menu_friends;
        global $menu_handle;
        global $global_activetextcolor;
        global $iconsource_braxclose_common;    
        
        $list = "
            <span class='meetuprecentshow' style='display:none;color:black'>
                <div class='pagetitle2' style='padding-left:10px;padding-right:10px;padding-top:0px;padding-bottom:5px;color:$global_textcolor'>
                    Blocked
                    <br>
                                
                </div>
            </span>
            ";
        if($mode!='P11'){
            return "";
        }
        
    

    $rownum = 0;

    $list =  "<h1>Blocked List</h1>";
    $list .=  "<table class='smalltext' style='font-size:12px;color:$global_textcolor'>";
    
    $result = pdo_query("1", "
            select 
            provider.providername as blockedname, provider.createdate,
            (select providername from provider where blocked.blocker = provider.providerid) as blockername,
            blocked.created as blockdate
            from blocked 
            left join provider on blockee = provider.providerid
            where provider.active = 'Y'
            order by blockedname, blockername
        ",null);

    
    while ($row = pdo_fetch($result)) 
    {
        
        $rownum=$rownum+1;

        
        $list .=  "<tr class=\"messages\" style='color:$global_textcolor'>";
            
        
            $list .=  "<td>$rownum</td>";
            $list .=  "<td>$row[blockedname]</td>";
            $list .=  "<td>$row[createdate]</td>";
            $list .=  "<td>$row[blockername]</td>";
            $list .=  "<td>$row[blockdate]</td>";


        $list .=  "</tr>";
        
    }
    $list .= "</table>";
        
    
        
        return $list;
    }            
                             
     
?>