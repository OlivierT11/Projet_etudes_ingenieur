<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

#region File inclusions

$database = new Database();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);
$news = new News($database, $log);
$action = new Action($database, $log);
$stat = new Stat($database, $log, $action);
$statistic = new Statistic($database, $log, $news);

#endregion
