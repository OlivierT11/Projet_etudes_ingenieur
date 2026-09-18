<?php 

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}
// Prevent backtab from abyss
if($_SESSION['player_area'] != 'dj2'){
    header("Location:index.php?page=".$_SESSION['player_area']);
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
$invent->getItemsList(true, false, true);

// Invent player
$inventPlayer = $invent->inventPlayer;
$_SESSION['invent-size'] = $invent->playerInventSize;

// Invent city
$inventCity = $invent->inventCity;

// Equipment
$shieldEquiped = $invent->shieldEquiped;
$upperEquiped = $invent->upperEquiped;
$lowerEquiped = $invent->lowerEquiped;
$helmetEquiped = $invent->helmetEquiped;
$maskEquiped = $invent->maskEquiped;
$spearEquiped = $invent->spearEquiped;

// Create the session $listID array for all invents
$_SESSION['invent-list-id'] = $invent->inventsList;

#endregion


/********/
/* CAMP */
/********/

$_SESSION['id_camp'] = getIdCamp($database);


/********/
/* Foe */
/********/

//for fight button display (ctrl_life_bars)
$foeNbr = getFoeNbr($database);


/**************/
/* HELL ENTRY */
/**************/

//Entry always at (0,0)
