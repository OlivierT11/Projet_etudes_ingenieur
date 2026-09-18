<?php 

#region Inventory

/**
 * 
 * Loads and format the inventories of the page.
 * The loading is made from session, or DB is no session exists.
 * 
 */

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


/******************/
/* RED / DARK era */
/******************/

// Changes the color of certain elements based on the era.
switch($_SESSION['era']){
    case '':
        $canvasMapColorClass = 'mapColor';
    break;
    case 'red':
        $canvasMapColorClass = 'mapRedColor';
    break;
    case 'dark':
        $canvasMapColorClass = 'mapDarkColor';
    break;
    default:
}

/********/
/* CAMP */
/********/

$_SESSION['id_camp'] = getIdCamp($database);

/************/
/* DJ ENTRY */
/************/

$djArray = getIdDjWhenOnEntry($database); //returns something when the player is on an area that contains a DJ
$_SESSION['id_dj'] = $djArray['id_dj'];
$_SESSION['dj_x'] = $djArray['dj_x'];
$_SESSION['dj_y'] = $djArray['dj_y'];


/**************** */
/* GET BIOME TYPE */
/**************** */

//to display illustrations based on biome
$_SESSION['player_sub_area'] = getBiomeType($database);

/********/
/* Foe */
/********/

//to display button fight
// getFoeData[] pour renvoyer plusieurs données. (à faire de base)
$foeNbr = getFoeData($database);

//get foe lvl
$foeLvl = getFoeLvl('outside', $database);

// Display the player's actions
$actionsListHTML = $action->getAllActions();
