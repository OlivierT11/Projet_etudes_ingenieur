<?php

/**
 * 
 * Handles the cron tasks.
 * Each player checks whether a particular task must be done and does it for everyone else in the city.
 * 
 * LOGIC 
 * Gets currday and compares it with the actual day. If 1 is added, then it's time from cron tasks. Currday should be cached and the file deleted at midnight.
 * 
 * Ever day events : trigger the list of fnctions to fire the daily events
 * Once and for all event (once per day, single day) : trigger lists of fonctions to fire events that are supposed to happen on a particular day.
 */

$database = new Database();
$log = new Log();
$action = new Action($database, $log);
$news = new News($database, $log);

// used in this file
$_SESSION['era'] = '';

// A mettre dans config
$baseMaxLvl = 10;

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

if(!isset($_SESSION['date_begin'])){ //player's 1st connection, avoiding querying on each page
    //get distance between now and city start
    $query='SELECT date_begin, currday
        FROM cron_city
        WHERE id_city='.$_SESSION['id_city'].'
        LIMIT 1';
    try
    {
        $res=$database->mysql->query($query);

        // If the player is inside a city, a date will be found
        if ($res->rowCount() > 0) 
        {
            $data=$res->fetch(PDO::FETCH_ASSOC);
            $dateBegin = $data['date_begin'];
            $currday = $data['currday'];
        }
        // If the player is not inside a city, and just logs in, attribute a default date
        else
        {
            $dateBegin = (int)microtime((true*1000));
            $currday = 0;
        }
        $_SESSION['date_begin'] = $dateBegin;
        $_SESSION['currday'] = $currday;
    } 
    catch (PDOException $e)
    {
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}

// Si on est dans une ville, faire passer les cron de la ville, sinon on sort du fichier
if ($_SESSION['currday'] != 0)
{
    // Check the difference between the start date and current date on each page, and do smth if a new day starts while the player plays.
    $date = microtime(true); //true to get microseconds as float (14 digits)
    $now = (int)$date*1000; // *1000 to get milliseconds; (int) to get int from float and cut the last digit.
    // !!! JS Date().gettime(); returns milliseconds (13 digits)
    // !!! beware, as converting micotime loses some precision.
    $distance = $now - $_SESSION['date_begin']; //13 digits - 13 digits
    //check if we are a day later than the stored version (ie: a user connects for the 1st time of the day)
    $currDayTest = (int)($distance / (24*60*60*1000)); //get the nbr of days past since city start
    $currday = (int)$_SESSION['currday'];
    $updateAllDailyCrons=0;

    //makes sure that the new won't be posted twice to DB
    $postNewWaveOnce=0;
    $postNewRedEraOnce=0;
    $postNewDarkEraOnce=0;
    $postNewAbyssOnce=0;
    $postWaveInTwoDays=0;

    // If the city is dead, kill the city.
    if($currday > 0 && $currDayTest >= 10)
    {
        if (!isset($_SESSION['city_has_ended'])) // prevents infinite redirect loop from page defeat
        {
            $_SESSION['city_has_ended'] = 1;
            header('Location:index.php?page=defeat');
            die();
        }
    }

    if($currDayTest > $currday) //happens once when the 1st player of the day connects.
    { 
        $currday = $currDayTest;
        $_SESSION['currday'] = $currDayTest;

        // TODO TOTEST
        //Update events and add a day but only when the player count is > to 50% of max player nbr (1st day only, on day 2 it starts normally)
        if($currDayTest == 1){ // While the city is D1.

            $playerNbr=0;
            $maxCityPlayers = 10;
            $query='SELECT player_nbr FROM city
                    WHERE id_city = '.$_SESSION['id_city'];
            try {
                $res=$database->mysql->query($query);
                    if ($res) {
                        $data=$res->fetch(PDO::FETCH_ASSOC);
                        $playerNbr = $data['id_city'];
                    }
            } catch (PDOException $e){
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
            if($playerNbr >= $maxCityPlayers/2)
            {
                // daily events
                $updateAllDailyCrons=true;

                //unique events posts
                $postNewWaveOnce=1;
                $postNewRedEraOnce=1;
                $postNewDarkEraOnce=1;
                $postNewAbyssOnce=1;

                // update the new day
                $query='UPDATE cron_city
                            SET currday = '.$currday.'
                            WHERE id_city='.$_SESSION['id_city'];
                try
                {
                    $res=$database->mysql->query($query);
                }
                catch (PDOException $e)
                {
                    $database->mysql->rollBack();
                    $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                    echo "Une erreur est survenue, merci de recharger la page.";
                    die();
                }
            }
            else
            {
                // Tells the players that the city will start tomorrow.
                $news->addNew('city-begin', 0, '', 1);
            }
        
        // If the day is not D1, do cron everyday.
        } else {

            // daily events
            $updateAllDailyCrons=true;

            //unique events posts
            $postNewWaveOnce=1;
            $postNewRedEraOnce=1;
            $postNewDarkEraOnce=1;
            $postNewAbyssOnce=1;
            $postWaveInTwoDays=1;

            // update the new day
            $query='UPDATE cron_city
                        SET currday = '.$currday.'
                        WHERE id_city='.$_SESSION['id_city'];
            try
            {
                $res=$database->mysql->query($query);
            }
            catch(PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
        }

    }

    //max possible lvl for all stats
    $_SESSION['max_possible_lvl'] = $baseMaxLvl + 5*$currday;

    // Define the era and if abyss is open
    $joursMs = 24*60*60*1000;
    $heuresMs = 60*60*1000;

    if(($distance > 2*$joursMs) && ($distance < 1000*$joursMs))
    {
        $_SESSION['abyss_is_open'] = 1;
    }
    else
    {
        $_SESSION['abyss_is_open'] = 0;
    }


    // Define the current Era
    if(($distance >= 6*$joursMs) && ($distance < 8*$joursMs))
    {
        $_SESSION['era'] = 'red';
    }
    else
    {
        $_SESSION['is_red_era'] = 0;
    }

    if($distance >= 8*$joursMs)// && $distance <=5*$joursMs)
    {
        $_SESSION['era'] = 'dark';
    }
    else
    {
        $_SESSION['is_dark_era'] = 0;
    }


    //point in time event (once every day)
    if ($updateAllDailyCrons){
        //foe respauwn (once a day on 1st connection)
        foeRespawn($database, $log);
        //farm items states (once a day)
        updateFarmItems($database, $log);
        //build
        build($database, $log, $news);
        //item dur (once a day). Done after build() to take into account the eventual new buidling effects.
        updateItemsDur($database, $log);
        //destroy items when dur<0
        destroyItems($database, $log);
        // update food and morale
        updateLifeBars($database, $log);
    }

    //once and for all event (once per day, single day)
    //wave
    if($currday == 3){
        if($postWaveInTwoDays==1){ //happens once when the 1st player of the day connects.
            postWaveInTwoDays($database, $log);
        }
    }
    if($currday == 5){
        if($postNewWaveOnce==1){ //happens once when the 1st player of the day connects.
            makeWaveDamageItems($database, $log); //wave dmg to inventories and camps
            makeWaveDestroyCamps($database, $log);
            makeWaveDamagePlayer($database, $action, $log);
            postNewWave($database, $log);
        }
    }
    else if($currday == 6){
        if($postNewRedEraOnce==1){ 
            postNewRedEra($database, $log);
        }
        if($postNewAbyssOnce==1){
            postNewOpenAbyss($database, $log);
        }
    }
    else if($currday == 8){
        if($postNewDarkEraOnce==1){
            postNewDarkEra($database, $log);
        }
    }

} // FIN if ($currday != 0)

$database->mysql->commit();

/*********** */
/* FUNCTIONS */
/*********** */

//post major news

function postNewOpenAbyss($database, $log){

    $newDate = date("H:i");
    $newContent='';
	$newContent .= "Un temblement de terre survint. Toute la citadelle tremble. Une crevasse s''ouvre dans la paroie, laissant sortir un énorme nuage de fumée noire. <span style=''color:red;''>L''abysse</span> est ouverte.";
	
	$query="INSERT INTO city_new(id_city, id_player, new_date, new_content, new_category, is_major)
                VALUES (".$_SESSION['id_city'].", ".$_SESSION['id_player'].", '".$newDate."', '".$newContent."', 'foo', 1)";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}

function postNewWave($database, $log){

	$newDate = date("H:i");
	$newContent = "La <span style=''color:red;''>Vague</span> est passée, endommageant tout ce qui n'est pas protégé. Les objets dans les entrepôts non améliorés subissent des dégâts, et les camps vides (sans objets ni joueurs) sont détruits.";
	
	$query="INSERT INTO city_new(id_city, id_player, new_date, new_content, new_category, is_major)
                VALUES (".$_SESSION['id_city'].", ".$_SESSION['id_player'].", '".$newDate."', '".$newContent."', 'foo', 1)";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}
function postWaveInTwoDays($database, $log){

	$newDate = date("H:i");
	$newContent = "La <span style=''color:red;''>Vague</span> arrivera après-demain. Elle endommagera gravement les objet dans les bâtiments non renforcés.";
	
	$query="INSERT INTO city_new(id_city, id_player, new_date, new_content, new_category, is_major)
                VALUES (".$_SESSION['id_city'].", ".$_SESSION['id_player'].", '".$newDate."', '".$newContent."', 'foo', 1)";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}
function postNewRedEra($database, $log){
	
	$newDate = date("H:i");
    $newContent = "Le ciel est <span style=''color:red;''>rouge</span>. Le soleil est masqué par un brouillard écarlate. Les plantes sont en train de mourir";
	
	$query="INSERT INTO city_new(id_city, id_player, new_date, new_content, new_category, is_major)
                VALUES (".$_SESSION['id_city'].", ".$_SESSION['id_player'].", '".$newDate."', '".$newContent."', 'foo', 1)";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}
function postNewDarkEra($database, $log){
	
	$newDate = date("H:i");
	$newContent = "Le ciel est entièrement noir. On y vois pas à deux mètres.";
	
	$query="INSERT INTO city_new(id_city, id_player, new_date, new_content, new_category, is_major)
                VALUES (".$_SESSION['id_city'].", ".$_SESSION['id_player'].", '".$newDate."', '".$newContent."', 'foo', 1)";
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}

//every item loses 5 hp (50%). Stack with the normal dur loss, for 80% total.
function makeWaveDamageItems($database, $log){

    $qty = 0;
    // Define the durability to lose based on the current building
    if(in_array(10, $_SESSION['id_bld']) && !in_array(11, $_SESSION['id_bld']) && !in_array(12, $_SESSION['id_bld'])){
        $qty = 5;
    }
    // Si le 2nd niveau de l'Atelier est construit
    else if(in_array(11, $_SESSION['id_bld']) && !in_array(12, $_SESSION['id_bld'])){
        $qty = 4;
    }
    // Si le 3e niveau est construit
    else if(in_array(12, $_SESSION['id_bld'])){
        $qty = 3;
    }

    // Attack the items inside the city (bld effects)
    $query='UPDATE city_item
    SET item_dur = item_dur - '.$qty.'
    WHERE id_city='.$_SESSION['id_city'].' AND item_dur>0 AND is_alive=1 AND item_pos="bank"';
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // attack the items outside the city
    $query='UPDATE city_item
    SET item_dur = item_dur - 5
    WHERE id_city='.$_SESSION['id_city'].' AND item_dur>0 AND is_alive=1 AND item_pos<>"bank"';
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

}

// The wave destroys the camp if there is no item nor player in it. 
function makeWaveDestroyCamps($database, $log)
{
    //Amel : ne pas détruire si un joueur est à l'intérieur.
    // Pour 'instant, détruire un camp uniquement sans items, et informer le joueur sur la page d'accueil du camp. Test que si le joueur est dans un camp qui
    // n'existe plus (par ex, à la co), alors l'id camp du joueur est remis à 0.

    $result=[];
    $result['id_city_camp'] = [];
    $result['id_camp'] = [];

    // Selectionner les ids des camps avec items
    $query="SELECT DISTINCT id_camp FROM city_item WHERE id_city=".$_SESSION['id_city']." AND item_pos='bank' AND id_camp<>0";
    
    try
    {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $result['id_camp'][] = $data['id_camp'];
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Selectionner les ids des camps de la ville en extérieur (possibilité de stoquer dans les grottes pour protéger des vagues)
    // meilleure méthode ?
    $query="SELECT id_city_camp FROM city_camp WHERE id_city=".$_SESSION['id_city']." AND camp_area='outside'";
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $result['id_city_camp'][] = $data['id_city_camp'];
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Pour chaque camp de la ville (extérieur), si le camp ne fait pas partie de la liste des camps avec au moins 1 item à l'intérieur, alors le camp est détruit.
    $idCityCampList = '';
    foreach($result['id_city_camp'] as $camp){
        if(!in_array($camp, $result['id_camp'])){
            $idCityCampList .= $camp . ',';
        }
    }
    //cut the last coma
    if($idCityCampList != '')
        $idCityCampList = substr($idCityCampList, 0, -1);

    // Détruire les camps restant
    if(strlen($idCityCampList) > 0){
        $query = "DELETE FROM city_camp WHERE id_city_camp IN (".$idCityCampList.")";
        try {
            $res = $database->mysql->query($query);
        } 
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    }
}

function makeWaveDamagePlayer($database, $action, $log)
{
    $query='UPDATE player_life_bar
        SET player_food -= 50, player_morale -= 50
        WHERE id_player='.$_SESSION['id_player'];
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
    $action->postAction('hitByWave');

    // To set the death reason
    $_SESSION['hit_by_wave'] = true;
}

// Define the amount of foes respawn, according to the current era of the city
function foeRespawn($database, $log){
    
    // On NORMAL era : cut foe and max_foe numbers by 2 if all the monsters have been killed, if not just replenish the number
    // On RED era : 10-20 foes / area
    // On DARK era : hordes (60 ombres) 1 case / 3 -> les récolteurs / crafters peuvent encore servir. Compliqué de se déplacer. Bloqué si ombres > 40 quoi qu'il arrive

    $era = $_SESSION['era'];

    // Update map foes depending on era.
    if($era == ''){

        //map
        $query='UPDATE
                map
            SET
                foe = (CASE 
                    WHEN (foe < max_foe AND foe = 0) THEN (max_foe / 2) 
                    WHEN (foe < max_foe AND foe != 0) THEN max_foe 
                    ELSE foe END)
            WHERE
                id_city ='.$_SESSION['id_city'];
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        /* // update max_foe too (unused)
                ,max_foe = (CASE 
                    WHEN (foe < max_foe AND foe = 0) THEN FLOOR(max_foe / 2) 
                    ELSE max_foe END)
            */ 

    }

    else if ($era == 'red'){

        // Spawn 20 foes, on 1/3 area.
        // map
        $query='UPDATE map SET foe = (CASE WHEN max_foe = 3 THEN 20
                                           WHEN max_foe = 2 THEN 10
                                           WHEN max_foe = 1 THEN 5 
                                           ELSE 0
                                      END)
                WHERE id_city ='.$_SESSION['id_city'];
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    else if ($era == 'dark')
    {
        // Spawn 60 foes, on 1/3 area.
        // map
        $query='UPDATE map SET foe = (CASE WHEN max_foe = 3 THEN 60
                                           WHEN max_foe = 2 THEN 40
                                           WHEN max_foe = 1 THEN 10 
                                           ELSE 10
                                      END)
                WHERE id_city ='.$_SESSION['id_city'];
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    } else {
        echo 'Era unknown.';
    }

    //dj (all era)
    $query='UPDATE dj
    SET
        dj_foe = (CASE
            WHEN (dj_foe < dj_max_foe AND dj_foe = 0) THEN dj_max_foe/2
            WHEN (dj_foe < dj_max_foe AND dj_foe != 0) THEN dj_max_foe
            ELSE dj_foe END),
        dj_max_foe = (CASE
            WHEN (dj_foe < dj_max_foe AND dj_foe = 0) THEN FLOOR(dj_max_foe/2)
            ELSE dj_max_foe END)
        WHERE
            id_dj ='.$_SESSION['id_city'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //abyss (all era)
    $query='UPDATE abyss
    SET
        abyss_foe = (CASE
            WHEN (abyss_foe < abyss_max_foe AND abyss_foe = 0) THEN abyss_max_foe/2
            WHEN (abyss_foe < abyss_max_foe AND abyss_foe != 0) THEN abyss_max_foe
            ELSE abyss_foe END),
        abyss_max_foe = (CASE
            WHEN (abyss_foe < abyss_max_foe AND abyss_foe = 0) THEN FLOOR(abyss_max_foe/2)
            ELSE abyss_max_foe END)
        WHERE
            id_abyss ='.$_SESSION['id_city'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

}

// Every item loses 3 hp (30%) every day
// Takes into account the building bonuses to durability.
function updateItemsDur($database, $log){

    $qty = 0;
    // Define the durability to lose based on the current building
    if(in_array(10, $_SESSION['id_bld']) && !in_array(11, $_SESSION['id_bld']) && !in_array(12, $_SESSION['id_bld'])){
        $qty = 3;
    }
    // Si le 2nd niveau de l'Atelier est construit
    else if(in_array(11, $_SESSION['id_bld']) && !in_array(12, $_SESSION['id_bld'])){
        $qty = 2;
    }
    // Si le 3e niveau est construit
    else if(in_array(12, $_SESSION['id_bld'])){
        $qty = 1;
    }

    // The items outside the city bank do not have the durability bonus.
    // amel : combiner les 2 avec CASE.
    $query='UPDATE city_item ci
    JOIN item i ON i.id_item = ci.id_item
    SET ci.item_dur = ci.item_dur - 3
    WHERE ci.id_city='.$_SESSION['id_city'].' AND ci.item_dur>0 AND ci.is_alive=1 AND ci.item_pos<>"bank" AND i.item_category<>"rare"';
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    // Update the item's durability inside bank according to building lvls
    $query='UPDATE city_item ci
    JOIN item i ON i.id_item = ci.id_item
    SET ci.item_dur = ci.item_dur - '.$qty.'
    WHERE ci.id_city='.$_SESSION['id_city'].' AND ci.item_dur>0 AND ci.is_alive=1 AND ci.item_pos="bank" AND i.item_category<>"rare"';
    try 
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

}

// Destroys broken items
function destroyItems($database, $log){

    $query='UPDATE city_item
        SET is_alive = 0
        WHERE id_city='.$_SESSION['id_city'].' AND item_dur <= 0 AND is_alive=1';
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

}

function updateLifeBars($database, $log){

    // retire 30 faim.
    // retire 30 moral si le joueur n'est pas à l'intérieur.
    $query='UPDATE player_life_bar plb
    JOIN player p ON plb.id_player = p.id_player
    SET plb.player_food = plb.player_food - 30, 
        plb.player_morale = (CASE WHEN p.player_area<>"inside" THEN plb.player_morale - 30 ELSE plb.player_morale END)
    WHERE p.id_city='.$_SESSION['id_city'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

}

//every item in field gets +1 state if it is watered, +0 if not.
function updateFarmItems($database, $log){

    //update the watered items
    $query='UPDATE city_farm
        SET
            id_item = (CASE
                WHEN (id_item=14 AND is_watered = 1) THEN 15
                WHEN (id_item=15 AND is_watered = 1) THEN 16
                WHEN (id_item=17 AND is_watered = 1) THEN 18
                WHEN (id_item=18 AND is_watered = 1) THEN 19
                ELSE id_item
            END)
        WHERE id_city='.$_SESSION['id_city'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    //unwater watered items
    $query='UPDATE city_farm
        SET is_watered = 0
        WHERE is_alive=1 AND is_watered=1 and id_city='.$_SESSION['id_city'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
}

function build($database, $log, $news){

    //check the precious wood resource count
    $wood1=0;
    $query="SELECT COUNT(id_item) 
            FROM city_item
            WHERE id_item=2 AND item_pos='bank' AND is_alive=1 AND id_city=".$_SESSION['id_city']."
            ORDER BY item_pos";
    try
    {
        $wood1 = $database->mysql->query($query)->fetchColumn();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }
    //select metal
    $metal=0;
    $query="SELECT COUNT(id_item) 
            FROM city_item
            WHERE id_item=20 AND item_pos='bank' AND is_alive=1 AND id_city=".$_SESSION['id_city']."
            ORDER BY item_pos";
    try
    {
        $metal = $database->mysql->query($query)->fetchColumn();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    if($wood1 >= 50 && $metal >= 50){
        $count = 1;
        if($wood1 >= 100 && $metal >= 100 ){$count=2;}
        if($wood1 >= 150 && $metal >= 150 ){$count=3;}
        if($wood1 >= 200 && $metal >= 200 ){$count=4;}
        if($wood1 >= 250 && $metal >= 250 ){$count=5;}
        if($wood1 >= 300 && $metal >= 300 ){$count=6;}
        if($wood1 >= 350 && $metal >= 350 ){$count=7;}

        //select the building with the most votes, with the name to add the new
        $query='SELECT bc.id_bld, b.bld_name
            FROM building_city bc INNER JOIN building b ON b.id_bld = bc.id_bld
            WHERE bc.id_city='.$_SESSION['id_city'].'
            ORDER BY bc.bld_city_vote DESC
            LIMIT '.$count;
        try
        {
            $res=$database->mysql->query($query);
                if ($res) {
                    while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                        $result['id_bld'][] = $data['id_bld'];
                        $result['bld_name'][] = $data['bld_name'];
                    }
                }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //build the building(s)
        $queryParam='(';
        for($i=0; $i<$count; $i++){
            $queryParam .= $result['id_bld'][$i].',';
        }
        $queryParam = substr_replace($queryParam ,"", -1);
        $queryParam .= ')';

        $query='UPDATE building_city
            SET is_available=0, is_built=1
            WHERE id_city='.$_SESSION['id_city'].' AND id_bld IN '.$queryParam; 
            //return $query;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //reset all the player votes for this building (player table)
        $query="UPDATE player_vote
            SET id_vote=0
            WHERE id_vote IN ".$queryParam." AND vote_type='bld'";
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //reset all the player votes for this building (building table)
        $query="UPDATE building_city
            SET bld_city_vote=0
            WHERE id_bld IN ".$queryParam;
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //remove the wood
        $wood1_to_destroy = $count * 50;
        $query='DELETE FROM city_item 
                    WHERE id_item=2 AND id_city='.$_SESSION['id_city'].' AND item_pos="bank" AND id_camp=0
                    LIMIT '.$wood1_to_destroy;
        /*
         $query='UPDATE city_item SET is_alive=0
                    WHERE id_item=2 AND id_city='.$_SESSION['id_city'].'
                    LIMIT '.$wood1_to_destroy;
                    */
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
        //remove the metal
        $metal_to_destroy = $count * 50;
        $query='DELETE FROM city_item 
                    WHERE id_item=20 AND id_city='.$_SESSION['id_city'].' AND item_pos="bank" AND id_camp=0
                    LIMIT '.$metal_to_destroy;
        /*$query='UPDATE city_item SET is_alive=0
                    WHERE id_item=20 AND id_city='.$_SESSION['id_city'].'
                    LIMIT '.$metal_to_destroy;*/
        try
        {
            $res=$database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //make the next building available, if exists
        for($i=0; $i<$count; $i++){
            $id=0;
            switch($result['id_bld'][$i]){
                case 3:
                    $id=4;
                    break;
                case 4:
                    $id=5;
                    break;
                case 6:
                    $id=7;
                    break; 
                case 7:
                    $id=8;
                    break;
                case 8:
                    $id=9;
                    break; 
                case 10:
                    $id=11;
                    break;
                case 11:
                    $id=12;
                    break;
                case 13:
                    $id=14;
                    break;
                case 14:
                    $id=15;
                    break;
            }
            $query='UPDATE building_city
                    SET is_available=1
                    WHERE id_city='.$_SESSION['id_city'].' AND id_bld='.$id;
                try
                {
                    $res=$database->mysql->query($query);
                } 
                catch (PDOException $e)
                {
                    $database->mysql->rollBack();
                    $log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                    echo "Une erreur est survenue, merci de recharger la page.";
                    die();
                }
        }

        //add the new of new building
        for($i=0; $i<$count; $i++){
            $news->addNew($database, "build", $result['bld_name'][$i], 1);
        }

    }
}

// Check the remaining time for IN VOTE PROCESS laws and validate or refuse them based on the vote numbers and the city's total players
function checkLawsVotesInProgress()
{
    // Update the ciy's laws's status if the vote process has timed out, and the player's current votes on theses laws

}