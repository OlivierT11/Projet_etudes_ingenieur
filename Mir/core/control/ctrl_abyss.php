<?php 

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

//get foe lvl
$foeLvl = getFoeLvl('abyss', $database);

/**************/
/* HELL ENTRY */
/**************/

//get the hell id if the player is on the entry
if(!isset($_SESSION['id_hell'])){
    $hellArray = getHellId($database); //returns something when the player is on an area that contains a hell entry
    $_SESSION['id_hell'] = $hellArray['id_hell'];
    $_SESSION['hell_x'] = $hellArray['hell_x']; 
    $_SESSION['hell_y'] = $hellArray['hell_y'];
}

/***************/
/* ABYSS ENTRY */
/***************/
//get abyss entry coord if the player connects inside the abyss

//get the abyss dj if the abyss is opened
if(!isset($_SESSION['abyss_x'])){
    $abyssArray = getAbyssIdWhenOpen($database); 
    $_SESSION['id_abyss'] = $abyssArray['id_abyss'];
    $_SESSION['abyss_x'] = $abyssArray['abyss_x']; 
    $_SESSION['abyss_y'] = $abyssArray['abyss_y'];
}

// Display the player's actions
$actionsListHTML = $action->getAllActions(); 
