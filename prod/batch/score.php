<?php
session_start();
set_time_limit ( 30 );
require_once("config-pdo.php");


$timestamp = time();
$chatmultiplier = 1;

    //Process Accounts active within last x days
    $days = 5;
    $result2 = pdo_query("1","
        SELECT provider.providerid FROM braxproduction.provider 
        left join staff on provider.providerid = staff.providerid
        where provider.active='Y'
        and 
        ( datediff(curdate(),staff.lastaccess) < $days 
          or
          (select count(*) from statusroom 
          where statusroom.owner = statusroom.providerid and 
          provider.providerid = statusroom.providerid) > 0 
        )
        order by staff.lastaccess desc
     ");   
    while($row2 = pdo_fetch($result2)){
        $providerid = $row2['providerid'];

        $score1 = 0;

        $result = pdo_query("1","
            SELECT count(*) as score1 from broadcastlog 
            where providerid = $providerid and mode ='B'
        ");
        if($row = pdo_fetch($result)){
            $score1 += intval($row['score1'])*200;
        }
        $result = pdo_query("1","
            SELECT count(*) as score1, sum(chatcount) as chatcount from broadcastlog 
            where providerid = $providerid and mode in ('V','R')
        ");
        if($row = pdo_fetch($result)){
            //Cap the credit of a chat count
            //There was a bug in chat count - will need to reaaddress
            $chatcount = intval($row['chatcount']);
            if($chatcount > 20){
                $chatcount = 20;
            }
            $score1 += intval($row['score1'])*10*$chatmultiplier;
            $score1 += $chatcount*2;
        }


        $result = pdo_query("1","
            SELECT count(*) as score1, 
            (select count(*) FROM braxproduction.statuspost where statuspost.roomid = statusroom.roomid) as postcount
            from statusroom
            where
            statusroom.owner = statusroom.providerid
            and statusroom.providerid = $providerid
        ");
        if($row = pdo_fetch($result)){
            if($row['postcount']>2){
                $score1 += intval($row['score1'])*100;
            }

        }

        $result = pdo_query("1","
            SELECT count(*) as score1 FROM braxproduction.statuspost 
            left join statusreads on statuspost.postid = statusreads.postid
            where statusreads.xaccode ='L' and statuspost.providerid = $providerid
            and statusreads.providerid != $providerid
        ");
        if($row = pdo_fetch($result)){
            $score1 += intval($row['score1'])*10;
        }
        $result = pdo_query("1","
            SELECT count(*) as score1 FROM braxproduction.statuspost 
            left join statusreads on statuspost.postid = statusreads.postid
            where statusreads.xaccode in ('P','R') and statuspost.providerid = $providerid
        ");
        if($row = pdo_fetch($result)){
            $score1 += intval($row['score1'])*10;
        }
        $result = pdo_query("1","
            SELECT count(*) as score1 FROM braxproduction.statuspost 
            left join statusreads on statuspost.postid = statusreads.postid
            where statusreads.xaccode ='L' and statusreads.providerid = $providerid
            and statuspost.providerid != $providerid
        ");
        if($row = pdo_fetch($result)){
            $score1 += intval($row['score1'])*2;
        }
        
        pdo_query("1","update provider set score = $score1 where providerid = $providerid ");
        
    }


?>