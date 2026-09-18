<?php

#region Session

if(!isset($_SESSION))
session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
    die();
}

#endregion


#region Classes

require dirname(dirname(__FILE__))."/config/config.php";

require dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

require dirname(dirname(__FILE__))."/class/class.map.php";
require dirname(dirname(__FILE__))."/tools/tools.php";

require dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

require dirname(dirname(__FILE__))."/class/class.cache.php";
$cache = new Cache($_SESSION['id_city']);

require dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

require dirname(dirname(__FILE__))."/class/class.action.php";
$action = new Action($database, $log);

require dirname(dirname(__FILE__))."/class/class.stat.php";
$stat = new Stat($database, $log, $action);

require dirname(dirname(__FILE__))."/class/class.statistic.php";
$statistic = new Statistic($database, $log, $news);

#endregion


// Globals
$nbrDecorsInGame = 2; //arbre, rocher
$nbrDecorsPerArea = 100;
$sizeMapX = 20;
$sizeMapY = 20;
//$defaultFoodLosTravelValue = 2;
//$defaultMoraleLosTravelValue = 2;
 
$database->mysql->beginTransaction();

#region Routing

if (isset($_REQUEST["param"]) && !empty($_REQUEST["param"])) {
    $param = $_REQUEST["param"];
   
   if (isset($_REQUEST["id"]) && !empty($_REQUEST["id"])) {
        $id = $_REQUEST["id"];
   }
 
    switch($param) {
      case "loadMap":
         loadMap($database, "map", $stat, $statistic, $action, $log);
         break;
        case "loadDj":
         loadMap($database, "dj", $stat, $statistic, $action, $log);
         break;
      case "loadAbyss":
         loadMap($database, "abyss", $stat, $statistic, $action, $log);
         break;
      case "loadHell":
         loadMap($database, "hell", $stat, $statistic, $action, $log);
         break;
      case "loadDj2":
         loadMap($database, "dj2", $stat, $statistic, $action, $log);
         break;
      case "checkMapLimitSides":
         checkMapLimitSides($database);
         break;
      case "checkMapLimitCorners":
         checkMapLimitCorners($database);
         break;
         
      case "plantTree":
         plantTree($database);
         break;
      case "reInitializeMap":
         reInitializeMap($database);
         break;
      case "createMap":
          createMap($database);
          break;
      case "moveUp":
          moveUp($database, $log);
          break;
      case "moveRight":
          moveRight($database, $log);
          break;
      case "moveDown":
          moveDown($database, $log);
          break;
      case "moveLeft":
          moveLeft($database, $log);
          break;
      
      case "moveDownLeft":
          moveDownLeft($database, $log);
          break;
      case "moveUpRight":
          moveUpRight($database, $log);
          break;
      case "moveDownRight":
          moveDownRight($database, $log);
          break;
      case "moveUpLeft":
          moveUpLeft($database, $log);
          break;
      
      case "getPosX":
          playerIsFighting($database);
          getPosX();
          break;
      case "getPosY":
          getPosY();
          break;
          
      default:
       break;
     }
}

#endregion


#region Area discovery

