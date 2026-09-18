<?php

#region Database calls (REPLACE BY CLASS)

function getMyMysqlConnection()
{
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";

    try {
        $mysql = new PDO("mysql:host=$servername;dbname=demo;charset=utf8", $username, $password);
        // set the PDO error mode to exception
        $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //echo "Connected successfully";
        return $mysql;
        }
    catch(PDOException $e)
        {
        echo "Connection failed: " . $e->getMessage();
        }
}

/*
// new function, totest
function getMyMysqlConnection()
{
    try 
    {
        $PDOstring = 'mysql:host='.MYSQL_SERVER_MIR.';dbname='.MYSQL_DATABASE_MIR.';charset=utf8';
        $mysql = new PDO($PDOstring, MYSQL_USERNAME_MIR, MYSQL_PASSWORD_MIR);

        // set the PDO error mode to exception
        $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e)
    {
          
    }
    return $mysql;
}

*/

// function getMyElkarteConnection()
// {
//     $servername = "127.0.0.1";
//     $username = "root";
//     $password = "";

//     try {
//         $mysql = new PDO("mysql:host=$servername;dbname=elkarte;charset=utf8", $username, $password);
//         // set the PDO error mode to exception
//         $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//         //echo "Connected successfully";
//         return $mysql;
//         }
//     catch(PDOException $e)
//         {
//         echo "Connection failed: " . $e->getMessage();
//         }
// }

#endregion


function getLifeBars()
{
    //select player and city data on connection
    $query='SELECT player_food, player_morale, player_shield, player_head, player_mask, player_upper, player_lower
        FROM player 
        WHERE id_player='.$_SESSION['id_player'];
    try {
        $res=$database->mysql->query($query);
            if ($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $_SESSION['player_food'] = $result['player_food'];
                    $_SESSION['player_morale'] = $result['player_morale'];
                    $_SESSION['player_shield'] = $result['player_shield'];
                    $_SESSION['player_head'] = $result['player_head'];
                    $_SESSION['player_mask'] = $result['player_mask'];
                    $_SESSION['player_upper'] = $result['player_upper'];
                    $_SESSION['player_lower'] = $result['player_lower'];
                }
            } else {
                $error = "Error : life bars introuvables";
            }
    } catch (PDOException $e){
        $_SESSION['mir_debug']=$e->getMessage()."\n";
        return false;
    }
}


#region Get side illustrations

function getSideImageLeft($area, $subarea='', $era='', $daytime=''){

    // If red or dark era and outside, display era illustration no matter the area
    if($era == 'red'){
        if($area == 'inside' || $area =='outside' || $area == 'camp'){
            return '<img src="www/img/side/era-red-side-left.png">';
        }
    }
    else if($era == 'dark'){
        if($area == 'inside' || $area =='outside' || $area == 'camp'){
            return '<img src="www/img/side/era-dark-side-left.png">';
        }
    }
    //select the correct side images to display
    switch($area){
        //areas
        case ($area == 'inside'):
            return '<img src="www/img/side/city-side-left-2.png">';
        case ($area == 'outside' && $subarea == 'forest'):
            return '<img src="www/img/side/forest-side-left.png">';
        case ($area == 'outside' && $subarea == 'plain'):
            return '<img src="www/img/side/plain-side-left.png">';
        case ($area == 'outside' && $subarea == 'forestcenter'):
            return '<img src="www/img/side/forestcenter-side-left.png">';
        case ($area == 'dj'):
            return '<img src="www/img/side/dj-side-left.png">';
        case ($area == 'dj2'):
            return '<img src="www/img/side/dj-side-left.png">';
        case ($area == 'abyss'):
            return '<img src="www/img/side/dj-side-left.png">';
        case ($area == 'hell'):
            return '<img src="www/img/side/hell-side-left.png">';

        //camps
        case ($area == 'camp' && $subarea == 'forest'):
            return '<img src="www/img/side/camp-forest-side-left.png">';
        case ($area == 'camp' && $subarea == 'plain'):
            return '<img src="www/img/side/camp-plain-side-left.png">';
        case ($area == 'camp' && $subarea == 'dj'):
            return '<img src="www/img/side/camp-dj-side-left.png">';
        case ($area == 'camp' && $subarea == 'dj2'):
            return '<img src="www/img/side/camp-dj2-side-left.png">';
        case ($area == 'camp' && $subarea == 'abyss'):
            return '<img src="www/img/side/camp-abyss-side-left.png">';
        case ($area == 'camp' && $subarea == 'hell'):
            return '<img src="www/img/side/hell-side-left.png">';

        //home and other menus
        case ($area == 'home'):
            return '<img src="www/img/side/home-side-left.png">';
        case ($area == 'intro'):
            return '<img src="www/img/side/intro-side-left.png">';
        default:
    }

}


