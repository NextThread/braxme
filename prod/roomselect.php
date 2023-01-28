<?php
session_start();
require("nohost.php");
require_once("config-pdo.php");
require_once("internationalization.php");
require_once("roomselect.inc.php");

    if(ServerTimeOutCheck()){
        
        return "T";

    }
    
require("validsession.inc.php");


    $time1 = microtime(true);

    $providerid = tvalidator("ID",$_POST['providerid']);

    $mode = @tvalidator("PURIFY",$_POST['mode']);
    $roomid = @tvalidator("ID",$_POST['roomid']);
    $find = stripslashes(htmlentities(@tvalidator("PURIFY",$_POST['find'],ENT_QUOTES)));
    
    SaveLastFunction($providerid,"", null);

    $result = pdo_query("1","
        select roomdiscovery, sponsor, roomfeed from provider where providerid = ? 
        ",array($providerid));
    $roomdiscovery = '';
    if($row = pdo_fetch($result)){
        $roomdiscovery = $row['roomdiscovery'];
        $sponsor = $row['sponsor'];
        $roomfeed = $row['roomfeed'];
        if($roomfeed == 'Y'){
            $feedforcechecked = '';
        } else {
            $feedforcechecked = 'checked=checked';
        }
    }
    
    $result = pdo_query("1",
        "
        update provider set roomnotified = now() where providerid = ?
        ",array($providerid));
    $result = pdo_query("1",
        "
        update alertrefresh set lastnotified = null where providerid=? and deviceid = '$_SESSION[deviceid]'
        ",array($providerid));


    $lock =  "<img class='icon15' src='../img/Lock-2_120px.png' style='top:3px;opacity:.3' title='Private Room' />";
    
    
    
    

    echo "
        <div class='gridnoborder' style='background-color:transparent;color:$global_textcolor;width:100%;margin:0;' >
        ";
    
    if($mode !='S' && $mode != 'FAQ'){
        echo "
            
            <div style='padding:10px'>
            ";
        if($roomdiscovery !== 'Y'){
            echo "
                <div class='pagetitle3' style='display:inline;white-space:nowrap;margin-top:20px;margin-left:0px;;color:$global_textcolor'>
                    <img class='icon25 showhidden' src='$iconsource_braxfind_common' title='Find Blog' />
                    <span class='showhiddenarea' style='display:none'>
                        <input class='showhidden2 inputline dataentry mainfont' id='findroom' placeholder='$menu_find $menu_room' name='findroom' type='text' size=20 value=''              
                            style='max-width:120px;padding-left:10px;;margin-bottom:10px;color:$global_textcolor'/>
                        <div class='mainfont roomselect' style='white-space:nowrap;display:inline;cursor:pointer;color:black' data-mode='F'>
                            <img class='icon25'   src='$iconsource_braxarrowright_common' 
                            style='top:3px' >
                        </div>
                    <span>    
                </div>
                &nbsp;
                <div class='pagetitle3' style='display:inline;white-space:nowrap;margin-top:20px;margin-left:0px;color:$global_textcolor'>
                    <span class='showhidden' style='cursor:pointer' title='Join a room using a hashtag'>
                        <img class='icon25 showhidden' src='$iconsource_braxjoin_common' title='Join Blog by Hashtag' />
                    </span>
                    <span class='showhiddenarea' style='display:none'>
                        <input class='inputline dataentry mainfont' id='roomhandle' placeholder='Hashtag' name='roomhandle' type='text' size=20 value=''              
                            style='max-width:120px;;margin-bottom:10px;color:$global_textcolor'/>
                        <div class='mainfont roomjoin' style='white-space:nowrap;display:inline;cursor:pointer;color:black'>
                            <img class='icon25 roomjoin' id='roomjoin' data-mode='J' src='$iconsource_braxarrowright_common' 
                            style='top:3px' >
                        </div>
                    <span>    
                </div>
                &nbsp;
                <br>
                ";
        }
        if($roomdiscovery == 'Y'){
            echo "
                <div class='pagetitle3' style='float:right;display:inline;white-space:nowrap;margin-top:0px;margin-left:0px;color:$global_textcolor'>
                    <span class='friends' data-caller='roomselect' style='cursor:pointer' title='Create and Manage Blog Rooms'>
                        <img class='icon25' src='$iconsource_braxsettings_common' title='Create and Manage Blogs' />
                    </span>
                </div>
                <div class='pagetitle3' style='display:inline;white-space:nowrap;margin-top:20px;margin-left:0px;color:$global_textcolor'>
                    <span class='roomdiscover' style='cursor:pointer' title='Discover Rooms'>
                        <img class='icon25 showhidden' src='$iconsource_braxglobe_common' title='Discover Blogs' />
                    </span>
                </div>
                &nbsp;
                <div class='pagetitle3' style='display:inline;white-space:nowrap;margin-top:20px;margin-left:0px;;color:$global_textcolor'>
                    <span class='showhiddenarea' style='display:none'><br><br></span>
                    <img class='icon25 showhidden' src='$iconsource_braxfind_common' title='Find Blog' />
                    <span class='showhiddenarea' style='display:none'>
                        <input class='showhidden2 inputline dataentry mainfont' id='findroom' placeholder='$menu_find $menu_room' name='findroom' type='text' size=20 value=''              
                            style='width:250px;padding-left:10px;;margin-bottom:10px;color:$global_textcolor'/>
                        <div class='mainfont roomselect' style='white-space:nowrap;display:inline;cursor:pointer;color:black' data-mode='F'>
                            <img class='icon25'   src='$iconsource_braxarrowright_common' 
                            style='top:3px' >
                        </div>
                    <span>    
                </div>
                &nbsp;
                <div class='pagetitle3' style='display:inline;white-space:nowrap;margin-top:20px;margin-left:0px;color:$global_textcolor'>
                    <span class='showhiddenarea' style='display:none'><br><br></span>
                    <span class='showhidden' style='cursor:pointer' title='Join a blog using a hashtag'>
                        <img class='icon25 showhidden' src='$iconsource_braxjoin_common' title='Join Blog by Hashtag' />
                    </span>
                    <span class='showhiddenarea' style='display:none'>
                        <input class='inputline dataentry mainfont' id='roomhandle' placeholder='Hashtag' name='roomhandle' type='text' size=20 value=''              
                            style='width:250px;;margin-bottom:10px;color:$global_textcolor'/>
                        <div class='mainfont roomjoin' style='white-space:nowrap;display:inline;cursor:pointer;color:black'>
                            <img class='icon25 roomjoin' id='roomjoin' data-mode='J' src='$iconsource_braxarrowright_common' 
                            style='top:3px' >
                        </div>
                    <span>    
                </div>
                &nbsp;
                <br>
                ";
        }
        
        if($mode!=='JCOMMUNITY'){    
            echo "
            <div class='gridnoborder' style='background-color:transparent;color:$global_textcolor;margin:auto;padding-left:10px;padding-top:10px;padding-right:20px;padding-bottom:3px;' >
                <center>
                <span class='pagetitle' style='color:$global_textcolor'>$menu_roomselect</span> 
                </center>
            </div>
            ";
        }
        echo "
                <div class='' style='text-align:center;vertical-align:top;'>
                <br>
            ";
    } else {
        echo "
            <div style='padding:10px'>
                <div class='' style='text-align:center;vertical-align:top;'>
            ";
        
    }
    
    if($mode == 'LIVE'){
        $mode = '';
    }
    
    
    if($roomdiscovery=='Y' && 
       $find=='' && 
       ( ($mode=='FEED' && $roomfeed=='Y') || ($mode=='FEEDFORCE')) ){

            $activeroomcontent = false;    
            require("roomselect2.php");
            if($activeroomcontent == true){
                RoomListFooter();
                exit();
        }

    }
    
    
    //echo "<br><br><div class='pagetitle2a' style='color:gray'><b>Recently Active</b></div><br>";
    /*****************************/
    $count = 0;
    //Station = Mode S
    if($mode == 'S'){
        //RadioRooms($providerid, $roomdiscovery, $mode);
        exit();
    }
    if($mode=='TRENDING' && $find == '' && $roomdiscovery == 'Y' ){
        echo "
                <div style='padding-bottom:20px;display:inline-block;width:90%'>
                    <div class='mainfont roomselect' data-mode='MYROOMS' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_rooms</div>
                    <!--<div class='mainfont roomselect' data-mode='COMMUNITY' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_community</div>-->
                </div>
                    ";
        
        TrendingRooms($providerid, $roomdiscovery);
    }
    if(($mode=='MYROOMS' || $mode=='' || $mode == 'FEED' || $mode == 'FEEDFORCE') && $find == '' && $roomdiscovery == 'Y' ){
        //if($_SESSION['superadmin']!='Y'){
        echo "
                <div style='padding-bottom:20px;display:inline-block;width:90%'>
                    <div class='mainfont roomselect' data-mode='TRENDING' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_trending</div>
                    <div class='mainfont roomdiscover'  style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_discoverrooms</div>
                    <div class='mainfont roomselect' data-mode='FEEDFORCE' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_whatsnew</div>
                    <!--
                    <div class='mainfont roomselect' data-mode='COMMUNITY' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_community</div>
                    -->
                </div>
                    ";
        
        
        //UnreadRooms($providerid );
        if($find == ''){
            FavoriteRooms($providerid, $find,"" );
            //ActiveRooms($providerid );
            WebsiteRooms($providerid, $find );
            //FeedRooms($providerid );
            if($_SESSION['enterprise']=='Y'){
                //OwnedRooms2($providerid, $find,"Y" );
            }
        }
        $count = OwnedRooms2($providerid, $find,"" );
        //CommunityRooms($providerid, $find );
        DiscoverRooms($providerid, $find, $roomdiscovery);
        //echo "test $roomdiscovery";
        //echo JoinCommunity($providerid, $roomdiscovery,"","<br>");
    }
    if((($mode=='MYROOMS' || $mode=='' || $mode == 'FEED')  && $find != '') || $roomdiscovery != 'Y' ){
        SaveLastFunction($providerid,"R", 0);
        
        echo "
                <div style='padding-bottom:20px;display:inline-block;width:90%'>
                    <div class='mainfont roomselect' data-mode='FEED' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>WHAT'S NEW</div>
                </div>
                    ";
        //AlphaRooms($providerid, $find );
        //OwnedRooms($providerid, $find );
        if($find == ''){
            FavoriteRooms($providerid, $find,"" );
            WebsiteRooms($providerid, $find );
            if($_SESSION['enterprise']=='Y'){
                OwnedRooms2($providerid, $find,"Y" );
            }
        }
        $count = OwnedRooms2($providerid, $find,"" );
        if($roomdiscovery == 'Y'){
            //CommunityRooms($providerid, $find );
        }
        DiscoverRooms($providerid, $find, $roomdiscovery);
    }
    if((($mode=='JCOMMUNITY')  ) && $roomdiscovery == 'Y' ){
        
        CommunityRooms($providerid, $find );
        FAQRooms($providerid, $find );
    }
    if((($mode=='COMMUNITY')  ) && $roomdiscovery == 'Y' ){
        
        echo "
                <div style='padding-bottom:20px;display:inline-block;width:90%'>
                    <div class='mainfont roomselect' data-mode='TRENDING' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_trending</div>
                    <div class='mainfont roomselect' data-mode='FEEDFORCE' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_whatsnew</div>
                    <div class='mainfont roomselect' data-mode='MYROOMS' style='float:right;cursor:pointer;margin-right:20px;color:$global_activetextcolor;'>$menu_rooms</div>
                </div>
                    ";
        CommunityRooms($providerid, $find );
        FAQRooms($providerid, $find );
    }
    if((($mode=='FAQ')  ) && $roomdiscovery == 'Y' ){
        
        FAQRooms($providerid, $find );
    }
    
    if(($mode == 'MYROOMS' || $mode =='') && $sponsor=='' && $roomdiscovery == 'N' && $_SESSION['enterprise']=='Y'){
        echo "<br><br>Automatically create your first $menu_room and website using the Create $enterpriseapp Space on your home page.<br><br><br>
             Enable SOCIAL MEDIA in My Account Info to see the public $menu_room<br><br>";
    }
    if($count > 0){
        echo "<br><br><br>
        ";    
        
    }
    
    echo "
        </div></div>
        
        </div>
        ";    

    if($count > 0 || $mode == 'COMMUNITY' || $mode == 'FAQ'){
        RoomListFooter();
    } else  
    if($mode!='S'  && $roomdiscovery == 'Y' && $find == ''){
        
        if($mode !=='JCOMMUNITY' && $mode!=='TRENDING'){
            NoRooms();
        }
        RoomListFooter();
        
    } else 
    if($_SESSION['enterprise']=='Y'){
        
        echo "
                <div class='pagetitle3' 
                    style='padding:20px;text-align:center;margin:auto;max-width:260px;width:80%;color:$global_textcolor;background-color:transparent'>
                    <div class='circular3' style=';overflow:hidden;margin:auto'>
                        <img class='' src='../img/agent.jpg' style='width:100%;height:auto' />
                    </div>
                    <div class='tipbubble pagetitle2a' style='padding:30px;color:$global_textcolor;background-color:$global_bottombar_color'>
                        You create Blog content in Blog Rooms.<br><br>
                        Use the $enterpriseapp Wizard to automatically create them for you.
                    </div>
                    <br>
                </div>
                <br><br><br>
        </div>
        ";
        
        RoomListFooter();
        
    }
    
    
    /*************************************************************
     * 
     * 
     * 
     * 
     *  ALPHABETICAL LIST
     * 
     * 
     * 
     */
$time2 = microtime(true);
    
    



    

?>
