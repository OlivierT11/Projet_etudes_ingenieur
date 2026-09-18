<?php

$newsList = getNewsList($database, $log);

$newsListHTML='';

// Build the news table
foreach ($newsList as $new) 
{
    $newsListHTML .= '<p>'.$new['new_date'].' | <span style="color:red; font-weight:bold;">'.$new['new_type'].'</span> | '.$new['new_descr'];
    $newsListHTML .= ' | <a href="'.$new['new_link'].'">Lien forum</a>';
}

// Build the join city bloc
$citiesList = $city->getAvailableCitiesList();

$citiesListHTML = '';
foreach ($citiesList as $city) 
{
    // check if the flag exists (has been player voted)
    if (file_exists($city['city_img_path']))
    {
        $img = '<img class="join-city-flag-img" src="' . $city['city_img_path'] . '"';
    }
    else
    {
        $img = "";
    }

    // Build HTML
    $citiesListHTML .= '<tr class="available-city-to-join">';
    $citiesListHTML .=  '<td class="join-city-flag-td"><div class="join-city-flag-div">'.$img.'</div></td>';
    $citiesListHTML .=  '<td style="font-weight:bold;">'.$city['city_name'].'</td>';
    $citiesListHTML .=  '<td>Jour '.$city['currday'].'</td>';
    $citiesListHTML .=  '<td><span style="font-weight:bold;">'.$city['player_nbr'].'</span>/'.Constants::$maxPlayersPerCity.' joueurs</td>';
    $citiesListHTML .=  '<td><button type="button" class="join-city-btn btn btn-success btn-sm" id='.$city['id_city'].'>Rejoindre</button></td>';
    $citiesListHTML .= '</tr>';
}
