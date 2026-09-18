<?php

/******************** */
/* DISPLAY STATISTICS */
/******************** */

$cityStats = getCityStats($database);

$n = sizeof($cityStats); 

$cityStatsHTML = '';
foreach ($cityStats as $key => $stat){ // 1 => 344
    $cityStatsHTML .= '<li><p>';
   // foreach ($stat => $detail){
        $cityStatsHTML .= $stat['name'] . " : <strong>" . $stat['amount'] . "</strong>";
  //  }
    $cityStatsHTML .= '</p></li>';
}

$bestPlayerStats = getBestPlayerStats($database);

$n = sizeof($bestPlayerStats); 

$bestPlayerStatsHTML = '';
foreach ($bestPlayerStats as $key => $stat){ // 1 => 344
    $bestPlayerStatsHTML .= '<li><p>';
   // foreach ($stat => $detail){
        $bestPlayerStatsHTML .= $stat['name'] . " : <strong>" . $stat['amount'] . "</strong> <strong>(" . $stat['player'] . ")</strong>";
  //  }
    $bestPlayerStatsHTML .= '</p></li>';
}

/************************ */
/* REINITIALIZE DATABASE  */
/************************ */

// Almost identical to when the player leaves the city.

$database->mysql->beginTransaction();

$city->removePlayerFromCity();

// check if the city hasn't been destroyed already, then destroy it
$city->destroyCity();

$database->mysql->commit();

/************************ */
/* REINITIALIZE SESSIONS  */
/************************ */

// Reinitialize the sesions

$idPlayer = $_SESSION['id_player'];
session_destroy();
session_start();
$_SESSION['id_player'] = $idPlayer;

// change the area at the end, so the player gets to city if he reloads (as the databases will be erased)
$_SESSION['player_area'] = 'home';