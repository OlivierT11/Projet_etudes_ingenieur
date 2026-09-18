<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if($_SESSION['player_area'] != 'abyss'){
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

function getIdCamp($database)
{
    $query = 'SELECT id_city_camp FROM city_camp 
                where id_city='.$_SESSION['id_city'].' 
                    AND camp_pos_x ='.$_SESSION['player_pos_x'].'
                    AND camp_pos_y = '.$_SESSION['player_pos_y'].'
                    AND camp_area = "abyss"';
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result = $data['id_city_camp'];
        }
    } catch (PDOException $e){
        $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
        return false;
    }

    return $result;
}

function getHellId($database)
{
    $query = 'SELECT id_hell, abyss_x, abyss_y
                FROM abyss_hell
                WHERE id_abyss='.$_SESSION['id_abyss'];
    try {
        $result = [];
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result['id_hell'] = $data['id_hell'];
            $result['hell_x'] = $data['abyss_x'];
            $result['hell_y'] = $data['abyss_y'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
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

function getFoeNbr($database)
{
    $query = 'SELECT abyss_foe FROM abyss where id_abyss='.$_SESSION['id_abyss'].' AND abyss_x ='.$_SESSION['player_pos_x'].' AND abyss_y = '.$_SESSION['player_pos_y'];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result = $data['abyss_foe'];
        }
    } catch (PDOException $e){
        $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
        return false;
    }

    return $result;
}