//discover the map area and increase explo stat
function discoverArea($area, $database, $stat, $statistic, $action, $log)
{
    switch($area)
    {
        case 'map':
            $query = 'UPDATE map SET is_discovered=1
                WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y = '.$_SESSION['player_pos_y'];
        break;
        case 'dj':
            $query = 'UPDATE dj SET is_discovered=1
                WHERE id_city='.$_SESSION['id_city'].' AND dj_x='.$_SESSION['player_pos_x'].' AND dj_y = '.$_SESSION['player_pos_y'];
        break;
        case 'abyss':
            $query = 'UPDATE abyss SET is_discovered=1
                WHERE id_abyss='.$_SESSION['id_city'].' AND abyss_x='.$_SESSION['player_pos_x'].' AND abyss_y = '.$_SESSION['player_pos_y'];
        break;
        default:
    }
    try
    {
        $database->mysql->query($query);
        $stat->updatePlayerStat('explo_stat', XP_DISCOVER_AREA);
        $statistic->updatePlayerStatistic('area_discovered', 1);
        $action->postAction('explo');
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // If the area contains a dungeon, add the dj_discover statistic.
    //TODO
}

#endregion


#region Map loading

function loadMap($database, $area, $stat, $statistic, $action, $log) 
{
    $ObjAreas = new Map($database, $log);

    if ($area == 'map')
    {
        $ObjAreas->getAreasDataMap($area, $_SESSION['player_pos_x'], $_SESSION['player_pos_y']);
        
        //if the area is undiscovered, discover it and update explo stat
        if($ObjAreas->is_discovered[4] == 0)
        {
            discoverArea('map', $database, $stat, $statistic, $action, $log);
        }

        //check if the area is a dj entry
        if($ObjAreas->idDj != 0)
        {
            $_SESSION['id_dj'] = $ObjAreas->idDj;
        }
    }

    else if ($area == 'dj')
    {
        $ObjAreas->getAreasDataDj($area, $_SESSION['player_pos_x'], $_SESSION['player_pos_y'], $_SESSION['id_dj']);

        //if the area is undiscovered (not map limit), discover it and update explo stat
        if(sizeof($ObjAreas->is_discovered) > 1){
            if($ObjAreas->is_discovered[4] == 0){
                discoverArea('dj', $database, $stat, $statistic, $action, $log);
            }
        //area discover (on map limit)
        } else {
            if($ObjAreas->is_discovered[0] == 0){
                discoverArea('dj', $database, $stat, $statistic, $action, $log);
            }
        }
        
        //the area is the exit
        if($ObjAreas->id_map_exit != 0){
            $_SESSION['id_map_exit'] = $ObjAreas->id_map_exit;
        }
        //the area is the d2 entry
        //if($ObjAreas->id_area_entry_d2[4] != 0){
        //    $_SESSION['id_dj'] = $ObjAreas->id_area_exit[4];
        //}
    }

    else if ($area == 'abyss')
    {
        $ObjAreas->getAreasDataAbyss($area, $_SESSION['player_pos_x'], $_SESSION['player_pos_y'], $_SESSION['id_abyss']);
        
        //if the area is undiscovered, discover it and update explo stat
        if($ObjAreas->is_discovered[4] == '0'){
            discoverArea('abyss', $database, $stat, $statistic, $action, $log);
        }
        //the area is the exit
        if($ObjAreas->id_map_exit != 0){
            $_SESSION['id_map_exit'] = $ObjAreas->id_map_exit;
        }
        //the area is the hell entry
        if($ObjAreas->id_hell_entry != 0){
            $_SESSION['id_hell_entry'] = $ObjAreas->id_hell_entry;
        }
    }

    else if ($area == 'hell')
    {
        $ObjAreas->getAreasDataHell($area, $_SESSION['player_pos_x'], $_SESSION['player_pos_y'], $_SESSION['id_hell']);
        
        //the area is the exit
        /*
        if($ObjAreas->id_map_exit != 0){
            $_SESSION['id_map_exit'] = $ObjAreas->id_map_exit;
        }*/
    }

    else if ($area == 'dj2')
    {
        $ObjAreas->getAreasDataDj2($area, $_SESSION['player_pos_x'], $_SESSION['player_pos_y'], $_SESSION['id_dj2']);
        
        //??
        //the area is the exit
        if($ObjAreas->id_map_exit != 0){
            $_SESSION['id_map_exit'] = $ObjAreas->id_map_exit;
        }
    }
    
    $ObjAreasJSON = json_encode($ObjAreas);
    echo $ObjAreasJSON;

}

#endregion


#region Map limits

function checkMapLimitSides($database)
{
    $area = $_SESSION['player_area'];
    $query = 'select max(x) as x, max(y) as y from '.$area;
    $result = $database->mysql->query($query);
    $maxPos = $result->fetch_assoc();
    
    if($_SESSION['player_pos_y'] == $maxPos["player_pos_y"]){
        echo 'north';
    }
    if($_SESSION['player_pos_y'] == -$maxPos["player_pos_y"]){
        echo 'south';
    }
    if($_SESSION['player_pos_x'] == $maxPos["player_pos_x"]){
        echo 'east';
    }
    if($_SESSION['player_pos_x'] == -$maxPos["player_pos_x"]){
        echo 'west';
    }  
}
function checkMapLimitCorners($database)
{
    $area = $_SESSION['player_area'];
    $query = 'select max(x) as x, max(y) as y from '.$area;
    $result = $database->query($query);
    $maxPos = $result->fetch_assoc();
    
    if($_SESSION['player_pos_y'] == $maxPos["y"] && $_SESSION['player_pos_x'] == $maxPos["x"]){
        echo 'northeast';
    }
    if($_SESSION['player_pos_y'] == -$maxPos["y"] && $_SESSION['player_pos_x'] == $maxPos["x"]){
        echo 'southeast';
    }
    if($_SESSION['player_pos_y'] == -$maxPos["y"] && $_SESSION['player_pos_x'] == -$maxPos["x"]){
        echo 'southwest';
    }
    if($_SESSION['player_pos_y'] == $maxPos["y"] && $_SESSION['player_pos_x'] == -$maxPos["x"]){
        echo 'northwest';
    }  
}

#endregion


#region Fleeing from battle

//Vérifier si le joueur a fuit son combat
function playerIsFighting($database){
    if(isset($_SESSION['is_fighting']) && $_SESSION['is_fighting'] == 1){
        $_SESSION['is_fighting'] = 0;
        $query="UPDATE player SET is_fighting=0 
            WHERE id_player=".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }
     }
}

#endregion


#region Player position display

function getPosX()
{
    if($_SESSION['player_pos_x'] >= 0){
        $x = $_SESSION['player_pos_x'];
        $result = $x .= " km Est";
    } else {
        $x = abs($_SESSION['player_pos_x']);
        $result = $x .= " km Ouest";
    }
    echo $result;
}

function getPosY()
{
    $area = $_SESSION['player_area'];
    // Position sur la map (centrée autour de 0,0)
    if($area == 'outside' || $area = 'dj'){
        if($_SESSION['player_pos_y'] >= 0){
            $x = $_SESSION['player_pos_y'];
            $result = $x .= " km Nord";
        } else {
        $x = abs($_SESSION['player_pos_y']);
        $result = $x .= " km Sud";
        }
        echo $result;
    }
    //position dans l'abysse ( haut-gauche = 0,0 pour y=profondeur)
    else if ($area == 'abyss'){
        $y = abs($_SESSION['player_pos_y']);
        $result = $y .= " km de profondeur";
        echo $result;
    }
}
 
#endregion


#region Unused functions

function plantTree($database)
{
   $query = 'UPDATE map SET content=\'tttttttttt\''; //WHERE id=$areaId;
   $database->mysql->query($query);
}
 
//rempli un tableau avec 100*100 chaines de charactères de décors puis les insère dans map. S'utilise une fois au debut de la partie.
function createMap($database)
{
    $query = 'truncate table map';
    $database->mysql->query($query);
    
    $size_X = $GLOBALS["sizeMapX"];
    $size_Y = $GLOBALS["sizeMapY"];
    $mapSize = $size_X * $size_Y; //9=3*3
    
    $content = [];
    
    for ($i=0; $i<$mapSize; $i++) {    //0 < i < 8
        array_push($content,randString($GLOBALS['nbrDecorsPerArea'])); //9 cases pour $content
    }
    echo $content[0];
    
    $id=0; //content[id]
    $minIdX = -floor(sqrt($mapSize)/2); //floor() arrondi inferieur, ceil() arrondie sup
    $maxIdX = floor(sqrt($mapSize)/2);
    $minIdY = -floor(sqrt($mapSize)/2);
    $maxIdY = floor(sqrt($mapSize)/2);
    for ($y=$minIdY; $y<=$maxIdY; $y++) { // -50<y<50;
        for ($x=$minIdX; $x<=$maxIdX; $x++) {
            
            $query = 'insert into map(content, x, y) values (\''.$content[$id].'\','.$x.','.$y.'); ';
           // echo 'insert into map(content, x, y) values (\''.$content[$id].'\','.$x.','.$y.'); ';
           $database->mysql->query($query);
            
            $id++;
        }
    }
}

//fait la même chose que createMap si les indices x et y sont deja créés.
function reInitializeMap($database)
{
  $query = 'select count(x) as x, count(y) as y from map';
  $result = $database->mysql->query($query);
  $row=$result->fetch_assoc();
  $max_X = $row["x"];
  $max_Y = $row["y"];
  
  //echo $max_X, $max_Y;
 
  $id=1;
  for ($i=0; $i<$max_X; $i++) {
   for ($j=0; $j<$max_Y; $j++) {
    $content=randString($GLOBALS['nbrDecorsPerArea']); //100 décors par case
    //echo $content;
    $query = 'update map set content=\''.$content.'\' where id=\''.$id.'\'';   
    //echo ' update map set content=\''.$content.'\' where id=\''.$id.'\'';
    $database->mysql->query($query);
    $id++;
   }
  }
 }
function randString($len) {
    $string = '';
    for ($i=0; $i<$len; $i++) {
        $char = rand(1,$GLOBALS['nbrDecorsInGame']+1); //+1 pour case vide
        switch ($char) {
            case '1': //tree
                $string.='t';
                break;
            case '2': //roche
                $string.='r';
                break;
            default: //void
                $string.='n';
                break;
        }
    }
    return $string;
}

#endregion


#region Allies

// Update the row "Ally" in the map table to konw the number of players on the player's area.
function updateMapAllies($database, $pos){

    $area = $_SESSION['player_area'];
    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];

    if($pos == 'prev'){

        switch($area){
            case 'outside':
                $query = 'UPDATE map SET ally = ally-1 
                WHERE x='.$x.' AND y='.$y.' AND id_city='.$_SESSION['id_city'];
                break;
            case 'dj':
                $query = 'UPDATE dj SET dj_ally = dj_ally-1 
                WHERE dj_x='.$x.' AND dj_y='.$y.' AND id_dj='.$_SESSION['id_city'];
                break;
            case 'dj2':
                $query = 'UPDATE dj2 SET dj2_ally = dj2_ally-1 
                WHERE dj2_x='.$x.' AND dj2_y='.$y.' AND id_dj2='.$_SESSION['id_city'];
                break;
            case 'abyss':
                $query = 'UPDATE abyss SET abyss_ally = abyss_ally-1 
                WHERE abyss_x='.$x.' AND abyss_y='.$y.' AND id_abyss='.$_SESSION['id_city'];
                break;
            case 'hell':
                $query = 'UPDATE hell SET hell_ally = hell_ally-1 
                WHERE hell_x='.$x.' AND hell_y='.$y.' AND id_hell='.$_SESSION['id_city'];
                break;
            default:
        }

    } else if ($pos == 'next'){

        switch($area){
            case 'outside':
                $query = 'UPDATE map SET ally = ally+1 
                WHERE x='.$x.' AND y='.$y.' AND id_city='.$_SESSION['id_city'];
                break;
            case 'dj':
                $query = 'UPDATE dj SET dj_ally = dj_ally+1 
                WHERE dj_x='.$x.' AND dj_y='.$y.' AND id_dj='.$_SESSION['id_city'];
                break;
            case 'dj2':
                $query = 'UPDATE dj2 SET dj2_ally = dj2_ally+1 
                WHERE dj2_x='.$x.' AND dj2_y='.$y.' AND id_dj2='.$_SESSION['id_city'];
                break;
            case 'abyss':
                $query = 'UPDATE abyss SET abyss_ally = abyss_ally+1 
                WHERE abyss_x='.$x.' AND abyss_y='.$y.' AND id_abyss='.$_SESSION['id_city'];
                break;
            case 'hell':
                $query = 'UPDATE hell SET hell_ally = hell_ally+1 
                WHERE hell_x='.$x.' AND hell_y='.$y.' AND id_hell='.$_SESSION['id_city'];
                break;
            default:
        }

    }

    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

}

