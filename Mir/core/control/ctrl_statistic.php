<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}



//get local player statistics list
//$localPlayerStatistics = getLocalPlayerStatistics($database);

// Si le joueur est dans une ville, affiche les statistiques locales de la ville en cours
$localPlayerStatisticsHTML = '';
if(isset($_SESSION['player_local_statistics']))
{
    //$n = sizeof($_SESSION['player_local_statistics']); 

    foreach ($_SESSION['player_local_statistics'] as $key => $stat){ //3D array: session = key et stat = 2nd dimension
        $localPlayerStatisticsHTML .= '<li><p>';
    // foreach ($stat => $detail){
            $localPlayerStatisticsHTML .= $stat['descr'].' : '.$stat['amount'];
    //  }
        $localPlayerStatisticsHTML .= '</p></li>';
    }
}
else
{
    $localPlayerStatisticsHTML = '<li><p>Vous n\'êtes dans aucune ville.</p></li>';
}
/*
//get player stats for the current city
$localCityStatistics = getLocalCityStatistics($database);

$n2 = sizeof($localCityStatistics['new_date']); //stored in the format $var = date('H:i');

$localCityStatisticsHTML = '';
for ($i=0; $i<$n2; $i++){

    $localCityStatisticsHTML .= '<li><p>';
    $localCityStatisticsHTML .= $localCityStatistics['new_date'][$i].' | '.$localCityStatistics['player_name'][$i].' | '.$localCityStatistics['new_content'][$i];
    $localCityStatisticsHTML .= '</p></li>';
    
}

//get global player statistics
$globalPlayerStatistics = getGlobalPlayerStatistics($database);

$n2 = sizeof($globalPlayerStatistics['new_date']); //stored in the format $var = date('H:i');

$globalPlayerStatisticsHTML = '';
for ($i=0; $i<$n2; $i++){

    $globalPlayerStatisticsHTML .= '<li><p>';
    $globalPlayerStatisticsHTML .= $globalPlayerStatistics['new_date'][$i].' | '.$globalPlayerStatistics['player_name'][$i].' | '.$globalPlayerStatistics['new_content'][$i];
    $globalPlayerStatisticsHTML .= '</p></li>';
    
}*/