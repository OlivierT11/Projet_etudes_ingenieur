<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if($_SESSION['player_area'] != 'inside'){
    header("Location:index.php?page=".$_SESSION['player_area']);
    die();
}

#region File inclusions

//require dirname(dirname(__FILE__))."/tools/tools.php";
require_once dirname(dirname(__FILE__))."/tools/tools.php";


//require dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

//require dirname(dirname(__FILE__))."/class/class.invent.php";
//$invent = new Invent();

//require dirname(dirname(__FILE__))."/class/class.map.php";

//require dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

//require dirname(dirname(__FILE__))."/class/class.cache.php";
$cache = new Cache($_SESSION['id_city']);

//require dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

//require dirname(dirname(__FILE__))."/class/class.action.php";
$action = new Action($database, $log);

//require dirname(dirname(__FILE__))."/class/class.stat.php";
$stat = new Stat($database, $log, $action);

//require dirname(dirname(__FILE__))."/class/class.statistic.php";
$statistic = new Statistic($database, $log, $news);

#endregion


function getMajorNewsList($database)
{
    $query="SELECT p.player_name, 
                cn.new_date, cn.new_content, cn.new_category
                FROM city_new cn INNER JOIN player p ON cn.id_player = p.id_player 
                WHERE cn.id_city=".$_SESSION['id_city']." AND cn.is_major=1 ORDER BY cn.id_city_new DESC LIMIT 50";
    try {
        $result=[];
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['new_date'][] = $data['new_date'];  //stored in the format $var = date('H:i');
                $result['new_content'][] = $data['new_content'];
                $result['new_category'][] = $data['new_category'];
                $result['player_name'][] = $data['player_name'];
            }
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $result;
}

function getMinorNewsList($database)
{
    $query="SELECT p.player_name, 
                cn.new_date, cn.new_content, cn.new_category
                FROM city_new cn INNER JOIN player p ON cn.id_player = p.id_player 
                WHERE cn.id_city=".$_SESSION['id_city']." AND cn.is_major=0 ORDER BY cn.id_city_new DESC LIMIT 50";
    try {
        $result=[];
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['new_date'][] = $data['new_date'];
                $result['new_content'][] = $data['new_content'];
                $result['new_category'][] = $data['new_category'];
                $result['player_name'][] = $data['player_name'];
            }
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $result;
}


function getAbyssIdWhenOpen($database)
{
    $query = 'SELECT id_abyss, abyss_x, abyss_y
                FROM abyss_city
                WHERE id_city='.$_SESSION['id_city'];
    try {
        $result = [];
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result['id_abyss'] = $data['id_abyss'];
            $result['abyss_x'] = $data['abyss_x'];
            $result['abyss_y'] = $data['abyss_y'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
        return false;
    }

    return $result;
}