function getSideImageRight($area, $subarea='', $era='', $daytime=''){

    // If red or dark era and outside, display era illustration no matter the area
    if($era == 'red'){
        if($area == 'inside' || $area =='outside' || $area == 'camp'){
            return '<img src="www/img/side/era-red-side-right.png">';
        }
    }
    else if($era == 'dark'){
        if($area == 'inside' || $area =='outside' || $area == 'camp'){
            return '<img src="www/img/side/era-dark-side-right.png">';
        }
    }
    //select the correct side images to display
    switch($area){
        //areas
        case ($area == 'inside'):
            return '<img src="www/img/side/city-side-right-2.png">';
        case ($area == 'outside' && $subarea == 'forest'):
            return '<img src="www/img/side/forest-side-right.png">';
        case ($area == 'outside' && $subarea == 'plain'):
            return '<img src="www/img/side/plain-side-right.png">';
        case ($area == 'outside' && $subarea == 'forestcenter'):
            return '<img src="www/img/side/forestcenter-side-left.png">';
        case ($area == 'dj'):
            return '<img src="www/img/side/dj-side-right.png">';
        case ($area == 'dj2'):
            return '<img src="www/img/side/dj-side-right.png">';
        case ($area == 'abyss'):
            return '<img src="www/img/side/dj-side-right.png">';
        case ($area == 'hell'):
            return '<img src="www/img/side/hell-side-right.png">';

        //camps
        case ($area == 'camp' && $subarea == 'forest'):
            return '<img src="www/img/side/camp-forest-side-right.png">';
        case ($area == 'camp' && $subarea == 'plain'):
            return '<img src="www/img/side/camp-plain-side-right.png">';
        case ($area == 'camp' && $subarea == 'dj'):
            return '<img src="www/img/side/camp-dj-side-right.png">';
        case ($area == 'camp' && $subarea == 'dj2'):
            return '<img src="www/img/side/camp-dj2-side-right.png">';
        case ($area == 'camp' && $subarea == 'abyss'):
            return '<img src="www/img/side/camp-abyss-side-right.png">';
        case ($area == 'camp' && $subarea == 'hell'):
            return '<img src="www/img/side/hell-side-right.png">';

        //home and other menus
        case ($area == 'home'):
            return '<img src="www/img/side/home-side-right.png">';
        case ($area == 'intro'):
            return '<img src="www/img/side/intro-side-right.png">';
        default:

    }
    
}

#endregion


#region Update food and morale values

// Update the food to the defined value
function updateFoodValue($database, int $value=-2){

    //$value = -2; = déplacement. Créer une var globale.

    // Update session and checks if the value is valid
    $_SESSION['player-food-bar'] += $value;
    if($_SESSION['player-food-bar'] > 100)
    {
        $_SESSION['player-food-bar'] = 100;
    }

    $query='UPDATE player_life_bar plb
    SET plb.player_food = '.$_SESSION['player-food-bar'].'
    WHERE plb.id_player='.$_SESSION['id_player'];
    try {
        $database->mysql->query($query);
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

}

// Update the morale to the defined value
function updateMoraleValue($database, $value=-2){

    #region Compute new morale value

    #region Gain morale if allies around

    // Check if there are other players around the player. If yes, change the morale value.
    if($_SESSION['player_area'] != 'inside'){
        $ally = checkAlliesAround($database);
        
        switch($ally){
            // Regain de 2 moral par case si entre 1 et 5 alliés à proximité.
            case ($ally >= 1 && $ally < 5):
                $value = 2;
            break;
            // Si 5 alliés ou + : 10 moral par déplacement.
            case ($ally >= 5):
                $value = 10;
            break;
            default:
            //Valeur donnée en paramètre
        }
    }
    #endregion

    #region Lose morale if ennemies around
    #endregion

    #region Lose morale based on era
    #endregion

    #region gain morale in city
    // To merger
    #endregion

    // Update session and checks if the value is valid
    $_SESSION['player-morale-bar'] += $value;
    if($_SESSION['player-morale-bar'] > 100)
    {
        $_SESSION['player-morale-bar'] = 100;
    }

    #endregion

    #region Update morale value in DB

    $query='UPDATE player_life_bar plb
    SET plb.player_morale = '.$_SESSION['player-morale-bar'].'
    WHERE plb.id_player='.$_SESSION['id_player'];
    try {
        $database->mysql->query($query);
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    #endregion

}

// Check the number of allies around the player (9 areas)
function checkAlliesAround($database){

    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];
    $query="SELECT COUNT(*) as allyCount
            FROM player p
            WHERE p.id_city=".$_SESSION['id_city']." AND p.player_area = '".$_SESSION['player_area']."'";
    $query.= 'AND (p.player_pos_x BETWEEN '.($x-1).' AND '.($x+1).') AND (p.player_pos_y BETWEEN '.($y-1).' AND '.($y+1).')';

    try {
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['count'] = $data['allyCount'] - 1; // Ne prend pas en compte le joueur lui-même 
            }
        }
    } catch (PDOException $e){
        echo 'error in query mod_players.php 1: ' . $e->getMessage();
        return false;
    }

    return $result['count'];
}

#endregion


#region Destroy city