#endregion


#region Movement in 8 directions

function moveUp($database, $log){   

    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    // move up on map (centered at (0,0))
    if($_SESSION['player_area'] != 'abyss'){
        $_SESSION['player_pos_y']+=1;
    } 
    // move up in abyss (not centered)
    else {
        $_SESSION['player_pos_y']-=1;
    }

    // change the ally number on the next area
    updateMapAllies($database, 'next');
    
    // to force the player to go back if the foe is too strong.
    $_SESSION['player_prev_dir'] = 's'; 
    
    //get the new area id from map ? -> useless as double join possible (x & y)

    //update this id in player
    $y = $_SESSION['player_pos_y'];
    $query = 'UPDATE player SET player_pos_y='.$y.', prev_dir="'.$_SESSION['player_prev_dir'].'" 
                WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
    
}
function moveRight($database, $log){

    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    // change the session to match the new area
    $_SESSION['player_pos_x']+=1;

    // change the ally number on the next area
    updateMapAllies($database, 'next');


    $_SESSION['player_prev_dir'] = 'w';
    
    $x = $_SESSION['player_pos_x'];

    $query = 'UPDATE player SET player_pos_x='.$x.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
}
function moveDown($database, $log)
{
    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    // move up on map (centered at (0,0))
    if($_SESSION['player_area'] != 'abyss'){
        $_SESSION['player_pos_y']-=1;
    } 
    // move up in abyss (not centered)
    else {
        $_SESSION['player_pos_y']+=1;
    }

    // change the ally number on the next area
    updateMapAllies($database, 'next');

    $_SESSION['player_prev_dir'] = 'n';
    
    $y = $_SESSION['player_pos_y'];

    $query = 'UPDATE player set player_pos_y='.$y.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
}
function moveLeft($database, $log){

    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    $_SESSION['player_pos_x']-=1;

    // change the ally number on the next area
    updateMapAllies($database, 'next');
    
    $_SESSION['player_prev_dir'] = 'e';
    
    $x = $_SESSION['player_pos_x'];

    $query = 'UPDATE player SET player_pos_x='.$x.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
}
function moveUpLeft($database, $log){  
    
    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    
    $_SESSION['player_pos_x']-=1;

    // move up on map (centered at (0,0))
    if($_SESSION['player_area'] != 'abyss'){
        $_SESSION['player_pos_y']+=1;
    } 
    // move up in abyss (not centered)
    else {
        $_SESSION['player_pos_y']-=1;
    }

    // change the ally number on the next area
    updateMapAllies($database, 'next');

    $_SESSION['player_prev_dir'] = 'se';
    
    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];
    
    $query = 'UPDATE player SET player_pos_y='.$y.', player_pos_x='.$x.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
    
}
function moveDownLeft($database, $log){

    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    $_SESSION['player_pos_x']-=1;

    // move up on map (centered at (0,0))
    if($_SESSION['player_area'] != 'abyss'){
        $_SESSION['player_pos_y']-=1;
    } 
    // move up in abyss (not centered)
    else {
        $_SESSION['player_pos_y']+=1;
    }

    // change the ally number on the next area
    updateMapAllies($database, 'next');

    $_SESSION['player_prev_dir'] = 'ne';
    
    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];

    $query = 'UPDATE player SET player_pos_y='.$y.', player_pos_x='.$x.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
}
function moveUpRight($database, $log){

    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    $_SESSION['player_pos_x']+=1;

    // move up on map (centered at (0,0))
    if($_SESSION['player_area'] != 'abyss'){
        $_SESSION['player_pos_y']+=1;
    } 
    // move up in abyss (not centered)
    else {
        $_SESSION['player_pos_y']-=1;
    }

    // change the ally number on the next area
    updateMapAllies($database, 'next');


    $_SESSION['player_prev_dir'] = 'sw';
    
    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];

    $query = 'UPDATE player SET player_pos_y='.$y.', player_pos_x='.$x.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
}
function moveDownRight($database, $log){

    // change the ally number on the current area
    updateMapAllies($database, 'prev');

    $_SESSION['player_pos_x']+=1;
    
    // move up on map (centered at (0,0))
    if($_SESSION['player_area'] != 'abyss'){
        $_SESSION['player_pos_y']-=1;
    } 
    // move up in abyss (not centered)
    else {
        $_SESSION['player_pos_y']+=1;
    }

    // change the ally number on the next area
    updateMapAllies($database, 'next');


    $_SESSION['player_prev_dir'] = 'nw';
    
    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];

    $query = 'UPDATE player SET player_pos_y='.$y.', player_pos_x='.$x.', prev_dir="'.$_SESSION['player_prev_dir'].'" WHERE id_player ='.$_SESSION['id_player'];
    
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get supply XP if the invent is near-full
    if($_SESSION['invent-size'] >= $_SESSION['max-invent']*(90/100) )
    {
        $stat->updatePlayerStat('supply_stat', XP_SUPPLY);
        $action->postAction('carry');
    }

    //Lose food and morale while traveling
    updateFoodValue($database);
    updateMoraleValue($database);

    // Check if the player has stealthed
    if(isset($_SESSION['player_can_stealth']) && $_SESSION['player_can_stealth'] == 1)
    {
        $stat->updatePlayerStat('explo_stat', XP_STEALTH);
        $statistic->updatePlayerStatistic('stealth', 1);
        $action->postAction('stealth-ok');
        unset($_SESSION['player_can_stealth']);
    }
}

