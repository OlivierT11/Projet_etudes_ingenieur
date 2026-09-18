<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if($_SESSION['player_area'] != 'dj'){
    header("Location:index.php?page=".$_SESSION['player_area']);
    die();
}


#region File inclusions

//require dirname(dirname(__FILE__))."/tools/tools.php";

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

//check if there is a camp at the player location
function getIdCamp($database)
{
    $query = 'SELECT id_city_camp 
                FROM city_camp 
                WHERE id_city='.$_SESSION['id_city'].' AND camp_pos_x ='.$_SESSION['player_pos_x'].' AND camp_pos_y = '.$_SESSION['player_pos_y'].' AND camp_area = "dj"';
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result = $data['id_city_camp'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    return $result;
}

//get the id of the map_dj entry
function getIdDj($database)
{
    if($_SESSION['player_area'] == 'outside'){
        $query = 'SELECT mdj.id_dj, mdj.dj_x, mdj.dj_y
                FROM map_dj mdj INNER JOIN map m ON m.id = mdj.id_area
                WHERE m.id_city='.$_SESSION['id_city'].' AND m.x ='.$_SESSION['player_pos_x'].' AND m.y = '.$_SESSION['player_pos_y'].'
                LIMIT 1';
    } else if($_SESSION['player_area'] == 'dj'){
        $query = 'SELECT mdj.dj_x, mdj.dj_y
                FROM map_dj mdj INNER JOIN dj ON dj.id_dj_area = mdj.id_dj_area
                WHERE dj.id_city='.$_SESSION['id_city'].' AND dj.dj_x ='.$_SESSION['player_pos_x'].' AND dj.dj_y = '.$_SESSION['player_pos_y'].'
                LIMIT 1';
    }
    
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            //$result['id_dj'] = $data['id_dj'];
            $result['dj_x'] = $data['dj_x'];
            $result['dj_y'] = $data['dj_y'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    return $result;
}



function getFoeNbr($database)
{
    $query = 'SELECT dj_foe FROM dj where id_city='.$_SESSION['id_city'].' AND dj_x ='.$_SESSION['player_pos_x'].' AND dj_y = '.$_SESSION['player_pos_y'];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result = $data['dj_foe'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    return $result;

}


function getDj2Id($database)
{
    $query = 'SELECT id_dj2, dj2_x, dj2_y
                FROM dj_dj2
                WHERE id_dj='.$_SESSION['id_dj'];
    try {
        $result = [];
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result['id_dj2'] = $data['id_dj2'];
            $result['dj2_x'] = $data['dj2_x'];
            $result['dj2_y'] = $data['dj2_y'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    return $result;
}
