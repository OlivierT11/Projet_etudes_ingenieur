<?php

// If the player is already connected, redirect to where he is in the game
if (isset($_SESSION['id_player']))
{
    header("location: index.php?page=".$_SESSION['player_area']);
}

#region File inclusions

require_once 'vendor/autoload.php';
require_once dirname(dirname(__FILE__))."/class/class.account.php";

$database = new Database();
$log = new Log();

//$cache = new Cache($_SESSION['id_city']);

#endregion