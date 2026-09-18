<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}



$playersList = getPlayersList($database);
$muteList = getMuteList($database);

// save the array in session, to be checked later for mute/unmute

$n = sizeof($playersList['player_name']); 

//TODO add title, likes, highest lvl, master
$playerListHTML = '';
for ($i=0; $i<$n; $i++){

    $playerListHTML .= '<li><p>';
    $playerListHTML .= '<strong><span class="player-obj">'.$playersList['player_name'][$i].'</span></strong>'; //for tooltip player obj

    // Display the main stat
    $playerListHTML .= '  '.L($playersList['bestStatName'][$i]) . ' (' . $playersList['bestStatLvl'][$i] . ') ';

    // Display the mastery icon
    if($playersList['is_master'][$i] == 1){
        $playerListHTML .= ' <span id="mastery-logo-player" title="Maitrise. Ajoute un bonus de 20% à cette stat. Arrive quand son niveau est deux fois plus grand que la stat 2."></span> ';
    }

    // Display mute Button
    // display a mute button if the player is not the user
    if($playersList['player_name'][$i] != $_SESSION['player_name']){

        // is the player already muted ? 
        if(in_array($playersList['id_player'][$i], $muteList)) {
            $playerListHTML .= '&nbsp<button id="'.$playersList['id_player'][$i].'" class="btn-unmute btn btn-dark btn-sm">Unmute</button>';
        
        //if not, display the mute button
        } else {
            $playerListHTML .= '&nbsp<button id="'.$playersList['id_player'][$i].'" class="btn-mute btn btn-light btn-sm">Mute</button>';
        }

    }
    $playerListHTML .= '</p></li>';
    
}


$playersListGeneral = getPlayersListGeneral($database);

// save the array in session, to be checked later for mute/unmute

$n = sizeof($playersListGeneral['player_name']); 

//TODO add title, likes, highest lvl, master
$playerListGeneralHTML = '';
for ($i=0; $i<$n; $i++){

    $playerListGeneralHTML .= '<li><p>';
    $playerListGeneralHTML .= '<strong><span class="player-obj">'.$playersListGeneral['player_name'][$i].'</span></strong>'; //for tooltip player obj

    // Display the main stat
    $playerListGeneralHTML .= '  '. L($playersListGeneral['bestStatName'][$i]) . ' (' . $playersListGeneral['bestStatLvl'][$i] . ') ';

    // Display the mastery icon
    if($playersListGeneral['is_master'][$i] == 1){
        $playerListGeneralHTML .= ' <span id="mastery-logo-player" title="Maitrise. Ajoute un bonus de 20% à cette stat. Arrive quand son niveau est deux fois plus grand que la stat 2."></span> ';
    }

    // Display mute Button
    // display a mute button if the player is not the user
    if($playersListGeneral['player_name'][$i] != $_SESSION['player_name']){

        // is the player already muted ? 
        if(in_array($playersListGeneral['id_player'][$i], $muteList)) {
            $playerListGeneralHTML .= '&nbsp<button id="'.$playersListGeneral['id_player'][$i].'" class="btn-unmute btn btn-dark btn-sm">Unmute</button>';
        
        //if not, display the mute button
        } else {
            $playerListGeneralHTML .= '&nbsp<button id="'.$playersListGeneral['id_player'][$i].'" class="btn-mute btn btn-light btn-sm">Mute</button>';
        }

    }
    $playerListGeneralHTML .= '</p></li>';
    
}