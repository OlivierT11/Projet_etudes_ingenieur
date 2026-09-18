<?php
if(!isset($_SESSION))
session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    die();
}

/**
 * 
 * This file contains :
 *  Code to join an already existing city.
 *  Code to create then join a city if none is free.
 *  Code to leave a city.
 * 
 */

 #region Classes

//loadCoreClassesForAjaxFiles(); //database, log, stat etc

require_once dirname(dirname(__FILE__))."/config/config.php";

require_once dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

require_once dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

require_once dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

require_once dirname(dirname(__FILE__))."/class/class.map.php";
require_once dirname(dirname(__FILE__))."/tools/tools.php";
require_once dirname(dirname(__FILE__))."/class/class.city.php";
$city = new City($database, $log, $news);

#endregion

/********************* */
// Rejoindre une ville */
/********************* */

// Join an available
if(isset($_POST['action']) && $_POST['action'] == 'joinCity')
{
    $database->mysql->beginTransaction();

    $idCity = htmlspecialchars(trim($_POST['idCity']));

    $city->addPlayerToCity($idCity);

    // Finally update the session to prevent retour arrière from inside to intro
    $_SESSION['page'] = 'intro';

    $database->mysql->commit();
}

/***************** */
/* Créer une ville */
/***************** */

// Join an available
if(isset($_POST['action']) && $_POST['action'] == 'createCity')
{
    $database->mysql->beginTransaction();

    $city->createNewCity();
    $city->addPlayerToCity($city->newIdCity);

    // Finally update the session to prevent retour arrière from inside to intro
    $_SESSION['page'] = 'intro';

    $database->mysql->commit();
}

/******************* */
/* Quitter une ville */
/******************* */

if(isset($_POST['action']) && $_POST['action'] == 'leaveCity')
{
    $database->mysql->beginTransaction();

    $city->removePlayerFromCity();

    // Reinit the session w/out disconnect
    $_SESSION['id_city'] = 0;
    $_SESSION['player_area'] = 'home';
    $_SESSION['player_sub_area'] = '';
    $_SESSION['player_pos_x'] = 0;
    $_SESSION['player_pos_y'] = 0;
    $_SESSION['is_fighting'] = 0;
    $_SESSION['player_stat'] = [];
    $_SESSION['max-invent'] = 0;
    $_SESSION['is_master'] = 0;
    $_SESSION['master_stat'] = '';
    $_SESSION['city_name'] = '';
    $_SESSION['city_img_path'] = '';
    $_SESSION['is_new'] = 0;
    $_SESSION['id_dj'] = 0;
    $_SESSION['id_dj2'] = 0;
    $_SESSION['id_abyss'] = 0;
    $_SESSION['id_hell'] = 0;

    $database->mysql->commit();
}