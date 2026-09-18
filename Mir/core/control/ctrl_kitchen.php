<?php 
//session_start();

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

#endregion

/************** */
/* ITEM HISTORY */
/************** */

//$invent = new Invent(); //meme inventaire que inventplayer ?
$itemsList = $invent->getItemsHistory();
$itemsHistory = $invent->formatItemsHistory($itemsList);

// Display the player's actions
$actionsListHTML = $action->getAllActions(); 
