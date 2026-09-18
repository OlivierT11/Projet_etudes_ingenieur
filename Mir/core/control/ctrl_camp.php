<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

require_once dirname(dirname(__FILE__))."/tools/tools.php";



//get major news list
$majorNewsList = getMajorNewsList($database);

$n = sizeof($majorNewsList['new_date']); //stored in the format $var = date('H:i');

$majorNewsListHTML = '';
for ($i=0; $i<$n; $i++){

    $majorNewsListHTML .= '<li><p>';
    //if($majorNewsList['player_name'][$i] == $_SESSION['player_name']){
    //    $majorNewsListHTML .= '<span style="background-color:#FFA500;">';
    //}
    $majorNewsListHTML .= $majorNewsList['new_date'][$i].' | <strong>'.$majorNewsList['player_name'][$i].'</strong> | '.$majorNewsList['new_content'][$i];
    //if($majorNewsList['player_name'][$i] == $_SESSION['player_name']){
    //    $majorNewsListHTML .= '</span>';
    //}
    $majorNewsListHTML .= '</p></li>';
    
}

//get minor news list
$minorNewsList = getMinorNewsList($database);

$n2 = sizeof($minorNewsList['new_date']); //stored in the format $var = date('H:i');

$minorNewsListHTML = '';
for ($i=0; $i<$n2; $i++){

    $minorNewsListHTML .= '<li><p>';
    //if($minorNewsList['player_name'][$i] == $_SESSION['player_name']){
    //    $minorNewsListHTML .= '<span style="background-color:#FFA500;">';
    //}
    $minorNewsListHTML .= $minorNewsList['new_date'][$i].' | <strong>'.$minorNewsList['player_name'][$i].'</strong> | '.$minorNewsList['new_content'][$i];
    //if($minorNewsList['player_name'][$i] == $_SESSION['player_name']){
    //    $minorNewsListHTML .= '</span>';
    //}
    $minorNewsListHTML .= '</p></li>';
    
}