#endregion


#region Check if player blocked by monsters

//check if the player is blocked by monsters
if (isset($_REQUEST["action"]) && $_REQUEST["action"] == 'checkIfPlayerBlocked') {
    $param = $_REQUEST["action"];
    $foeNbr = $_REQUEST["foeNbr"];

    $response = [];
    $response[0] = ''; //prev_dir
    $response[1] = ''; //player name
    $response[2] = ''; //action date
    $response[3] = ''; //action content
    $actionToDisplay = '';

    // An explorer can go twice as far as a warrior
    $defStealthCoef = 2;

    //if there is no monster, no need to flee
    if($foeNbr == 0){
        $prev_dir = 0;
    } else {

        #region Compute player stat

        // If explo is master, add 20%
        if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'explo_stat'){
            $exploStat = $_SESSION['player_stat']['explo_stat']['player_stat_lvl'] + floor($_SESSION['player_stat']['explo_stat']['player_stat_lvl']*20/100);
        } else {
            $exploStat = $_SESSION['player_stat']['explo_stat']['player_stat_lvl'];
        }   
        // If combat (atk) is master, add 20%
        if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'atk_stat'){
            $atkStat = $_SESSION['player_stat']['atk_stat']['player_stat_lvl'] + floor($_SESSION['player_stat']['atk_stat']['player_stat_lvl']*20/100);
        } else {
            $atkStat = $_SESSION['player_stat']['atk_stat']['player_stat_lvl'];
        }   

        #endregion

        #region adds stealth lvls based on building
        $exploStatBonusBld = 0;

        // Si seul le 1er niveau de collecte est construit (par défault)
        if(in_array(22, $_SESSION['id_bld']) && !in_array(23, $_SESSION['id_bld']) && !in_array(24, $_SESSION['id_bld'])){
            $exploStatBonusBld = 0;
        }
        // Si le 2nd niveau de collect est construit
        else if(in_array(23, $_SESSION['id_bld']) && !in_array(24, $_SESSION['id_bld'])){
            $exploStatBonusBld = 5;
        }
        // Si le 3e niveau est construit
        else if(in_array(24, $_SESSION['id_bld'])){
            $exploStatBonusBld = 10;
        }

        $exploStat += $exploStatBonusBld;

        #endregion

        #region Compute foe strenght

        $foeAtk = getFoeLvl($_SESSION['player_area'], $database);

        #endregion
        

        // If there are more than 40 foe on area (horde or dark era), then block anyway.
        $maxFoeNbrToBlock = 30;
        if($foeNbr >= $maxFoeNbrToBlock)
        {
            $query = 'SELECT prev_dir FROM player WHERE id_player ='.$_SESSION['id_player'];
                
            try
            {
                $res=$database->mysql->query($query);
                $data=$res->fetch(PDO::FETCH_ASSOC);
                $prev_dir = $data['prev_dir'];

                $action->postAction('blocked');
                // action for real time display
                $actionToDisplay = "Les Ombres sont trop puissantes ici. Vous êtes <span style='font-weight:bold;color:red;'>Bloqué(e)</span>. Vous pouvez revenir en arrière.";
            }  
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "error";
                die();
            }

        } else {
        
            //if the player is too weak, but can stealth pass the ennemies
            if( ($foeAtk > $atkStat && $foeAtk/$defStealthCoef <= $exploStat)){

                $action->postAction('stealth');
                //action for real time display
                $actionToDisplay =  "Les Ombres sont trop puissantes ici, mais vous êtes suffisament discret(e) pour vous <span style='font-weight:bold;color:olive;'>Camoufler</span>.";
                
                $prev_dir = 0;
                $_SESSION['player_can_stealth'] = 1; // if the player moves and this session is true, then postAction(stealth-ok), XP+ and reinitialize session. 
            } 
            
            //if the player is blocked
            else if( ($foeAtk > $atkStat && $foeAtk/$defStealthCoef > $exploStat)) //si le joueur n'est pas assez fort ou camouflé
                { 
                    $query = 'SELECT prev_dir FROM player WHERE id_player ='.$_SESSION['id_player'];
                    
                    try
                    {
                        $res=$database->mysql->query($query);
                    }
                    catch (PDOException $e)
                    {
                        $database->mysql->rollBack();
                        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                        echo "error";
                        die();
                    }

                    $data=$res->fetch(PDO::FETCH_ASSOC);
                    $prev_dir = $data['prev_dir'];

                    $action->postAction('blocked');
                    // action for real time display
                    $actionToDisplay = "Les Ombres sont trop puissantes ici. Vous êtes <span style='font-weight:bold;color:red;'>Bloqué(e)</span>. Vous pouvez revenir en arrière.";
                    
            } else {
                $prev_dir = 0;
            }
        }
    }

    //return  the blocked directions and the User Message.
    $response[0] = $prev_dir;
    $response[1] = $_SESSION['player_name'];
    $response[2] = date("H:i");
    $response[3] = $actionToDisplay;

    $responseJSON = json_encode($response);
    echo $responseJSON;

}