// Called from defeat or victory screen when the city's timer exceeds the time limit.
function destroyCity($database, $log){

    /*

        When the city is destroyed, remove everything about the city from the database, except the row in the CITY table and the city's statistics

    */

    $idPlayer = $_SESSION['id_player'];
    $idCity = $_SESSION['id_city'];

    // First, check if the city has already been destroyed by another player.
    // TODO : remplacer par un test sur has_ended
    $query="SELECT player_nbr FROM city 
                WHERE id_city=".$idCity;
    $result='';
    try
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            $data=$res->fetch(PDO::FETCH_ASSOC);
            $result = (int)$data['player_nbr'];  
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // If there is still someone inside the city, delete the city data and the players' data for all the players
    if($result != 0 && $result != ''){
        
        // Reset the city's player nbr
        $query="UPDATE city SET player_nbr = 0, has_ended = 1
                    WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // reinitialize the players' stats
        $query="UPDATE player_stat ps 
                    JOIN player p ON ps.id_player = p.id_player
                    SET ps.player_stat_lvl = 1, ps.player_stat_xp = 0
                    WHERE p.id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // reinitialize the players' local statistics
        $query="UPDATE statistic_local_player slp
                    JOIN player p ON slp.id_player = p.id_player
                    SET slp.amount = 0
                    WHERE p.id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete items for the city
        $query="DELETE FROM city_item 
                WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Reset players' life bars
        $query="UPDATE player_life_bar plb
                    JOIN player p ON plb.id_player = p.id_player
                    SET plb.player_food = 100, plb.player_morale = 100
                    WHERE p.id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Reset players' mute (exemple de delete avec jointure)
        $query="DELETE player_mute FROM player_mute
                    JOIN player  ON player_mute.id_player = player.id_player
                    WHERE player.id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete farm items for the city
        $query="DELETE FROM city_farm 
                    WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //reinitialize the player vote
        $query="UPDATE player_vote pv
                    JOIN player p ON pv.id_player = p.id_player
                    SET pv.id_vote = 0, pv.vote_type = ''
                    WHERE p.id_city = ".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete buildings
        $query="DELETE FROM building_city
                    WHERE id_city = ".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete chatbox messages
        $query="DELETE FROM chatbox_msg
                    WHERE id_city = ".$idCity;
        try 
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete city actions
        $query="DELETE FROM city_action
                    WHERE id_city = ".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete city camps
        $query="DELETE FROM city_camp
                    WHERE id_city = ".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete city news
        $query="DELETE FROM city_new
                    WHERE id_city = ".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete city cron
        $query="DELETE FROM cron_city
                    WHERE id_city = ".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete map, dj, dj2, abyss, hell
        $query="DELETE FROM map 
                    WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        $query="DELETE FROM dj 
                    WHERE id_dj=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
        $query="DELETE FROM dj2 
                    WHERE id_dj2=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        $query="DELETE FROM abyss 
                    WHERE id_abyss=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        $query="DELETE FROM hell 
                    WHERE id_hell=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Delete the links between areas for the city
        $query="DELETE FROM abyss_city 
                    WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        $query="DELETE FROM abyss_hell 
                    WHERE id_hell=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        $query="DELETE FROM dj_dj2 
                    WHERE id_dj=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        $query="DELETE FROM map_dj 
                    WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Change the players' city and area
        // AT THE VERY END, as the player table is used in JOIN
        $query="UPDATE player 
                    SET id_city=0, player_area='home'
                    WHERE id_city=".$idCity;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
        
    }


}

#endregion


// Compute foe lvl to display over the map. Also computed in battle.php. Merge the two ?
function getFoeLvl($area, $database){

    // Une ville dure 10 jours.
    $maxCityDay = 10;

    // Une abysse a 18 cases de profondeurs
    $abyssDeepness = 18;

    switch($area){
        case 'outside':
            return floor( sqrt( pow($_SESSION['player_pos_x'],2) + pow($_SESSION['player_pos_y'], 2) ) );
        break;
        case 'dj':
            // selectionne les coordonnées de l'entrée du donjon sur la carte
            $query="SELECT m.x, m.y 
            FROM map m INNER JOIN map_dj md ON m.id = md.id_area
            WHERE id_dj=".$_SESSION['id_dj'];
            $result=[];
            try {
                $res = $database->mysql->query($query);
                if($res) {
                    while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                        $result['x'] = $data['x'];
                        $result['y'] = $data['y'];
                    }
                }
            } catch (PDOException $e){
                echo $e->getMessage();
                return false;
            }
            // Le niveau du monstre dépend de la distance à la capitale ET de la distance à l'entrée du DJ
            return floor( sqrt( pow($_SESSION['player_pos_x']-$result['x'],2) + pow($_SESSION['player_pos_y']-$result['y'], 2) ) );

            // Tous les monstres du DJ ont le même niveau
            //return floor( sqrt( pow($result['x'],2) + pow($result['y'], 2) ) );
        break;
        case 'abyss':
            // détermine la valeur max possible de la statistique d'attaque des joueurs à l'ouverture de l'abysse (4 jours avant la fin)
            $maxStat = 10 + ($maxCityDay - 4) * 5;
            return $maxStat;
        break;
        case 'hell':
            $maxStat = 10 + ($maxCityDay - 4) * 5;
            return $maxStat + $abyssDeepness;
        break;
    }

}