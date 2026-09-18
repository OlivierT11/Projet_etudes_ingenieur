<?php

if(!isset($_SESSION))
    session_start();
    
if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
    die();
}

#region Classes

//loadCoreClassesForAjaxFiles(); //database, log, stat etc

require_once dirname(dirname(__FILE__))."/config/config.php";

require_once dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

require_once dirname(dirname(__FILE__))."/class/class.map.php";
require_once dirname(dirname(__FILE__))."/tools/tools.php";

require_once dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

require_once dirname(dirname(__FILE__))."/class/class.cache.php";
$cache = new Cache($_SESSION['id_city']);

require_once dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

require_once dirname(dirname(__FILE__))."/class/class.action.php";
$action = new Action($database, $log);

require_once dirname(dirname(__FILE__))."/class/class.stat.php";
$stat = new Stat($database, $log, $action);

require_once dirname(dirname(__FILE__))."/class/class.statistic.php";
$statistic = new Statistic($database, $log, $news);

#endregion

// Permet de muter un joueur dans la BDD
if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="mute") {

    $playerToMute = $_REQUEST['playerToMute'];
    //$muteList = $_SESSION['muteList'];

    //check if the provided id is a number
    if ( filter_var($playerToMute, FILTER_VALIDATE_INT) === false ) {
        echo "Merci de ne pas jouer avec les IDs";
    }
    
    // muter le joueur
    $query="INSERT INTO player_mute(id_player, id_player_muted) 
        VALUE(:id, :player)";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':id' => $_SESSION['id_player'], ':player' => $playerToMute));

}


//Permet de rendre la parole à un joueur préalablement muet
if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="unmute") {

    $playerToUnmute = $_REQUEST['playerToUnmute'];
    $muteList = $_SESSION['muteList'];

     // check if the id is inside the session list
    /*if(!in_array($playerToUnmute, $muteList)){
        echo "Merci de ne pas jouer avec les ids";
        die();
    }*/

    //check if the provided id is a number
    if ( filter_var($playerToUnmute, FILTER_VALIDATE_INT) === false ) {
        echo "Merci de ne pas jouer avec les IDs";
    }
    
    // muter le joueur
    $query="DELETE FROM player_mute
    WHERE id_player = :id AND id_player_muted = :player";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute(array(':id' => $_SESSION['id_player'], ':player' => $playerToUnmute));

}