#endregion


#region Minimap

if(isset($_REQUEST['action']) && $_REQUEST['action'] != ''){
    if($_REQUEST['action'] == 'loadMiniMap'){ 

        #region Define coordinates

        // Define the X and Y coordinates to display on the minimap.

        // If there is a registered position for the minimap, use it. Else, use the player's pos.
        // When the player opens the map, it is automaticaly set to "center", so the position is reinitialized on the player.
        if($_REQUEST['direction'] == 'center'){

            $maxX = $_SESSION['player_pos_x'] + 10; 
            $minX = $_SESSION['player_pos_x'] - 9;
            $maxY = $_SESSION['player_pos_y'] + 10;
            $minY = $_SESSION['player_pos_y'] - 9;

        }
        if($_REQUEST['direction'] == 'n'){

            // 10 + 1 + 9 = 20 areas
            $maxX = $_SESSION['minimap_max_x']; 
            $minX = $_SESSION['minimap_min_x'];
            $maxY = $_SESSION['minimap_max_y'] + 5;
            $minY = $_SESSION['minimap_min_y'] + 5;

        }
        if($_REQUEST['direction'] == 'e'){

            $maxX = $_SESSION['minimap_max_x'] + 5; 
            $minX = $_SESSION['minimap_min_x'] + 5;
            $maxY = $_SESSION['minimap_max_y'];
            $minY = $_SESSION['minimap_min_y'];

        }
        if($_REQUEST['direction'] == 's'){

            $maxX = $_SESSION['minimap_max_x']; 
            $minX = $_SESSION['minimap_min_x'];
            $maxY = $_SESSION['minimap_max_y'] - 5;
            $minY = $_SESSION['minimap_min_y'] - 5;

        }
        if($_REQUEST['direction'] == 'w'){
            
            // Evolution compared to previous position.
            $maxX = $_SESSION['minimap_max_x'] - 5; 
            $minX = $_SESSION['minimap_min_x'] - 5;
            $maxY = $_SESSION['minimap_max_y'];
            $minY = $_SESSION['minimap_min_y'];

        }

        // Keep that in mind if the player clics again. Will be unset in model.
        $_SESSION['minimap_max_x'] = $maxX;
        $_SESSION['minimap_min_x'] = $minX;
        $_SESSION['minimap_max_y'] = $maxY;
        $_SESSION['minimap_min_y'] = $minY;

        #endregion 

        //TODO look beyond 29 areas
        //TODO, border limit

        // Loads the correct areas based on the session variable
        switch($_SESSION['player_area']){
            case 'outside':
                $resultJSON = loadOutsideMinimap($database, $maxX, $minX, $maxY, $minY);
            break;
            case 'dj':
                $resultJSON = loadDjMinimap($database, $maxX, $minX, $maxY, $minY);
            break;
            case 'abyss':
                $resultJSON = loadAbyssMinimap($database);
            break;
            default:
        }
        echo $resultJSON;
    }
}

