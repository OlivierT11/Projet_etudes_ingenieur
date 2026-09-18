<?php
ini_set('display_errors','on');
error_reporting(E_ALL);

// server should keep session data for AT LEAST 1 hour
ini_set('session.gc_maxlifetime', 3600);

#region Database

define('MYSQL_DATABASE_MIR', 'demo');
define('MYSQL_SERVER_MIR', '127.0.0.1');
define('MYSQL_USERNAME_MIR', 'root');
define('MYSQL_PASSWORD_MIR', '');

define('MYSQL_DATABASE_MIR_GCLOUD', 'demo');
define('MYSQL_SERVER_MIR_GCLOUD', '34.78.105.82');
define('MYSQL_USERNAME_MIR_GCLOUD', 'root');
define('MYSQL_PASSWORD_MIR_GCLOUD', 'test');

#endregion

#region XP gain values

define('XP_DISCOVER_AREA', 20);
define('XP_STEALTH', 10);
define('XP_SUPPLY', 20);
define('XP_ATK', 20);
define('XP_DEF', 20);
define('XP_FORGE_SHIELD', 20);
define('XP_FORGE_UP', 20);
define('XP_FORGE_HEAD', 20);
define('XP_FORGE_PIKE', 20);
define('XP_COOK_ORANGE', 20);
define('XP_COOK_HONEY', 20);
define('XP_WOOD_CUTTING', 20);
define('XP_FARM', 20);

#endregion

// Une ville dure X jours.
define('MAX_CITY_DAYS', 10);

// Une abysse a X cases de profondeurs
define('ABYSS_DEEPNESS', 18);

// Nombre max de joueurs par ville
define('MAX_CITY_PLAYERS', 20);



//define('__ROOT__', dirname(dirname(dirname(__FILE__))));  //mir, à mettre dans config

/*
require_once '../core/lib/fmCommon/prepend.inc.php';  
define("RELROOT", dirname($_SERVER['SCRIPT_NAME']));
define("WEBROOT",realpath(__dir__."/.."));

define('USER_MD_COLLABORATOR_CODE', _MD_COLLABORATOR_CODE);
define('USER_MD_COLLABORATOR_ID', _MD_COLLABORATOR_ID);
define('USER_MD_INT_FNAME', _MD_INT_FNAME);
define('USER_MD_INT_NAME', _MD_INT_NAME);
define('USER_MD_PRO_EMAIL', _MD_PRO_EMAIL);
define('USER_MD_LANGUAGE_CODE', _MD_LANGUAGE_CODE);
define('USER_MD_ACCOUNT_ID', _MD_ACCOUNT_ID);
define('USER_MD_ACCOUNT_CODE', _MD_ACCOUNT_CODE);
define('USER_MD_ACCOUNT_LOGIN', _MD_ACCOUNT_LOGIN);

define('SERVER_NAME', _SERVER_NAME);
define('SERVER_URL', _SERVER_URL);
define('SERVER_PROFIL_TYPE', _SERVER_PROFIL_TYPE);

define('MYSQL_SERVER', _MYSQL_SERVER);
define('MYSQL_LOGIN', _DEFAULT_MYSQL_LOGIN);
define('MYSQL_PASSWORD', _DEFAULT_MYSQL_PASSWORD);
define('MYSQL_DATABASE', 'DIF_fm_yoda');

define('WS_WSDL_URL', _WS_WSDL_URL);

define('RT_USER','nqi');
define('RT_PASS','SavGekPin7');
define('RT_URL','http://rt.fmlogistic.fr');
*/
