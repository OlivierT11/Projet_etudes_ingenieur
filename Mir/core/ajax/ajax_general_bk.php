<?php
if(!isset($_SESSION))
session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
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

require_once dirname(dirname(__FILE__))."/class/class.map.php";
require_once dirname(dirname(__FILE__))."/tools/tools.php";

require_once dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

require_once dirname(dirname(__FILE__))."/class/class.cache.php";
$cache = new Cache($_SESSION['id_city']);

require_once dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

require_once dirname(dirname(__FILE__))."/class/class.action.php";
$action = new Action($database, $log);

require_once dirname(dirname(__FILE__))."/class/class.stat.php";
$stat = new Stat($database, $log, $action);

require_once dirname(dirname(__FILE__))."/class/class.statistic.php";
$statistic = new Statistic($database, $log, $news);

#endregion

/********************* */
// Rejoindre une ville */
/********************* */

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'joinCity')
{
    $database->mysql->beginTransaction();

    //testing
    //createNewCity($database, $news, $log);
    
    // Check the player has previously left the city, to make him go to another one.
    $query="SELECT prev_id_city FROM account
                WHERE id_player=".$_SESSION['id_player'];
    try
    {
        $res=$database->mysql->query($query);
        if($res)
        {
            $data=$res->fetch(PDO::FETCH_ASSOC);
            $prevIdCity = $data['prev_id_city'];
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //check for free city, other than the previously selected
    //TODO also check for timer
    $query="SELECT id_city FROM city
                WHERE (player_nbr < ".MAX_CITY_PLAYERS.") AND id_city<>".$prevIdCity." AND has_ended = 0 LIMIT 1";
    try 
    {
        $res=$database->mysql->query($query);
        if($res->rowCount() > 0)
        {
            $data=$res->fetch(PDO::FETCH_ASSOC);
            $idCity = $data['id_city'];
            addPlayerToCity($database, $news, $idCity);
        } 
        else
        {
            // No city is available
            createNewCity($database, $news, $log);
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Finally update the session to prevent retour arrière from inside to intro
    $_SESSION['page'] = 'intro';

    $database->mysql->commit();

}

function addPlayerToCity($database, $news, $idCity)
{
    $idPlayer = $_SESSION['id_player'];

    //update player(id_city)
    $query="UPDATE player SET id_city=".$idCity.", player_area='inside'
                WHERE id_player=".$idPlayer;
    try 
    {
        $database->mysql->query($query);
        $_SESSION['id_city'] = $idCity;
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //update city(player_nbr)
    $query="UPDATE city SET player_nbr = player_nbr + 1
                WHERE id_city=".$idCity;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // reinitialize the player stats
    $query="UPDATE player_stat SET player_stat_lvl = 1, player_stat_xp = 0
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // reinitialize the player's local statistics
    $query="UPDATE statistic_local_player SET amount = 0
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // initialize the player's inventory with basic items
    $query="INSERT INTO city_item(id_item, id_city, id_player, item_pos) 
            VALUES
                (1, ".$idCity.", ".$idPlayer.", 'invent'),
                (3, ".$idCity.", ".$idPlayer.", 'invent'),
                (4, ".$idCity.", ".$idPlayer.", 'invent'),
                (5, ".$idCity.", ".$idPlayer.", 'invent'),
                (6, ".$idCity.", ".$idPlayer.", 'invent'),
                (7, ".$idCity.", ".$idPlayer.", 'invent'),
                (8, ".$idCity.", ".$idPlayer.", 'invent'),
                (9, ".$idCity.", ".$idPlayer.", 'invent'),
                (10, ".$idCity.", ".$idPlayer.", 'invent')";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //delete farm items for the player
    $query="UPDATE city_farm SET is_alive = 0
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //initialize the player life bars 
    $query="UPDATE player_life_bar SET player_food = 100, player_morale=100
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    } 
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //reinitialize the player vote
    $query="UPDATE player_vote SET id_vote = 0, vote_type = ''
                WHERE id_player = ".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Load the sessions that should norally be loaded on login. Here the player gets into a city without logging in so the sessions must be reloaded.
    
    // get effects of built buildings
    $_SESSION['id_bld'] = [];

    $query="SELECT id_bld FROM building_city WHERE id_city=".$_SESSION['id_city']." AND is_built=1";
    $result=[];
    try
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            while($data=$res->fetch(PDO::FETCH_ASSOC))
            {
                $_SESSION['id_bld'][] = $data['id_bld'];
            }
        }
    } 
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Get player stats
    

    // Finally add the new player in the news section
    $news->addNew("newPlayer", $_SESSION['player_name'], 1, $idCity, $idPlayer);
}

/***************** */
// Creer une ville */
/***************** */

// TODO 
// 1. Création DJ, map et abysse depuis fichier en PHP et non en front
// 2. Multiple DJ par ville.

function createNewCity($database, $news, $log)
{
    // define the start date (13 digits) and round it to the previous midnight.
    //$date = microtime(true);
    //$dateStart = (int)$date*1000;

    //TODO test à la création de la ville.
    $dateTest = time(); // nombre de secondes depuis janvier 1 1970 0:00
    $dateTest = $dateTest - ($dateTest % (60*60*24)); // nbre de secondes, jours compris MOINS nombre de secondes, jours non compris = nombre de jour arrondi à minuit.
    $dateTest *= 1000; // seconds to milliseconds, to fit the database lecture in cron.php (13 digits). Maybe use time() everywhere ?

    // create new city in DB and get the last inserted ID to avoid congruent requests
    $query = 'INSERT INTO city(player_nbr, `start_date`) VALUES(0, '.$dateTest.')';
    try
    {
        $database->mysql->query($query);
        $newIdCity = $database->mysql->lastInsertId();
        $newIdDj = $newIdCity; // 1 dj par ville pour l'instant. Après il faudra une table avec id_dj en clé primaire pour avoir lastInsertId.
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Create a new cron for the city
    $query = 'INSERT INTO cron_city(id_city, date_begin, currday) VALUES('.$newIdCity.', '.$dateTest.', 1)';
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #region Create map

    //map (image) storage in DB
        
    $x = $_REQUEST['xMap'];
    $y = $_REQUEST['yMap'];
    $r = $_REQUEST['rMap'];
    $g = $_REQUEST['gMap'];
    $b = $_REQUEST['bMap'];
    $a = $_REQUEST['aMap'];
    
    $x = explode(",", $x);
    $y = explode(",", $y);
    $r = explode(",", $r);
    $g = explode(",", $g);
    $b = explode(",", $b);
    $a = explode(",", $a);

    $n = sizeof($x)-1;

    //convert the arrays of strings into arrays of integers
    for ($i=0; $i<$n; $i++){
        $x[$i]=(int)$x[$i];
        $y[$i]=(int)$y[$i];
        $r[$i]=(int)$r[$i];
        $g[$i]=(int)$g[$i];
        $b[$i]=(int)$b[$i];
        $a[$i]=(int)$a[$i];
    }
    

    // Décalage d'indices pour avoir (0,0) au centre de la carte
    /*
    Before : 
        (0,0)   ...   (25,0)  ... (49,0) 
        ...
        (0,25)  ...   (25,25) ... (49,25) 
        ...
        (0,49)  ...   (25,49) ... (49,49) 


    After :
        (-25,25)   ...   (0,25)  ... (25,25) 
        ...             
        (-25,0)    ...   (0,0)   ... (25,0) 
        ...
        (-25,-25)  ...   (0,-25) ... (25,-25) 
    */

    $maxCityX = 99; //to have -24 -> 25
    $maxCityY = 100; //to have 25 -> -24
    $newY = floor($maxCityY / 2);
    $compteurPosY = 0;
    // Pour tout x : x - floor(49/2) = x - 24.
    for($i=0;$i<$n;$i++){
        $x[$i] -= floor($maxCityX/2);
    }
    // Pour tout y : toutes les suites de 49 cases prennent le même y. Diminue de 1 à la 49e case.
    for($i=0;$i<$n;$i++){
        $y[$i] = $newY;
        if($compteurPosY == $maxCityY){
            $newY -=1;
            $compteurPosY=0;
        }
        $compteurPosY++; // when it reaches 49, the next Y will have an offset $newY reduced by 1.
    }

    // Set area type and gate as a function of area color
    $area_type = [];

    for ($i=0; $i<$n; $i++){
        if ($r[$i]=="182" && $g[$i]=="255" && $b[$i]=="0"){
            $area_type[$i]="field"; //OR $area_type[]="field";
        }
        else if ($r[$i]=="76" && $g[$i]=="255" && $b[$i]=="0"){
            $area_type[$i]="forest_1";
        }
        else if ($r[$i]=="38" && $g[$i]=="127" && $b[$i]=="0"){
            $area_type[$i]="forest_2";
        }
        else if ($r[$i]=="255" && $g[$i]=="0" && $b[$i]=="0"){
            $area_type[$i]="forest_2";
        }
    }
    
    //create random content selon la zone
    for ($i=0; $i<$n; $i++){
        $content[$i]='';
        $contentString = '';
        $is_discovered[$i] = 0;

        /// Plaine arbres 5-20%, rock : 0-5% orange 0-3% honey 0-3%, rare tree 1/10000 or 1 every 100 areas
        if($area_type[$i]=="field"){
            for($j=0; $j<100; $j++){
                $m = rand(1,10000);
                if ($m>=500 && $m<2000){
                    $contentString .= 't';
                }
                else if ($m >= 2000 && $m<2500){
                    $contentString .= 'r';
                }
                else if ($m >= 2500 && $m <2800){
                    $contentString .= 'o';
                }
                else if ($m >= 2800 && $m <3100){
                    $contentString .= 'h';
                }
                else if ($m == 9999){ //rare tree
                    $contentString .= 'u';
                }
                else {
                    $contentString .= 'n';
                }
            }
        }

            /// Foret arbres 50-70 rock 0-5 orange 0-3 honey 0-3 rare tree 1/1000 or 1/10 areas.
        if($area_type[$i]=="forest_1"){
            for($j=0; $j<100; $j++){
                $m = rand(1,10000);
                if ($m>=100 && $m<7000){
                    $contentString .= 't';
                }
                else if ($m >= 7000 && $m <7500){
                    $contentString .= 'r';
                }
                else if ($m >= 7500 && $m <7800){
                    $contentString .= 'o';
                }
                else if ($m >= 7800 && $m <8100){
                    $contentString .= 'h';
                }
                else if ($m >= 9990){ //rare tree
                    $contentString .= 'u';
                }
                else {
                    $contentString .= 'n';
                }
            }
        }

            /// Foret 2 arbres 25-40 arbres rares 40% rock 0-2 orange 0 honey 0
        if($area_type[$i]=="forest_2"){
            for($j=0; $j<100; $j++){
                $m = rand(1,10000);
                if ($m>=100 && $m<4000){
                    $contentString . 't';
                }
                else if ($m>=4000 && $m<9000){
                    $contentString . 'u';
                }
                else if ($m >= 9000 && $m <9200){
                    $contentString . 'r';
                }
                else {
                    $contentString . 'n';
                }
            }
        }

        /*
        // Montagne arbres 0-5 rocks 50-70 orange 0-2 honey 0-2
        if($area_type[$i]=="mountain"){
            for($j=0; $j<100; $j++){
                $n = rand(1,100);
                if ($n>=1 && $n<6){
                    $content[$i] . 't';
                }
                else if ($n >= 6 && $n <64){
                    $content[$i] . 'r';
                }
                else if ($n >= 64 && $n <66){
                    $content[$i] . 'o';
                }
                else if ($n >= 66 && $n <68){
                    $content[$i] . 'h';
                }
                else {
                    $content[$i] . 'n';
                }
            }
        }
        */

        $content[$i] = $contentString;

    }
    // populate foes on map
    $foe_hp = [];
    $max_foe = [];
    
    for ($i=0; $i<$n; $i++){

        $foe[$i]=0;
        $foe_hp[$i]='';
        $max_foe[$i]=0;

        //40% empty, 30% 1 foe, 20% 2 foes, 10% 3 foes.
        $m = rand(1,100);
        switch($m){
            case ($m <= 40):
                break;
            case ($m > 40 && $m <= 70):
                $foe[$i] = 1;
                break;
            case ($m > 70 && $m <= 90):
                $foe[$i] = 2;
                break;
            default:
                $foe[$i] = 3;
        }

        $foeNbr = $foe[$i];
        for($j=0; $j<$foeNbr; $j++){
            $foe_hp[$i].='A';
        }
        
        $max_foe[$i]=$foe[$i];
    }

    

        // Populate Hordes and Titans

        /*
        // TODO Do not add foe on (0,0) (city) and Set (0,0) to discovered
        for ($i=0; $i<$n; $i++){
            if(x[$i]==0 && y[$i]==0){
                //x and y have the same index as the area is a square
                $foe[$i] = 0;
                $max_foe[$i] = 0;
                $foe_hp[$i] = '';
                $is_discovered[$i] = 1;
            }
        }
        */


    //create new map
    $query="insert into map (id_city, x, y, r, g, b, content, foe, max_foe, foe_hp, area_type) values ";
    for ($i=0; $i<$n; $i++)
    {
        $query.="(".$newIdCity.", ".$x[$i].", ".$y[$i].", ".$r[$i].", ".$g[$i].", ".$b[$i].", '".$content[$i]."', ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."', '".$area_type[$i]."'),";
    }
    $query = substr($query, 0, -1);
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    /*********************** */
    /* Adds stuff to the map */
    /*********************** */

    // Remove the foes on city entry (0,0)
    $query="UPDATE map SET foe=0, max_foe=0, foe_hp='' 
            WHERE x=0 AND y=0 AND id_city=".$newIdCity;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Define the DJ entry (OUTSIDE dj). It is the only red dot on map.
    $djEntryXout = 0;
    $djEntryYout = 0;
    for ($i=0; $i<$n; $i++){
        if ($r[$i]==255 && $g[$i]==0 && $b[$i]==0){
            $djEntryXout = $x[$i];
            $djEntryYout = $y[$i];
        }
    }
    // Select the id area of defined X and Y
    $query="SELECT id FROM map WHERE id_city=".$newIdCity." AND x=".$djEntryXout." AND y=".$djEntryYout;
    $res = $database->mysql->query($query);

    try
    {
        $res->execute();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    $row = $res->fetchAll(PDO::FETCH_OBJ);
    $id = $row[0]->id;

    // Select the id_area where the color is RED
    /* !!! DONE IN DJ
    $query="SELECT id FROM map 
            WHERE id_city=1 AND r=255 AND g=0 AND b=0
            LIMIT 1";
    try {
        $idRed = 0;
        $res=$database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $idRed = $data['id'];
            }
        }
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin select RED area " . $e->getMessage();
    }

    // Add a DJ entry where the area is RED
    $query="INSERT INTO map_dj(id_city, id_dj, id_dj_area, id_area, dj_x, dj_y) VALUE (1, 1, 1, ".$idRed.", 1, 1)";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin set DJ entry " . $e->getMessage();
    }
    */

    // Create walls on map limits by setting the content value of limit areas to '0'.
    $limitLeftX = -49;
    $limitRightX = 50;
    $limitUpY = 50;
    $limitDownY = -49;
    $query="UPDATE map SET content='' 
            WHERE x=".$limitLeftX." OR x=".$limitRightX." OR y=".$limitUpY." OR y=".$limitDownY;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #endregion

    #region Create Dungeon

    //TODO AMEL MULTIPLE DJ ON MAP

    $x = $_REQUEST['xMaze'];
    $y = $_REQUEST['yMaze'];
    $up = $_REQUEST['up'];
    $right = $_REQUEST['right'];
    $down = $_REQUEST['down'];
    $left = $_REQUEST['left'];

    $x = explode(",", $x);
    $y = explode(",", $y);
    $up = explode(",", $up);
    $right = explode(",", $right);
    $down = explode(",", $down);
    $left = explode(",", $left);
    
    $n = sizeof($x)-1;


    // TODO
    //adds empty areas around the maze with walls to deal w/ the map limit problem. So the real size is 21*21
    // -> Plus compliqué, non nécessaire. Pour l'instant, ne pas charger les 9 cases si elles n'existent pas. De toutes façons on ne mettra pas les monstres sur
    // les side map.

    // ...


    // Move the center from top-right to middle
    /*
    Before : 
        (0,0)   ...   (25,0)  ... (49,0) 
        ...
        (0,25)  ...   (25,25) ... (49,25) 
        ...
        (0,49)  ...   (25,49) ... (49,49) 


    After :
        (-25,25)   ...   (0,25)  ... (25,25) 
        ...             
        (-25,0)    ...   (0,0)   ... (25,0) 
        ...
        (-25,-25)  ...   (0,-25) ... (25,-25) 
    */

    $maxMazeX = 19; //to have -19 -> 20
    $maxMazeY = 20; //to have 20 -> -19
    $newY = floor($maxMazeY / 2);
    $compteurPosY = 0;
    // Pour tout x : x - floor(20/2) = x - 10.
    for($i=0;$i<$n;$i++){
        $x[$i] -= floor($maxMazeX/2);
    }
    // Pour tout y : toutes les suites de 20 cases prennent le même y. Diminue de 1 à la 20e case.
    for($i=0;$i<$n;$i++){
        $y[$i] = $newY;
        if($compteurPosY == $maxMazeY-1){ // the -1 is because $maxmazeY is 20 and Y goes from 0 to 19.
            $newY -=1;
            $compteurPosY=0;
        }
        $compteurPosY++; // when it reaches 20, the next Y will have an offset $newY reduced by 1.
    }

    // populate foes in dj, 10 foes everywhere
    $foe_hp = [];
    $max_foe = [];
    
    for ($i=0; $i<$n; $i++){

        $foe[$i]=0;
        $foe_hp[$i]='';
        $max_foe[$i]=0;

        //10 foes everywhere
        $foe[$i] = 10;
        
        $foeNbr = $foe[$i];
        for($j=0; $j<$foeNbr; $j++){
            $foe_hp[$i].='A';
        }
        
        $max_foe[$i]=$foe[$i];
    }

    
    //create dj in table
    $query="INSERT INTO dj(id_city, id_dj, dj_x, dj_y, dj_u, dj_r, dj_d, dj_l, dj_foe, dj_max_foe, dj_foe_hp) VALUES ";
    for ($i=0; $i<$n; $i++)
    {
        $query.="(".$newIdCity.", ".$newIdDj.", ".$x[$i].", ".$y[$i].", ".$up[$i].", ".$right[$i].",".$down[$i].", ".$left[$i].", ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."'),";
    
        // Defines the DJ entry coordinates in the dj (the blue pixel)
        //if($r[$i] == 0 && $g[$i] == 0 && $b[$i] == 255)
        //{
        //    $djEntryXin = $x[$i];
        //    $djEntryYin = $y[$i];
        //}

    }
    //kill the last coma
    $query = substr($query, 0, -1);
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // set the dj entry coordinates on the map
    $djEntryXin = -8; // or add a blue dot when randomly generating the dj and uncomment the previous code.
    $djEntryYin =  8;
    $query="INSERT INTO map_dj(id_city, id_dj, id_area, dj_x, dj_y) VALUE(".$newIdCity.", ".$newIdDj.", ".$id.", ".$djEntryXin.", ".$djEntryYin.")";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
    
    // set the dj exit to dj2
    $djEntryDj2X = 8; // or add a red dot when randomly generating the dj
    $djEntryDj2Y = -8;
    $query="INSERT INTO dj_dj2(id_dj, id_dj2, dj2_x, dj2_y) VALUE(".$newIdDj.", ".$newIdDj.", ".$djEntryDj2X.", ".$djEntryDj2Y.")";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Remove the foes on dj entry
    $query="UPDATE dj SET dj_foe=0, dj_max_foe=0, dj_foe_hp='' 
            WHERE dj_x=".$djEntryXin." AND dj_y=".$djEntryYin." AND id_city=".$newIdCity;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #endregion

    #region Create Abyss

    // Difference with map : only X position switch.

    $x = $_REQUEST['xAbyss'];
    $y = $_REQUEST['yAbyss'];
    $r = $_REQUEST['rAbyss'];
    $g = $_REQUEST['gAbyss'];
    $b = $_REQUEST['bAbyss'];
    //$a = $_REQUEST['aAbyss'];
    
    $x = explode(",", $x);
    $y = explode(",", $y);
    $r = explode(",", $r);
    $g = explode(",", $g);
    $b = explode(",", $b);
    //$a = explode(",", $a);
    
    $n = sizeof($x)-1;

    // Change indexes
    $maxAbyssX = 19; //to have -19 -> 20
    $newY = floor($maxAbyssX / 2);
    $compteurPosY = 0;
    // Pour tout x : x - floor(20/2) = x - 10.
    for($i=0;$i<$n;$i++){
        $x[$i] -= floor($maxAbyssX/2);
    }

    // populate foes in abyss, 20 foes everywhere
    $foe_hp = [];
    $max_foe = [];
    
    for ($i=0; $i<$n; $i++){

        $foe[$i]=0;
        $foe_hp[$i]='';
        $max_foe[$i]=0;

        //20 foes everywhere
        $foe[$i] = 20;
        
        $foeNbr = $foe[$i];
        for($j=0; $j<$foeNbr; $j++){
            $foe_hp[$i].='A';
        }
        
        $max_foe[$i]=$foe[$i];
    }

    $query="INSERT INTO abyss(id_abyss, abyss_x, abyss_y, abyss_r, abyss_g, abyss_b, abyss_foe, abyss_max_foe, abyss_foe_hp) VALUES ";
    for ($i=0; $i<$n; $i++){
        $query.="(".$newIdCity.", ".$x[$i].", ".$y[$i].", ".$r[$i].", ".$g[$i].", ".$b[$i].", ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."'),";

        //Find which pixel is the entrance (the blue pixel)
        if($r[$i] == 0 && $g[$i] == 0 && $b[$i] == 255)
        {
            $abyssEntry_x = $x[$i];
            $abyssEntry_y = $y[$i];
        }

        //Find which pixel is the hell entry (the red pixel)
        else if($r[$i] == 255 && $g[$i] == 0 && $b[$i] == 0)
        {
            $hellEntry_x = $x[$i];
            $hellEntry_y = $y[$i];
        }
    }
    //kill the last coma
    $query = substr($query, 0, -1);
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //Définir l'entrée de l'abysse. C'est la 1ere case bleue.
    $query="INSERT INTO abyss_city(id_city,id_abyss,abyss_x,abyss_y) VALUE(".$newIdCity.", ".$newIdCity.", ".$abyssEntry_x.", ".$abyssEntry_y.")";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
    $sth->execute();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //Définir l'entrée de l'enfer. C'est la premiere case rouge.
    /*
    $query="SELECT abyss_x, abyss_y 
                FROM abyss 
                WHERE id_abyss=".$newIdCity." AND abyss_r = 255 AND abyss_g = 0 AND abyss_b = 0
                ORDER BY abyss_y DESC, abyss_x ASC
                LIMIT 1";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();
    $row = $sth->fetchAll(PDO::FETCH_OBJ);
    $hell_x = $row[0]->abyss_x;
    $hell_y = $row[0]->abyss_y;
    */

    // Adds the hell entry coordinates in the abyss.
    $query="INSERT INTO abyss_hell(id_abyss,id_hell,abyss_x,abyss_y) VALUE(".$newIdCity.", ".$newIdCity.", ".$hellEntry_x.", ".$hellEntry_y.")";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #endregion

    #region Create Dj2

    // To build all 6 buildings, 300 rare woods are needed, so 35/area for 9 areas. Same goes for metal. Put a little more (40) in case someone's inventory is full.
    
    // Create random content (always 40 trees)
    //create random content selon la zone
    for ($i=0; $i<9; $i++){

        $content[$i]='';

        for($j=0; $j<100; $j++){
            $m = rand(1,100);
            if ($m>=1 && $m<=40){
                $content[$i] .= 'u';
            }
            else {
                $content[$i] .= 'n';
            }
        }
        
    }
    
    $newIdDj2 = $newIdCity;
    $query="INSERT INTO dj2 (id_dj2, dj2_x, dj2_y, dj2_content, dj2_foe, dj2_max_foe, dj2_foe_hp) 
            VALUES
                (".$newIdDj2.", -2, -2, '', 0,  0, ''),
                (".$newIdDj2.", -2, -1, '', 0,  0, ''),
                (".$newIdDj2.", -2, 0, '', 0,  0, ''),
                (".$newIdDj2.", -2, 0, '', 0,  0, ''),
                (".$newIdDj2.", -2, 2, '', 0,  0, ''),
                (".$newIdDj2.", -1, -2, '', 0,  0, ''),
                (".$newIdDj2.", -1, -1, '".$content[0]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", -1, 0, '".$content[1]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", -1, 1, '".$content[2]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", -1, 2, '', 0,  0, ''),
                (".$newIdDj2.", 0, -2, '', 0,  0, ''),
                (".$newIdDj2.", 0, -1, '".$content[3]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", 0, 0, '".$content[4]."', 0,  0, ''),
                (".$newIdDj2.", 0, 1, '".$content[5]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", 0, 2, '', 0,  0, ''),
                (".$newIdDj2.", 1, -2, '', 0,  0, ''),
                (".$newIdDj2.", 1, -1, '".$content[6]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", 1, 0, '".$content[7]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", 1, 1, '".$content[8]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdDj2.", 1, 2, '', 0,  0, ''),
                (".$newIdDj2.", 2, -2, '', 0,  0, ''),
                (".$newIdDj2.", 2, -1, '', 0,  0, ''),
                (".$newIdDj2.", 2, 0, '', 0,  0, ''),
                (".$newIdDj2.", 2, 1, '', 0,  0, ''),
                (".$newIdDj2.", 2, 2, '', 0,  0, '')";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #endregion

    #region Create Hell
    
    //create hell
    $query="INSERT INTO hell (id_hell, hell_x, hell_y, hell_content, hell_foe, hell_max_foe, hell_foe_hp) 
            VALUES
                (".$newIdCity.", -2, -2, '0', 0,  0, ''),
                (".$newIdCity.", -2, -1, '0', 0,  0, ''),
                (".$newIdCity.", -2, 0, '0', 0,  0, ''),
                (".$newIdCity.", -2, 0, '0', 0,  0, ''),
                (".$newIdCity.", -2, 2, '0', 0,  0, ''),
                (".$newIdCity.", -1, -2, '0', 0,  0, ''),
                (".$newIdCity.", -1, -1, '0', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", -1, 0, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", -1, 1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", -1, 2, '0', 0,  0, ''),
                (".$newIdCity.", 0, -2, '0', 0,  0, ''),
                (".$newIdCity.", 0, -1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", 0, 0, '', 0,  0, ''),
                (".$newIdCity.", 0, 1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", 0, 2, '0', 0,  0, ''),
                (".$newIdCity.", 1, -2, '0', 0,  0, ''),
                (".$newIdCity.", 1, -1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", 1, 0, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", 1, 1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                (".$newIdCity.", 1, 2, '0', 0,  0, ''),
                (".$newIdCity.", 2, -2, '0', 0,  0, ''),
                (".$newIdCity.", 2, -1, '0', 0,  0, ''),
                (".$newIdCity.", 2, 0, '0', 0,  0, ''),
                (".$newIdCity.", 2, 1, '0', 0,  0, ''),
                (".$newIdCity.", 2, 2, '0', 0,  0, '')";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #endregion

    // put a camp at hell (0,0)
    $query="INSERT INTO city_camp(id_city, camp_pos_x, camp_pos_y, camp_area) 
            VALUE(".$newIdCity.", 0, 0, 'hell')";
    try 
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // TODO Delete the monsters on DJ / Abyss entries.

    // Initialize the buildings
    $query="INSERT INTO building_city(id_bld, id_city, is_available, is_built, bld_city_vote) 
            VALUES (3, ".$newIdCity.", 1, 0, 0),
                   (4, ".$newIdCity.", 0, 0, 0),
                   (5, ".$newIdCity.", 0, 0, 0),
                   (6, ".$newIdCity.", 1, 0, 0),
                   (7, ".$newIdCity.", 0, 0, 0),
                   (8, ".$newIdCity.", 0, 0, 0),
                   (9, ".$newIdCity.", 0, 0, 0),
                   (10, ".$newIdCity.", 1, 0, 0),
                   (11, ".$newIdCity.", 0, 0, 0),
                   (12, ".$newIdCity.", 0, 0, 0),
                   (13, ".$newIdCity.", 1, 0, 0),  
                   (14, ".$newIdCity.", 0, 0, 0),   
                   (15, ".$newIdCity.", 1, 0, 0)";   
    try 
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }


    /******** */
    /* OTHERS */
    /******** */

    // add news of the city creation
    $news->addNew('newCity', 0, 1, $newIdCity, $_SESSION['id_player']);

    // finally put the player into the new city
    addPlayerToCity($database, $news, $newIdCity);

    // TODO Create CRON for the new city (datebegin, currday)
}



/******************* */
/* Quitter une ville */
/******************* */

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'leaveCity') {

    $database->mysql->beginTransaction();

    removePlayerFromCity($database, $news);

    $database->mysql->commit();

}

function removePlayerFromCity($database, $news){

    $idPlayer = $_SESSION['id_player'];

    // Keep the previous city in mind
    $idCity = $_SESSION['id_city'];

    // Change the player's city and area
    $query="UPDATE player SET id_city=0, id_area=0, player_pos_x=0, player_pos_y=0, id_dj=0, id_abyss=0, id_camp=0, is_fighting=0, prev_dir='0', player_thumbs=0, player_area='home'
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
    
    // Remove 1 player from the city
    $query="UPDATE city SET player_nbr = player_nbr - 1
                WHERE id_city=".$idCity;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // reinitialize the player stats
    $query="UPDATE player_stat SET player_stat_lvl = 1, player_stat_xp = 0
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // reinitialize the player's local statistics
    $query="UPDATE statistic_local_player SET amount = 0
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Rendre les objets de l'inventaire à la ville
    $query="UPDATE city_item SET item_pos='bank' 
            WHERE id_player=".$idPlayer." AND is_alive=1 AND item_pos IN ('invent','shield','upper','lower','mask','helmet','spear')";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Les objets détruits sont inacessibles par les autres joueurs, et seront détruits à la fin de la ville.

    //delete farm items for the player
    $query="UPDATE city_farm SET is_alive = 0
                WHERE id_player=".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //reinitialize the player vote
    $query="UPDATE player_vote SET id_vote = 0, vote_type = ''
                WHERE id_player = ".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Empêcher le joueur de rejoindre la ville qu'il vient de quitter
    $query="UPDATE account SET prev_id_city = ".$idCity."
                WHERE id_player = ".$idPlayer;
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Finally add the new
    $news->addNew("playerLeave", $_SESSION['player_name'], 1);

    // Unset sessions
    $_SESSION['id_city'] = 0;
    $_SESSION['era'] = '';
    $_SESSION['player_area'] = 'home';

}


/*********** */
/* Chatboxes */
/*********** */

// Check the Show / Hide state of the chatboxes from session (false by default)
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'check-cb') {
    
    $_SESSION['is_cb_hidden'] == 1 ? ($state = '25px') : ($state = '280px');
    echo $state;

}
// Keep in memory the Show / Hide state of the chatboxes.
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'hide-cb') {
    
    // Toggle the CB display state
    $_SESSION['is_cb_hidden'] = $_REQUEST['hidden'];
    echo $_SESSION['is_cb_hidden'];

}