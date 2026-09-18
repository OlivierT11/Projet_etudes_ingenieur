<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

#region Inventory

/**
 * 
 * Loads and format the inventories of the page.
 * The loading is made from session, or DB is no session exists.
 * 
 */

// Initialize object. The parent class Database is also charged w/ its constructor
$invent = new Invent();

// Gets every object on the page depending on the parameters and gives them a unique HTML id.
$invent->getItemsList(true, true, false);

// Invent player
$inventPlayer = $invent->inventPlayer;
$_SESSION['invent-size'] = $invent->playerInventSize;

// Invent city
$inventCity = $invent->inventCity;

// Create the session $listID array for all invents
$_SESSION['invent-list-id'] = $invent->inventsList;

$counterCityItems = $invent->inventCitySize;

#endregion

#region Building Effects
    
/// OLD : on a enlevé la hard limit de taille de stock de ville. On considère que la soft limit de perte de durabilité suffit

// Get the max invent size for city, base on building.
// Si seul le 1er niveau de stock est construit (par défault)
// if(in_array(6, $_SESSION['id_bld']) && !in_array(7, $_SESSION['id_bld']))
// {
//     $maxInventCity = 1000;
// }
// // Si le 2nd niveau de stock est construit
// else if(in_array(7, $_SESSION['id_bld']) && !in_array(8, $_SESSION['id_bld']))
// {
//     $maxInventCity = 2000;
// }
// // Si le 3e niveau est construit
// else if(in_array(8, $_SESSION['id_bld']))
// {
//     $maxInventCity = 5000;
// }
// else // défaut, si erreur.
// {
//     $maxInventCity = 1000;
// }

#endregion