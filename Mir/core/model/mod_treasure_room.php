<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if($_SESSION['player_area'] != 'inside' && $_SESSION['player_area'] != 'camp'){
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


//inutile car on a déja ajax_treasure_room.php, mais il envoie de l'html dans l'ajax, ce qui n'est pas propre.

/*
function getInventPlayer(){

    

    $query="SELECT id_item, item_name, item_lvl, item_dur, item_descr, item_category, id_camp, id_harvester, id_crafter
        FROM city_item ci JOIN item i ON i.id_item = ci.id_item
        WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND item_pos='invent' AND is_alive=1";
     try {
         $res=$database->mysql->query($query);
         $result=[];
         if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $result['id_item'] = $data['id_item'];
                $result['item_name'] = $data['item_name'];
                $result['item_lvl'] = $data['item_lvl'];
                $result['item_dur'] = $data['item_dur'];
                $result['item_descr'] = $data['item_descr'];
                $result['item_category'] = $data['item_category'];
                $result['id_camp'] = $data['id_camp'];
                $result['id_harvester'] = $data['id_harvester'];
                $result['id_crafter'] = $data['id_crafter'];
            }
        }
     } catch (PDOException $e){
         echo "Erreur dans la requête mod_treasure_room.php";
     }

     return $result;

}*/