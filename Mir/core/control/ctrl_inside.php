<?php

// met le moral à 100 si le joueur entre dans la ville.
if(isset($_SESSION['player-morale-bar']) && $_SESSION['player-morale-bar'] != 100){
    updateMoraleValue($database, 100);
}

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

// If there is no new (city start)
if(isset($minorNewsList['new_date']))
{
    $n2 = sizeof($minorNewsList['new_date']); //stored in the format $var = date('H:i');
}
else
{
    $n2 = 0;
}

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

/***************/
/* ABYSS ENTRY */
/***************/

//get the abyss dj if the abyss is opened
if($_SESSION['abyss_is_open'])
{
    if(!isset($_SESSION['id_abyss']) && !isset($_SESSION['abyss_x']))
    {
        $abyssArray = getAbyssIdWhenOpen($database); 
        //var_dump($abyssArray);
        $_SESSION['id_abyss'] = $abyssArray['id_abyss'];
        $_SESSION['abyss_x'] = $abyssArray['abyss_x']; 
        $_SESSION['abyss_y'] = $abyssArray['abyss_y'];
    }
}