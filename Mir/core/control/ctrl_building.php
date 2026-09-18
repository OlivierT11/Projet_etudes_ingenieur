<?php

$buildingList = getBuildingList($database);

$buildingListHTML='';

//builds the building table
foreach ($buildingList as $bld) {

    //barre la ligne si non disponible
    if ($bld['is_available'] == 0 && $bld['is_built'] == 0){
        $buildingListHTML .= '<tr style="background-color:#e5712d;">';
    } else if ($bld['is_built'] == 1){
        $buildingListHTML .= '<tr style="background-color:orange;">';
    } else { //available = 1, done = 0
        $buildingListHTML .= '<tr>';
    }

    $buildingListHTML .=
    '<td>'.$bld['bld_cat'].' &nbsp; </td>
    <td>'.$bld['bld_name'].' &nbsp; </td>
    <td>'.$bld['bld_descr'].' &nbsp; </td>';

    //affiche les votes si bâtiment dispo
    if ($bld['is_available'] == 0 && $bld['is_built'] == 0){
        $buildingListHTML .= 
        '<td align="center">Indisponible</td>
        <td></td>
        </tr>'; 
    } else if ($bld['is_built'] == 1){
        $buildingListHTML .= 
        '<td align="center">Construit</td>
        <td></td>
        </tr>'; 
    } else { //available = 1, done = 0
        $buildingListHTML .= 
        '<td align="center">
            <button id="'.$bld['id_bld'].'" class="btn-vote-bld btn btn-light btn-sm">Voter</button>
        </td>
        <td  align="center">'.$bld['bld_city_vote'].' votes &nbsp;</td>
        </tr>'; 
    }
}

// get metal and rare wood available in city stocks
$woodNbr = getRareWood($database);
$metalNbr = getMetal($database);
