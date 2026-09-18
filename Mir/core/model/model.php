<?php

if(!isset($_SESSION))
	session_start();

require_once dirname(dirname(__FILE__))."/config/config.php"; // PATHs à partir de model.php
require_once dirname(dirname(__FILE__))."/tools/tools.php";

// Database class
require_once dirname(dirname(__FILE__))."/class/class.database.php";
/*
if(!isset($database))
{
	$database = new Database();
}*/
// Logging class
require_once dirname(dirname(__FILE__))."/class/class.log.php";
/*
if(!isset($log))
{
	$log = new Log();
}*/

require_once dirname(dirname(__FILE__))."/class/class.invent.php";
require_once dirname(dirname(__FILE__))."/class/class.model.php";
require_once dirname(dirname(__FILE__))."/class/class.player.php";
require_once dirname(dirname(__FILE__))."/class/class.map.php";

// I18N class
require_once dirname(dirname(__FILE__))."/class/class.i18n.php";

if(!isset($i18n))
{
	// Default language. Not a constant as it will be changed later.
	// The class will test it to select the main language (if no forced language is set).
	$_SESSION['lang'] = 'fr'; 

	$langPath = dirname(dirname(__FILE__)).'/i18n/lang/lang_{LANGUAGE}.ini';
	$langCachePath = dirname(dirname(__FILE__)).'/i18n/langcache/';

	$i18n = new i18n($langPath, $langCachePath, 'fr');
	$i18n->init();
}

// Constantes
require_once dirname(dirname(__FILE__))."/class/class.constants.php";

// Caching class
require_once dirname(dirname(__FILE__))."/class/class.cache.php";
/*
if(!isset($cache) && isset($_SESSION['id_city']))
{
	$cache = new Cache($_SESSION['id_city']);
}
*/
// News class
require_once dirname(dirname(__FILE__))."/class/class.news.php";
/*
if(!isset($news))
{
	$news = new News($database, $log);
}*/

// Actions class
require_once dirname(dirname(__FILE__))."/class/class.action.php";
/*
if(!isset($action))
{
	$action = new Action($database, $log);
}*/

// Classes loaded from inside a city only
//if(isset($_SESSION['id_city']) && $_SESSION['id_city'] != 0)
//{
	// Stats class
	require_once dirname(dirname(__FILE__))."/class/class.stat.php";
/*
	if(!isset($stat))
	{
		$stat = new Stat($database, $log, $action);
	}
*/
	// Statistic class
	require_once dirname(dirname(__FILE__))."/class/class.statistic.php";
/*
	if(!isset($statistic))
	{
		$statistic = new Statistic($database, $log, $news);
	}*/
//}

require_once dirname(dirname(__FILE__))."/class/class.city.php";

require_once dirname(dirname(__FILE__))."/class/class.tools.php";

if(isset($_SESSION['player_is_logged_in']) && $_SESSION['player_is_logged_in'] == 1){ //for every file that uses sessions defined during login to account.
	require_once dirname(dirname(__FILE__))."/cron/cron.php";
}

// Unset the battle sessions, because every page change during a fight is considered a flee.
if(isset($_SESSION['posFoeInString']))
	unset($_SESSION['posFoeInString']);

// Reset the minimap if the player changes page or reloads.
if(isset($_SESSION['minimap_max_x'])){
	unset($_SESSION['minimap_max_x']);
    unset($_SESSION['minimap_min_x']);
    unset($_SESSION['minimap_max_y']);
    unset($_SESSION['minimap_min_y']);
}


// get current timezone : date_default_timezone_get(); (Europe/Paris)
// timezone set for remote servers : date_default_timezone_set('Europe/Paris');

//$_SESSION['date_st']=microtime(); // To compute the pagr loading time ?
if(!isset($_GET['page'])) {
	$_GET['page']="";
}
$model = new Model;
$model->getPage($_GET['page']);
//cookie management
