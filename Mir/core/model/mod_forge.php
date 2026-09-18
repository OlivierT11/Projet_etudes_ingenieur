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
