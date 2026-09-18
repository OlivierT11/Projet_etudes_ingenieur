<?php
//include("../../control/ctrl_head.php"); 
include(dirname(dirname(dirname(__FILE__)))."/model/mod_head.php");
include(dirname(dirname(dirname(__FILE__)))."/control/ctrl_head.php");

//include("../../model/mod_head.php"); 
?><!DOCTYPE html>


<html lang="en">

<head>
	<meta charset="UTF-8"/>
	<title> Mïr </title>
	<!-- OLD <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"> -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="www/css/general_UI.css">
    <link rel="stylesheet" type="text/css" href="www/css/map.css">
    <link rel="stylesheet" type="text/css" href="www/css/forum.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
</head>

<body>
	<div id="left-img">
        <?php 
            echo getSideImageLeft($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); 
        ?>
    </div>
    
	<div id="center-div">
        <!-- <div class="navbar-wrapper"> -->
            <nav class="navbar navbar-expand-sm bg-dark navbar-dark justify-content-between">
                <!-- Brand -->

                <a class="navbar-brand" id="btn-toggle-left-menu">Menu</a>

                <?php
                    // Accueil redirect the user to the current area page, or login.php
                    if(isset($_SESSION['player_area']))
                    {
                        echo '<a class="navbar-brand" href="index.php?page='.$_SESSION['player_area'].'">Accueil</a>';
                    }
                    else
                    {
                        echo '<a class="navbar-brand" href="index.php?page=login">Accueil</a>';
                    }
                ?>

                <ul class="navbar-nav">
                    <!-- <li class="nav-item">
                        This item is a little hacked to align with the other links 
                        <a class="navbar-brand" href="index.php?page=forum" style="margin-top:-2px;">Forum</a>&nbsp&nbsp&nbsp&nbsp&nbsp
                    </li> -->
                    <li class="nav-item dropdown">
                        <a class="navbar-brand dropdown-toggle" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="margin-top:-2px;">
                        Forums
                        </a>&nbsp&nbsp&nbsp&nbsp&nbsp
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <?php if(isset($_SESSION['id_city']) && $_SESSION['id_city'] != 0)
                                {
                                    echo '<a class="dropdown-item" href="index.php?page=forum&isGlobal=0">Forum de la Citadelle</a>';
                                }
                            ?>
                            <a class="dropdown-item" href="index.php?page=forum&isGlobal=1">Forum Global</a>
                        </div>
                    </li>
                    <li class="nav-item">
                        <!-- This item is a little hacked to align with the other links -->
                        <a class="navbar-brand" href="index.php?page=advice" style="margin-top:-2px;">Aide</a>&nbsp&nbsp&nbsp&nbsp&nbsp
                    </li>
                    <!-- Display the current day, only when a game has started-->
                    <?php if(isset($_SESSION['id_city']) && $_SESSION['id_city'] != 0){

                        // the city has just been created, so no currday is set yet.
                        if(!isset($_SESSION['currday'])){
                            $_SESSION['currday'] = 1;
                        }
                        
                    echo '<li class="nav-item">
                            <a class="nav-link disabled" href="#" style="color:orange; font-weight:bold;">Jour ' . $_SESSION['currday'] . ' <span id="navbar-currtime">(' . date("H:i") . ')</span></a>
                        </li>
                        <!--
                        <li class="nav-item">
                            <a class="nav-link disabled" href="#" style="color:orange; font-weight:bold;" title="Augmente la qualité des objets si vous les obtenez quand d\'autres joueurs sont connectés en même temps que vous à l\'heure du ralliement.">Ralliement (20:00 - 21:00h)</a>
                        </li>
                        -->';
                    }
                    ?>

                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=account">Compte</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=statistic">Stats</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?page=login&action=logout">Déconnexion</a>
                    </li>
                </ul>
                
            </nav> 
        <!-- </div> -->
