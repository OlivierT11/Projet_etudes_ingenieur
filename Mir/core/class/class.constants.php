<?php

class Constants
{
    // Login
    static $google_signin_client_id = "660525557493-mqr1178abgs6vuee0vk5ubf40b7ba8tl.apps.googleusercontent.com";
    // static $baseUrl = "https://d74f6b64652b.ngrok.io/gitRepoMir/gitRepoMir/Mir/index.php?page=login";
    // static $baseUrl = "https://d74f6b64652b.ngrok.io/gitRepoMir/gitRepoMir/Mir/core/control/ctrl_login.php";
    
    // Database  
    static $MYSQL_DATABASE_MIR = 'demo';
    static $MYSQL_SERVER_MIR = '127.0.0.1';
    static $MYSQL_USERNAME_MIR = 'root';
    static $MYSQL_PASSWORD_MIR = '';

    // Building
    static $rareWoodPerBuilding = 50;
    static $rareMetalPerBuilding = 50;

    // City
    static $maxCityDays = 10;
    static $maxPlayersPerCity = 20;

    // Player
    static $numberOfStats = 12;

    // Maze
    static $maze_dim_x = 50;
    static $maze_dim_z = 50;

    // Forum
    static $forum_max_message_length = 1000;
    static $forum_max_nbr_of_choices_per_survey = 9;

}