// Load outside minimap
function loadOutsideMinimap($database, $maxX, $minX, $maxY, $minY){

    //TODO : ajouter cave, players, sentinelles
    $query="SELECT m.id, m.content, m.is_discovered, m.area_type, m.foe, m.x, m.y, m.ally
            FROM map m
            WHERE m.id_city=".$_SESSION['id_city']." AND (m.x BETWEEN ".$minX." AND ".$maxX.") AND (m.y BETWEEN ".$minY." AND ".$maxY.")
            ORDER BY m.y DESC, m.x ASC"; //from top-left to bottom-right.
    $mapArray =[];
    try {
        $res = $database->mysql->query($query);
        //$count=0;
        $discoverCount = 0;
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                //create associative array for json
                //$id_dj = (isset($data['id_dj']) ? $data['id_dj'] : 0); // If there is at least 1 DJ in the 20*20 areas.
                $is_city = ( ($data['x'] == 0 && $data['y'] == 0) ? '1' : '0'); // If there is the city entry in one of the 20*20 areas.
                 
                if ($data['is_discovered'] == 1){
                   $discoverCount++;
                }
                
                $mapArray[] = array
                (
                    'id' => $data['id'],
                    'content' => $data['content'],
                    'is_discovered' => $data['is_discovered'],
                    'area_type' => $data['area_type'],
                    'foe' => $data['foe'],
                    'x' => $data['x'],
                    'y' => $data['y'],
                    'ally' => $data['ally'],
                    'id_dj' => '0',
                    'is_city' => $is_city,
                    'is_camp' => '0'
                );
                //$count++;
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    //if the player wants to query areas that are out of the map (do not exist), display "black area" and reinitialize the map.
    if(sizeof($mapArray) != 400){ 
        echo 'outofthemap';

        unset($_SESSION['minimap_max_x']);
        unset($_SESSION['minimap_min_x']);
        unset($_SESSION['minimap_max_y']);
        unset($_SESSION['minimap_min_y']);

        $database->mysql->commit();
        die();
    }


    //if no area is discovered in the requested area
    if($discoverCount == 0){ 
        echo 'noDiscover'; //to alert

        unset($_SESSION['minimap_max_x']);
        unset($_SESSION['minimap_min_x']);
        unset($_SESSION['minimap_max_y']);
        unset($_SESSION['minimap_min_y']);

        $database->mysql->commit();
        die();
    }

    $discoverCount = 0;

    //select if the area is a CAMP or not
    //TODO : better to query city_camp instead ?
    $query="SELECT m.id
            FROM city_camp cc INNER JOIN map m ON cc.camp_pos_x = m.x AND cc.camp_pos_y = m.y
            WHERE cc.id_city=".$_SESSION['id_city']." AND (cc.camp_pos_x BETWEEN ".$minX." AND ".$maxX.") AND (cc.camp_pos_y BETWEEN ".$minY." AND ".$maxY.") AND cc.camp_area = 'outside'
            ORDER BY m.y DESC, m.x ASC"; //from top-left to bottom-right.
    
    $is_camp = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $is_camp[] = $data['id']; // If there is at least 1 DJ in the 20*20 areas.
                
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $is_camp) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_camp'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }
    //select if the area is a DJ or not
    $query="SELECT md.id_area
            FROM map_dj md INNER JOIN map m ON md.id_area = m.id 
            WHERE m.id_city=".$_SESSION['id_city']." AND (m.x BETWEEN ".$minX." AND ".$maxX.") AND (m.y BETWEEN ".$minY." AND ".$maxY.")
            ORDER BY m.y DESC, m.x ASC"; //from top-left to bottom-right.
    
    //$count=0;
    $is_dj = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $is_dj[] = $data['id_area']; // If there is at least 1 DJ in the 20*20 areas.
                //$count += 1;
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $is_dj) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_dj'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Adds the player's position to the Array
    $mapArray[0]['player_pos_x'] = $_SESSION['player_pos_x'];
    $mapArray[0]['player_pos_y'] = $_SESSION['player_pos_y'];

    
    /* mapArray = {'content' => 'tttttutututtutt', 
                    'is_discovered' => '1',
                    'area_type' => 'field',
                    'foe' => '4',
                    'players' => '5',
                    ...}
        */

    $resultJSON = json_encode($mapArray);
    return $resultJSON;
}

// Load dj minimap
function loadDjMinimap($database, $maxX, $minX, $maxY, $minY){

    //TODO : ajouter cave, players, sentinelles
    $query="SELECT d.id_dj_area, d.dj_content, d.is_discovered, d.dj_foe, d.dj_x, d.dj_y , d.dj_u, d.dj_r, d.dj_d, d.dj_l, d.dj_ally
            FROM dj d
            WHERE d.id_dj=".$_SESSION['id_dj']; //." AND (m.x BETWEEN ".$minX." AND ".$maxX.") AND (m.y BETWEEN ".$minY." AND ".$maxY.")
            //ORDER BY m.y DESC, m.x ASC"; //from top-left to bottom-right.
    $mapArray =[];
    try {
        $res = $database->mysql->query($query);
        //$count=0;
        $discoverCount = 0;
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {

                //create associative array for json
                 
                if ($data['is_discovered'] == 1){
                   $discoverCount++;
                }
                
                $mapArray[] = array
                (
                    'id' => $data['id_dj_area'],
                    'content' => $data['dj_content'],
                    'is_discovered' => $data['is_discovered'],
                    'area_type' => 'dj',
                    'foe' => $data['dj_foe'],
                    'x' => $data['dj_x'],
                    'y' => $data['dj_y'],
                    'u' => $data['dj_u'],
                    'r' => $data['dj_r'],
                    'd' => $data['dj_d'],
                    'l' => $data['dj_l'],
                    'ally' => $data['dj_ally'],
                    'id_dj' => '0',
                    'is_camp' => '0',
                    'is_dj' => '1',
                    'is_dj_entry' => '0',
                    'is_dj_exit' => '0',
                    'is_dj2_entry' => '0',
                    'is_dj2_exit' => '0',
                    'is_hell_entry' => '0',
                    'is_hell_exit' => '0'
                );
                //$count++;
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    //if the player wants to query areas that are out of the map (do not exist)
    // For now this code is useless, as the DJ will only be 20*20 areas, the minimap size.
    //if(sizeof($mapArray) != 400) //361 ??
    //{ 
    //    $database->mysql->commit();
    //    echo 'outofthemap';
    //    die();
    //}
    ////if no area is discovered in the requested area
    //if($discoverCount == 0)
    //{ 
    //    $database->mysql->commit();
    //    echo 'noDiscover';
    //    die();
    //}
    $discoverCount = 0;
    //select if the area is a CAMP or not
    //TODO : better to query city_camp instead ?
    $query="SELECT m.id
            FROM city_camp cc INNER JOIN map m ON cc.camp_pos_x = m.x AND cc.camp_pos_y = m.y
            WHERE cc.id_city=".$_SESSION['id_city']." AND (cc.camp_pos_x BETWEEN ".$minX." AND ".$maxX.") AND (cc.camp_pos_y BETWEEN ".$minY." AND ".$maxY.")
            ORDER BY m.y DESC, m.x ASC"; //from top-left to bottom-right.
    
    $is_camp = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $is_camp[] = $data['id']; // If there is at least 1 DJ in the 20*20 areas.
                
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $is_camp) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_camp'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // ???
    //select if the area is a DJ or not, to display the dj-camp sprite
    /*
    $query="SELECT md.id_area
            FROM map_dj md INNER JOIN map m ON md.id_area = m.id 
            WHERE m.id_city=".$_SESSION['id_city']." AND (m.x BETWEEN ".$minX." AND ".$maxX.") AND (m.y BETWEEN ".$minY." AND ".$maxY.")
            ORDER BY m.y DESC, m.x ASC"; //from top-left to bottom-right.
    
    //$count=0;
    $is_dj = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $is_dj[] = $data['id_area']; // If there is at least 1 DJ in the 20*20 areas.
                //$count += 1;
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $is_dj) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_dj'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }*/

    #region DJ exit

    // Load the DJ exit to display on minimap
    $query = 'SELECT dj_x, dj_y
              FROM map_dj 
              WHERE id_dj = '.$_SESSION['id_city'];
    try
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            $data=$res->fetch(PDO::FETCH_ASSOC);

            // Find the coordinates for dj entry
            for($i=0; $i<sizeof($mapArray); $i++)
            {
                if($mapArray[$i]['x'] == $data['dj_x'] && $mapArray[$i]['y'] == $data['dj_y'])
                {
                    $mapArray[$i]['is_dj_exit'] = '1';
                }
            }
       
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    #endregion

    #region dj_2 entry
    
    // Load the DJ2 entry to display on minimap (assumes there is only one dj2 per dj)
    $query = 'SELECT dj2_x, dj2_y
              FROM dj_dj2
              WHERE id_dj = '.$_SESSION['id_city'];
    try
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            $data=$res->fetch(PDO::FETCH_ASSOC);

            // Find the coordinates for dj entry
            for($i=0; $i<sizeof($mapArray); $i++)
            {
                if($mapArray[$i]['x'] == $data['dj2_x'] && $mapArray[$i]['y'] == $data['dj2_y'])
                {
                    $mapArray[$i]['is_dj2_entry'] = '1';
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    #endregion

    // Adds the player's position to the Array
    $mapArray[0]['player_pos_x'] = $_SESSION['player_pos_x'];
    $mapArray[0]['player_pos_y'] = $_SESSION['player_pos_y'];

    $resultJSON = json_encode($mapArray);
    return $resultJSON;
}

// Load abyss minimap
function loadAbyssMinimap($database){

    // une abysse fait 20*20 cases. Afficher toujours toutes les cases.
    $maxX = 9;
    $minX = -10;
    $maxY = 0;
    $minY = 19;

    $query="SELECT a.id_abyss_area, a.abyss_content, a.abyss_foe, a.abyss_x, a.abyss_y, a.is_discovered, a.abyss_ally
            FROM abyss a
            WHERE a.id_abyss=".$_SESSION['id_city']; //from top-left to bottom-right.
    $mapArray =[];
    try {
        $res = $database->mysql->query($query);
        //$count=0;
        $discoverCount = 0;
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {

                //create associative array for json
                if ($data['is_discovered'] == 1){
                   $discoverCount++;
                }
                
                $mapArray[] = array
                (
                    'id' => $data['id_abyss_area'],
                    'content' => $data['abyss_content'],
                    'area_type' => 'abyss',
                    'foe' => $data['abyss_foe'],
                    'x' => $data['abyss_x'],
                    'y' => $data['abyss_y'],
                    'id_dj' => '0',
                    'is_city' => '0',
                    'is_camp' => '0',
                    'is_abyss_exit' => '0',
                    'is_hell_entry' => '0',
                    'is_discovered' => $data['is_discovered'],
                    'ally' => $data['abyss_ally']
                );
                //$count++;
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }
    //echo $count;
    //if the player wants to query areas that are out of the map (do not exist)
    // For now this code is useless, as the DJ will only be 20*20 areas, the minimap size.
    /*if(sizeof($mapArray) != 361){ 
        echo 'outofthemap';
        die();
    }*/
    //if no area is discovered in the requested area
    /*if($discoverCount == 0){ 
        echo 'noDiscover'; //to alert
        die();
    }*/
    $discoverCount = 0;

    //select which area has a camp on it
    $query="SELECT a.id_abyss_area
            FROM city_camp cc INNER JOIN abyss a ON cc.camp_pos_x = a.abyss_x AND cc.camp_pos_y = a.abyss_y
            WHERE cc.id_city=".$_SESSION['id_city']." AND (cc.camp_pos_x BETWEEN ".$minX." AND ".$maxX.") AND (cc.camp_pos_y BETWEEN ".$minY." AND ".$maxY.") AND cc.camp_area = 'abyss'
            ORDER BY a.abyss_x DESC, a.abyss_y ASC"; //from top-left to bottom-right.
    
    $id_camp = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $id_camp[] = $data['id_abyss_area']; // If there is at least 1 DJ in the 20*20 areas.
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $id_camp) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_camp'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // check which area is the hell entry
    $query="SELECT a.id_abyss_area
            FROM abyss_hell ah INNER JOIN abyss a ON  ah.id_abyss = a.id_abyss 
            WHERE a.id_abyss=".$_SESSION['id_city']." AND (a.abyss_x BETWEEN ".$minX." AND ".$maxX.") AND (a.abyss_y BETWEEN ".$minY." AND ".$maxY.")
            ORDER BY a.abyss_y DESC, a.abyss_x ASC"; //from top-left to bottom-right.
    
    //$count=0;
    $is_hell_entry = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $is_hell_entry[] = $data['id_abyss_area']; // If there is at least 1 DJ in the 20*20 areas.
                //$count += 1;
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $is_hell_entry) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_hell_entry'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // check which area is the abyss exit
    $query="SELECT a.id_abyss_area
            FROM abyss a INNER JOIN abyss_city ac ON ac.id_abyss = a.id_abyss 
            WHERE a.id_abyss=".$_SESSION['id_city']." AND (a.abyss_x BETWEEN ".$minX." AND ".$maxX.") AND (a.abyss_y BETWEEN ".$minY." AND ".$maxY.")
            ORDER BY a.abyss_y DESC, a.abyss_x ASC"; //from top-left to bottom-right.
    
    //$count=0;
    $id_abyss_exit = [];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $id_abyss_exit[] = $data['id_abyss_area']; // If there is at least 1 DJ in the 20*20 areas.
                //$count += 1;
            }
            //echo sizeof($is_dj);
            //test if each area has a dj entry, then update the map array
            $n = sizeof($mapArray);
            for($i=0; $i<$n; $i++){
                if( in_array($mapArray[$i]['id'], $id_abyss_exit) ){ //test if the id of the area is also in $id_dj
                    $mapArray[$i]['is_abyss_exit'] = 1; 
                }
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_outside.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Adds the player's position to the Array
    $mapArray[0]['player_pos_x'] = $_SESSION['player_pos_x'];
    $mapArray[0]['player_pos_y'] = $_SESSION['player_pos_y'];
    
    /* mapArray = {'content' => 'tttttutututtutt', 
                    'is_discovered' => '1',
                    'area_type' => 'field',
                    'foe' => '4',
                    'players' => '5',
                    ...}
        */
    $resultJSON = json_encode($mapArray);
    return $resultJSON;
}

#endregion

$database->mysql->commit();
