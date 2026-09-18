<?php

#region Sessions

if(!isset($_SESSION))
session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
    die();
}

$masterStat = $_SESSION['master_stat'];
$isMaster = $_SESSION['is_master'];

#endregion


#region Classes

//loadCoreClassesForAjaxFiles(); //database, log, stat etc

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

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

#region Give every item to city

if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'give-all-to-city') {

    //

    // Give all items to the correct bank (city or camp)
    if($_SESSION['player_area'] == 'inside'){

        $query = 'UPDATE city_item SET item_pos = "bank", id_player=0
                WHERE id_player='.$_SESSION['id_player'].' AND item_pos = "invent" AND id_city = '.$_SESSION['id_city'].' AND is_alive=1';
    
    } else if ($_SESSION['player_area'] == 'camp'){
    
        $query = 'UPDATE city_item SET item_pos = "bank", id_player=0, id_camp='.$_SESSION['id_camp'].'
                WHERE id_player='.$_SESSION['id_player'].' AND item_pos = "invent" AND id_city = '.$_SESSION['id_city'].' AND is_alive=1';
    }
    
    try 
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }
    $action->postAction('give', null);

}

#endregion


#region Drag and Drop

//Update en BDD de la pos de l'item droppé dans un inventaire
if(isset($_REQUEST['idDraggedItem'])) {
    
    //

    $idDraggedItem = $_REQUEST['idDraggedItem'];

    //Comparaison de l'id div avec l'id BDD
    $idItemBDD = $_SESSION['invent-list-id'][$idDraggedItem];

    // Name transformation for BDD, and check for max-invent
    $droppedArea = $_REQUEST['droppedArea'];
    switch ($droppedArea){
        case "invent-player":
            $droppedArea = "invent";
            break;
        case "invent-city":
            $droppedArea = "bank";
            break;
        case "equip-shield":
            $droppedArea = "shield";
            break;
        case "equip-upper":
            $droppedArea = "upper";
            break;
        case "equip-lower":
            $droppedArea = "lower";
            break;
        case "equip-helmet":
            $droppedArea = "helmet";
            break;
        case "equip-mask":
            $droppedArea = "mask";
            break;
        case "equip-spear":
            $droppedArea = "spear";
            break;
        default:
            $database->mysql->commit();
            die(); //area invalide, empêche requête
            
    }

    // Drop in city bank or in camp bank
    if($droppedArea == "bank"){
        if($_SESSION['player_area'] == 'inside'){

            $query="UPDATE city_item SET item_pos='".$droppedArea."', id_player=0
            WHERE id_city_item=".$idItemBDD;
        
        } else if ($_SESSION['player_area'] == 'camp'){
        
            $query="UPDATE city_item SET item_pos='".$droppedArea."', id_camp=".$_SESSION['id_camp'].", id_player=0
            WHERE id_city_item=".$idItemBDD;
        }
    } else {
        // Any other case
        $query="UPDATE city_item SET item_pos='".$droppedArea."', id_camp=0, id_player=".$_SESSION['id_player']."
            WHERE id_city_item=".$idItemBDD;
    }
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Get the lvl and dur of the updated item to update session(lvl, dur) to keep the fight going w/out reload.
    // Only if the item is no dropped oin invent.
    if($droppedArea != 'invent'){
        $query="SELECT item_lvl, item_dur FROM city_item 
        WHERE item_pos='".$droppedArea."' AND id_camp=0 AND id_player=".$_SESSION['id_player']." AND is_alive=1 AND id_city_item=".$idItemBDD;
        try {
            $result = '';
            $res=$database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    // Update the session for the correct item
                    switch($droppedArea){
                        case 'shield':
                            $_SESSION['player_shield_dur'] = $data['item_dur'];
                            $_SESSION['player_shield_lvl'] = $data['item_lvl'];
                            $result = $data['item_dur'];
                        break;
                        case 'upper':
                            $_SESSION['player_upper_dur'] = $data['item_dur'];
                            $_SESSION['player_upper_lvl'] = $data['item_lvl'];
                            $result = $data['item_dur'];
                        break;
                        case 'lower':
                            $_SESSION['player_lower_dur'] = $data['item_dur'];
                            $_SESSION['player_lower_lvl'] = $data['item_lvl'];
                            $result = $data['item_dur'];
                        break;
                        case 'helmet':
                            $_SESSION['player_helmet_dur'] = $data['item_dur'];
                            $_SESSION['player_helmet_lvl'] = $data['item_lvl'];
                            $result = $data['item_dur'];
                        break;
                        case 'mask':
                            $_SESSION['player_mask_dur'] = $data['item_dur'];
                            $_SESSION['player_mask_lvl'] = $data['item_lvl'];
                            $result = $data['item_dur'];
                        break;
                        case 'spear':
                            $_SESSION['player_spear_dur'] = $data['item_dur'];
                            $_SESSION['player_spear_lvl'] = $data['item_lvl'];
                            $result = $data['item_dur'];
                        break;
                        default:
                    }
                }
                // Return the lvl of the dropped item to update the life_bar box on real time.
                $database->mysql->commit();
                echo $result;
            }
        } catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }
    }

    $statistic->updatePlayerStatistic('item_given_to_city', 1);
}

#endregion


#region Craft and cook

// Pour ajouter un nouvel emplacement de craft, simplement ajouter des variables là où il y en a déja 4.
if(isset($_REQUEST['idCraftMat1'])) {

    // If sypply is master, add 20% to max invent
    if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'supply_stat'){
        $maxInvent = $_SESSION['max-invent'] + floor($_SESSION['max-invent']*20/100);
    } else {
        $maxInvent = $_SESSION['max-invent'];
    }

    //vérifier la place restante dans l'inventaire
    if($_SESSION['player-invent-size'] >= $maxInvent)
    {
        $database->mysql->commit();
        echo "Merci de laisser une place dans l'inventaire.";
        die();
    }

    $idCraftMat1 = $_REQUEST['idCraftMat1'];
    $idCraftMat2 = $_REQUEST['idCraftMat2'];
    $idCraftMat3 = $_REQUEST['idCraftMat3'];
    $idCraftMat4 = $_REQUEST['idCraftMat4'];
    //$idCraftMat5 = $_REQUEST['idCraftMat5'];
    $itemType = $_REQUEST['itemType'];

    #region Sanitizing user input

    //Comparaison de l'id div avec l'id BDD

        //sanitize : être sûr que charque id div exite en index dans le tableau (ex : 3 => 34564)
        //chaque id doit être différent et inférieur à l'id max (taille du tableau session -1)

    $size = sizeof($_SESSION['invent-list-id']);
    if (( ($idCraftMat1 != $idCraftMat2) && ($idCraftMat2 != $idCraftMat3) && ($idCraftMat3 != $idCraftMat4) )) { //&& ($idCraftMat4 != $idCraftMat5)
        if (($idCraftMat1 < $size ) 
            && ($idCraftMat2 < $size) 
            && ($idCraftMat3 < $size) 
            && ($idCraftMat4 < $size)){ // && ($idCraftMat5 < $size )
        }
        else
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }
    }
    else
    {
        $database->mysql->commit();
        echo "L'objet n'existe plus !";
        die();
    }

    $idItemBDD1 = $_SESSION['invent-list-id'][$idCraftMat1];
    $idItemBDD2 = $_SESSION['invent-list-id'][$idCraftMat2];
    $idItemBDD3 = $_SESSION['invent-list-id'][$idCraftMat3];
    $idItemBDD4 = $_SESSION['invent-list-id'][$idCraftMat4];
    //$idItemBDD5 = $_SESSION['invent-list-id'][$idCraftMat5];

    //BDD get 5 material items (lvl, name)
    $query="SELECT i.item_name, ci.item_lvl 
        FROM city_item ci JOIN item i ON ci.id_item = i.id_item
        WHERE ci.id_city_item = :id";
    
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array(':id' => $idItemBDD1));
            $res = $sth->fetchAll();
            $nameItem1 = $res[0]['item_name'];
            $lvlItem1 = $res[0]['item_lvl'];
        $sth->execute(array(':id' => $idItemBDD2));
            $res = $sth->fetchAll();
            $nameItem2 = $res[0]['item_name'];
            $lvlItem2 = $res[0]['item_lvl'];
        $sth->execute(array(':id' => $idItemBDD3));
            $res = $sth->fetchAll();
            $nameItem3 = $res[0]['item_name'];
            $lvlItem3 = $res[0]['item_lvl'];
        $sth->execute(array(':id' => $idItemBDD4));
            $res = $sth->fetchAll();
            $nameItem4 = $res[0]['item_name'];
            $lvlItem4 = $res[0]['item_lvl'];
        /*$sth->execute(array(':id' => $idItemBDD5));
            $res = $sth->fetchAll();
            $lvlItem5 = $res[0]['item_name'];
            $nameItem5 = $res[0]['item_lvl'];*/
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    //Vérifie que la valeur du sélecteur de pièce d'armure est correcte
    if(($itemType != "upper-armor")
        && ($itemType != "lower-armor")
        && ($itemType != "helmet")
        && ($itemType != "mask")
        && ($itemType != "shield")
        && ($itemType != "spear")
        && ($itemType != "baked_orange")
        && ($itemType != "baked_honey")
        && ($itemType != "cake")) 
        {
            $database->mysql->commit();
            echo "Merci de ne pas modifier le sélecteur";
        }
    #endregion
    
    #region Amount of materials used

    // Recettes à combinaisons d'items
    // Total d'ingrédients uilisés (pas de placement particulier en zone de craft)
    $bakedOrangeMaterial = 0;
    $bakedHoneyMaterial = 0;
    switch ($nameItem1){
        case 'baked_orange':
            $bakedOrangeMaterial += 1;
        break;
        case 'baked_honey':
            $bakedHoneyMaterial += 1;
        break;
        default :
    }
    switch ($nameItem2){
        case 'baked_orange':
            $bakedOrangeMaterial += 1;
        break;
        case 'baked_honey':
            $bakedHoneyMaterial += 1;
        break;
        default :
    }
    switch ($nameItem3){
        case 'baked_orange':
            $bakedOrangeMaterial += 1;
        break;
        case 'baked_honey':
            $bakedHoneyMaterial += 1;
        break;
        default :
    }
    switch ($nameItem4){
        case 'baked_orange':
            $bakedOrangeMaterial += 1;
        break;
        case 'baked_honey':
            $bakedHoneyMaterial += 1;
        break;
        default :
    }

    #endregion

    #region New lvl computation

    //créer les stats (lvl, nom) du nouvel item based on raw materials
    $lvlItemCrafted = floor(($lvlItem1 + $lvlItem2 + $lvlItem3 + $lvlItem4 ) / 4); // + $lvlItem5

    // Define the lvl of the crafted item and adds the mastery +20% if exists
    switch($itemType){
        case "shield":
            $lvlItemCrafted = floor($_SESSION['player_stat']['forge_shield_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'forge_shield_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "upper-armor":
            $lvlItemCrafted = floor($_SESSION['player_stat']['forge_up_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'forge_up_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "lower-armor":
            $lvlItemCrafted = floor($_SESSION['player_stat']['forge_up_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'forge_up_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "helmet":
            $lvlItemCrafted = floor($_SESSION['player_stat']['forge_head_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'forge_head_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "mask":
            $lvlItemCrafted = floor($_SESSION['player_stat']['forge_head_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'forge_head_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "spear":
            $lvlItemCrafted = floor($_SESSION['player_stat']['forge_pike_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'forge_pike_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "cake":
            // The highest stat is used to cook.
            if($_SESSION['orange_cook_stat']['player_stat_lvl'] >= $_SESSION['honey_cook_stat']['player_stat_lvl']){
                $lvlItemCrafted = floor($_SESSION['orange_cook_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            } else {
                $lvlItemCrafted = floor($_SESSION['honey_cook_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            }
            //check mastery
            if($isMaster == 1 && ($masterStat == 'orange_cook_stat' || $masterStat == 'honey_cook_stat')){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "baked_orange":
            $lvlItemCrafted = floor($_SESSION['orange_cook_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'orange_cook_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
        case "baked_honey":
            $lvlItemCrafted = floor($_SESSION['honey_cook_stat']['player_stat_lvl']+$lvlItemCrafted)/2;
            if($isMaster == 1 && $masterStat == 'honey_cook_stat'){
                $lvlItemCrafted += floor($lvlItemCrafted*20/100);
            }
            break;
    }

    // Adds a random number based on the current building built
    $n = 0;

    // Cooking
    if ($itemType == "cake" || $itemType == "baked_orange" || $itemType == "baked_honey")
    {
        // Si seul le 1er niveau de la cuisine est construit (par défault)
        if(in_array(6, $_SESSION['id_bld']) && !in_array(7, $_SESSION['id_bld']) && !in_array(8, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de la cuisine est construit
        else if(in_array(7, $_SESSION['id_bld']) && !in_array(8, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(8, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }
    }

    // Crafting
    else
    { 
        // Si seul le 1er niveau de l'Atelier est construit (par défault)
        if(in_array(3, $_SESSION['id_bld']) && !in_array(4, $_SESSION['id_bld']) && !in_array(5, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de l'Atelier est construit
        else if(in_array(4, $_SESSION['id_bld']) && !in_array(5, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(5, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }
    }

    $lvlItemCrafted += $n;
    if ($lvlItemCrafted < 1){
        $lvlItemCrafted = 1;
    }

    #endregion

    #region Check if the recipe exists

    //Definie le type d'item à crafter et vérifie la recette
    switch($itemType){
        case "shield":
            if(($nameItem1 == "wood") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 9;
                $stat->updatePlayerStat('forge_shield_stat', XP_FORGE_SHIELD);
                $statistic->updatePlayerStatistic('shield_crafted', 1);
                $action->postAction('forge', null, 'Bouclier', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser 4 bois pour cette recette.";
                die();
            }
            break;
        case "upper-armor":
            if(($nameItem1 == "wood") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 5;
                $stat->updatePlayerStat('forge_up_stat', XP_FORGE_UP);
                $statistic->updatePlayerStatistic('upper_crafted', 1);
                $action->postAction('forge', null, 'Armure Haute', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser 4 bois pour cette recette.";
                die();
            }
            break;
        case "lower-armor":
            if(($nameItem1 == "wood") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 6;
                $stat->updatePlayerStat('forge_up_stat', XP_FORGE_UP);
                $statistic->updatePlayerStatistic('lower_crafted', 1);
                $action->postAction('forge', null, 'Armure Basse', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser 4 bois pour cette recette.";
                die();
            }
            break;
        case "helmet":
            if(($nameItem1 == "wood") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 7;
                $stat->updatePlayerStat('forge_head_stat', XP_FORGE_HEAD);
                $statistic->updatePlayerStatistic('helmet_crafted', 1);
                $action->postAction('forge', null, 'Casque', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser 4 bois pour cette recette.";
                die();
            }
            break;
        case "mask":
            if(($nameItem1 == "wood") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 8;
                $stat->updatePlayerStat('forge_head_stat', XP_FORGE_HEAD);
                $statistic->updatePlayerStatistic('mask_crafted', 1);
                $action->postAction('forge', null, 'Masque', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser 4 bois pour cette recette.";
                die();
            }
            break;
        case "spear":
            if(($nameItem1 == "wood") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 10;
                $stat->updatePlayerStat('forge_pike_stat', XP_FORGE_PIKE);
                $statistic->updatePlayerStatistic('pike_crafted', 1);
                $action->postAction('forge', null, 'Lance', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser 4 bois pour cette recette.";
                die();
            }
            break;
        case "cake":
            if($bakedHoneyMaterial == 2 && $bakedOrangeMaterial == 2){
                $id = 11;
                $statistic->updatePlayerStatistic('cake_cooked', 1);
                $action->postAction('cook', null, 'Gâteau', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser deux oranges et deux miels (cuisinés) pour cette recette.";
                die(); 
            }
            break;
        case "baked_orange":
            if(($nameItem1 == "orange") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 12;
                $stat->updatePlayerStat('cook_orange_stat', XP_COOK_ORANGE);
                $statistic->updatePlayerStatistic('orange_cooked', 1);
                $action->postAction('cook', null, 'Tarte aux fruits', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser des oranges pour cette recette.";
                die();
            }
            break;
        case "baked_honey":
            if(($nameItem1 == "honey") && ($nameItem1 == $nameItem2) && ($nameItem2 == $nameItem3) && ($nameItem3 == $nameItem4)){
                $id = 13;
                $stat->updatePlayerStat('cook_honey_stat', XP_COOK_HONEY);
                $statistic->updatePlayerStatistic('honey_cooked', 1);
                $action->postAction('cook', null, 'Miel raffiné', $lvlItemCrafted);
            } else {
                $database->mysql->commit();
                echo "Merci d'utiliser du miel pour cette recette.";
                die();
            }
            break;
        default:
            $database->mysql->commit();
            echo "Type d'item inconnu";
            die();
    }
    
    #endregion

    #region Add item and delete materials

    //supprimer 4 matériaux et créer le nouvel objet dans l'inventaire du joueur qui l'a fait
    $query="UPDATE city_item SET is_alive=0 
            WHERE id_city_item IN (".$idItemBDD1.", ".$idItemBDD2.", ".$idItemBDD3.", ".$idItemBDD4.")"; //, ".$idItemBDD5."
    try
    {
       $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    $query="INSERT INTO city_item(id_city, id_player, id_item, item_lvl, item_pos) VALUES (".$_SESSION['id_city'].", ".$_SESSION['id_player'].", ".$id.", ".$lvlItemCrafted.", 'invent')";
    try
    {
      $database->mysql->query($query);
    } 
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    #endregion

}

#endregion


#region Eat food
         
// Food variables for config
$foodOrangeBase = 20;
$moraleHoneyBase = 20;
$foodBakedOrangeBase = 90;
$moraleBakedHoneyBase = 90;
$food_and_morale_cake_base = 100;

if(isset($_REQUEST['eat'])) {

    //Event listener sur boutons dans tooltip item

    if($_REQUEST['eat']=='orange'){

        //test food bar
        if($_SESSION['player-food-bar']==100)
        {
            $database->mysql->commit();
            echo 'Vous êtes déjà rassasié !';
            die();
        }

        // Sanitize input id
        if(isset($_SESSION['invent-list-id'][$_REQUEST['idItemEaten']])){
            $idItemBDDEatOrange = $_SESSION['invent-list-id'][$_REQUEST['idItemEaten']];

            // Get the Lvl of eaten item through request(id)
            $idItemBDD = $_SESSION['invent-list-id'][$_REQUEST['idItemEaten']];
            $query = 'SELECT item_lvl FROM city_item where id_city_item='.$idItemBDDEatOrange.' AND id_item=3 LIMIT 1';
            try {
                $res = $database->mysql->query($query);
                if($res) {
                    while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                        $foodLvl = $data['item_lvl'];
                    }
                }
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "error";
                die();
            }

        }
        else
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }

        // If the returned data is empty, then the item is not an orange
        if(!isset($foodLvl))
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }

        // Define the food value to provide, depending on the player's best stat and the food lvl.
        // If I am lvl 10 and the food is lvl 20, the food loses 1 power compared to its base lvl. If I am lvl 300 and the food is 10, the food loses 28 power.
        // This allows a low dispersion at low differences, and still makes a difference at high lvl difference
        // Change the 10 to modify the impact of the lvl difference on food power.
        $diff = ($_SESSION['best_stat_lvl'] - $foodLvl);
        $diff > 0 ? ($foodToGive = $foodOrangeBase - floor($diff/10)) : ($foodToGive = $foodOrangeBase + floor($diff/10));

        // Is food too weak ? set the minimum food power at 5%
        $foodToGive < 0 ? ($foodToGive = 5) : '';

        //Nouvelle barre de faim
        $newFoodValue = $_SESSION['player-food-bar'] + $foodToGive;
        if ($newFoodValue > 100){
            $newFoodValue = 100;
        }
        $_SESSION['player-food-bar'] = $newFoodValue;

        $query="UPDATE player_life_bar SET player_food=".$newFoodValue."
            WHERE id_player=".$_SESSION['id_player'];
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         //suppression de l'item consommé
         $query="UPDATE city_item SET is_alive=0
            WHERE id_city_item=".$idItemBDD;
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         $action->postAction('eat-orange');
    }
    
    if($_REQUEST['eat']=='honey'){
        //test morale bar
        if($_SESSION['player-morale-bar']==100)
        {
            $database->mysql->commit();
            echo 'Vous êtes déjà à fond !';
            die();
        }

        //Nouvelle barre de morale
        $newMoraleValue = $_SESSION['player-morale-bar'] + 50;
        if ($newMoraleValue > 100){
            $newMoraleValue = 100;
        }
        $_SESSION['player-morale-bar'] = $newMoraleValue;

        $query="UPDATE player_life_bar SET player_morale=".$newMoraleValue."
            WHERE id_player=".$_SESSION['id_player'];
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         //suppression de l'item consommé
         $idItemBDD = $_SESSION['invent-list-id'][$_REQUEST['idItemEaten']];

         $query="UPDATE city_item SET is_alive=0
            WHERE id_city_item=".$idItemBDD;
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         $action->postAction('eat-honey');

    }

    if($_REQUEST['eat']=='baked_orange'){
        //test morale bar
        if($_SESSION['player-food-bar']==100)
        {
            $database->mysql->commit();
            echo 'Vous êtes déjà à fond !';
            die();
        }

        //Nouvelle barre de food
        $newFoodValue = $_SESSION['player-food-bar'] + 100;
        if ($newFoodValue > 100){
            $newFoodValue = 100;
        }
        $_SESSION['player-food-bar'] = $newFoodValue;

        $query="UPDATE player_life_bar SET player_food=".$newFoodValue."
            WHERE id_player=".$_SESSION['id_player'];
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         //suppression de l'item consommé
         $idItemBDD = $_SESSION['invent-list-id'][$_REQUEST['idItemEaten']];

         $query="UPDATE city_item SET is_alive=0
            WHERE id_city_item=".$idItemBDD;
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         $action->postAction('eat-baked-orange');
    }

    if($_REQUEST['eat']=='baked_honey'){
        //test morale bar
        if($_SESSION['player-morale-bar']==100)
        {
            $database->mysql->commit();
            echo 'Vous êtes déjà à fond !';
            die();
        }

        //Nouvelle barre de morale
        $newMoraleValue = $_SESSION['player-morale-bar'] + 100;
        if ($newMoraleValue > 100){
            $newMoraleValue = 100;
        }
        $_SESSION['player-morale-bar'] = $newMoraleValue;

        $query="UPDATE player_life_bar SET player_morale=".$newMoraleValue."
            WHERE id_player=".$_SESSION['id_player'];
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         //suppression de l'item consommé
         $idItemBDD = $_SESSION['invent-list-id'][$_REQUEST['idItemEaten']];

         $query="UPDATE city_item SET is_alive=0
            WHERE id_city_item=".$idItemBDD;
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         $action->postAction('eat-baked-honey');

    }

    if($_REQUEST['eat']=='cake'){
        //test morale bar
        if($_SESSION['player-morale-bar']==100 && $_SESSION['player-food-bar']==100)
        {
            $database->mysql->commit();
            echo 'Vous êtes déjà à fond !';
            die();
        }

        //Nouvelle barre de faim
        $newFoodValue = $_SESSION['player-food-bar'] + 100;
        if ($newFoodValue > 100){
            $newFoodValue = 100;
        }
        $_SESSION['player-food-bar'] = $newFoodValue;

        //Nouvelle barre de morale
        $newMoraleValue = $_SESSION['player-morale-bar'] + 100;
        if ($newMoraleValue > 100){
            $newMoraleValue = 100;
        }
        $_SESSION['player-morale-bar'] = $newMoraleValue;

        $query="UPDATE player_life_bar SET player_morale=".$newMoraleValue.", player_food=".$newFoodValue."
            WHERE id_player=".$_SESSION['id_player'];
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         //suppression de l'item consommé
         $idItemBDD = $_SESSION['invent-list-id'][$_REQUEST['idItemEaten']];

         $query="UPDATE city_item SET is_alive=0
            WHERE id_city_item=".$idItemBDD;
         try
         {
            $database->mysql->query($query);
         }
         catch (PDOException $e)
         {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
         }

         $action->postAction('eat-cake');

    }
}
    
#endregion


#region Harvest

// Harvest rare tree. May happen outside, or in DJ2.
function harvestTree1($database, $log, $statistic, $action, $stat){

    $area = $_SESSION['player_area'];
    //Request area string from DB

    if($area == 'outside'){

        $query = 'SELECT content FROM map 
                where id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' and y='.$_SESSION['player_pos_y'].'
                LIMIT 1';          

    } else if($area == 'dj2'){

        $query = 'SELECT dj2_content as content FROM dj2 
                where id_dj2='.$_SESSION['id_city'].' AND dj2_x='.$_SESSION['player_pos_x'].' and dj2_y='.$_SESSION['player_pos_y'].'
                LIMIT 1';
         
    }

    $lvlItemHarvested = 999;
    
    //Modify the area string to cut a tree
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                //Retirer un arbre de la chaine et le remplacer par une souche
                $content=$data['content'];
                //https://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match#1252710 
                $pos = strpos($content, 'u'); //first occurence of 't' in $content;
                if ($pos !== false) {
                    $newContent = substr_replace($content, 's', $pos, strlen('u')); //replace the char at $pos by 's'
                   
                    if($area == 'outside'){

                        $query2 = 'UPDATE map SET content=\''.$newContent.'\' WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y='.$_SESSION['player_pos_y'];
                                
                    } else if($area == 'dj2'){
                
                        $query2 = 'UPDATE dj2 SET dj2_content=\''.$newContent.'\' WHERE id_dj2='.$_SESSION['id_city'].' AND dj2_x='.$_SESSION['player_pos_x'].' AND dj2_y='.$_SESSION['player_pos_y'];
                         
                    }
                    $database->mysql->query($query2);
                    $query3 = "INSERT INTO city_item(id_item, id_player, id_city, item_lvl, item_pos) VALUES (2, ".$_SESSION['id_player'].", ".$_SESSION['id_city'].", ".$lvlItemHarvested.", 'invent')";
                    $database->mysql->query($query3);

                    // These functions are put here so they are not executed if the player clics on the harvest btn before it disapears. 
                    $stat->updatePlayerStat('woodcutter_stat', 20);
                    $statistic->updatePlayerStatistic('wood1_harvesting', 1);
                    $action->postAction('harvest-tree1', null, null, $lvlItemHarvested);
                }
            }
            
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans une des requêtes SQL de ajax_item.php harvestTree1 : '. $e->getMessage(), 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

}

//FONCTIONS A COMBINER
if(isset($_REQUEST['harvest'])) {

    // If sypply is master, add 20%
    if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'supply_stat'){
        $maxInvent = $_SESSION['max-invent'] + floor($_SESSION['max-invent']*20/100);
    } else {
        $maxInvent = $_SESSION['max-invent'];
    }

    //test max invent
    if($_SESSION['invent-size'] >= $maxInvent)
    {
        $database->mysql->commit();
        echo "Votre inventaire est plein !";
        die();
    }

    if($_REQUEST['harvest'] == 'tree'){

        // Define whether the tree is rare or common ("palourde" effect) (1/500)
        // Fait aussi au moment de la création de la map, pour afficher les arbres rares directement dessus.
        $m = rand(1,500);
        if ($m == 499){
            harvestTree1($database, $log, $statistic, $action, $stat);
            $database->mysql->commit();
            die();
        }

        // Define the lvl of the harvested item
        $lvlItemHarvested = $_SESSION['player_stat']['woodcutter_stat']['player_stat_lvl'];

        //adds the mastery +20% if exists
        if($isMaster == 1 && $masterStat == 'woodcutter_stat'){
            $lvlItemHarvested += floor($lvlItemHarvested*20/100);
        }
        
        #region adds randomness to planted item lvl based on the current building built
        $n = rand(-2, 5);

        // Si seul le 1er niveau de collecte est construit (par défault)
        if(in_array(16, $_SESSION['id_bld']) && !in_array(17, $_SESSION['id_bld']) && !in_array(18, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de collect est construit
        else if(in_array(17, $_SESSION['id_bld']) && !in_array(18, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(18, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }

        #endregion

        $lvlItemHarvested += $n;

        if($lvlItemHarvested <= 0)
            $lvlItemHarvested = 1;
         
        //Request area string from DB
        $query = 'SELECT content FROM map 
            WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y='.$_SESSION['player_pos_y'].'
            LIMIT 1';

        //Modify the area string to cut a tree
        try
        {
            $err ='';
            $res = $database->mysql->query($query);
            if($res)
            {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) 
                {
                    //Retirer un arbre de la chaine et le remplacer par une souche
                    $content=$data['content'];
                    $pos = strpos($content, 't'); //first occurence of 't' in $content;
                    if ($pos !== false)
                    {
                        $newContent = substr_replace($content, 's', $pos, strlen('t')); //replace the char at $pos by 's'
                        $query2 = 'UPDATE map SET content=\''.$newContent.'\' WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y='.$_SESSION['player_pos_y'];
                        $database->mysql->query($query2);
                        
                        $query3 = "INSERT INTO city_item(id_item, id_player, id_city, item_lvl, item_pos) VALUES (1, ".$_SESSION['id_player'].", ".$_SESSION['id_city'].", ".$lvlItemHarvested.", 'invent')";
                        $database->mysql->query($query3);

                        // These functions are put here so they are not executed if the player clics on the harvest btn before it disapears. 
                        $stat->updatePlayerStat('woodcutter_stat', 20);
                        $statistic->updatePlayerStatistic('wood_harvesting', 1);
                        $action->postAction('harvest-tree', null, null, $lvlItemHarvested);
                    }
                    else
                    {
                       // Si quelqu'un a coupé le dernier arbre avant nous
                       $database->mysql->commit();
                       echo "Il n'y a plus rien à couper !";
                       die();
                   }
                }
            }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans une des requêtes SQL de ajax_item.php harvestTree : '. $e->getMessage(), 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

    }

    else if($_REQUEST['harvest'] == 'tree1')
    {
        harvestTree1($database, $log, $statistic, $action, $stat);


        /*
        $area = $_SESSION['player_area'];
        //Request area string from DB

        if($area == 'outside'){

            $query = 'SELECT content FROM map 
                    where id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' and y='.$_SESSION['player_pos_y'].'
                    LIMIT 1';          

        } else if($area == 'dj2'){

            $query = 'SELECT dj2_content as content FROM dj2 
                    where id_dj2='.$_SESSION['id_city'].' AND dj2_x='.$_SESSION['player_pos_x'].' and dj2_y='.$_SESSION['player_pos_y'].'
                    LIMIT 1';
            
        }

        $lvlItemHarvested = 999;
        
        //Modify the area string to cut a tree
        try {
            $res = $database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    //Retirer un arbre de la chaine et le remplacer par une souche
                    $content=$data['content'];
                    //https://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match#1252710 
                    $pos = strpos($content, 'u'); //first occurence of 't' in $content;
                    if ($pos !== false) {
                        $newContent = substr_replace($content, 's', $pos, strlen('u')); //replace the char at $pos by 's'
                    
                        if($area == 'outside'){

                            $query2 = 'UPDATE map SET content=\''.$newContent.'\' WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y='.$_SESSION['player_pos_y'];
                                    
                        } else if($area == 'dj2'){
                    
                            $query2 = 'UPDATE dj2 SET dj2_content=\''.$newContent.'\' WHERE id_dj2='.$_SESSION['id_city'].' AND dj2_x='.$_SESSION['player_pos_x'].' AND dj2_y='.$_SESSION['player_pos_y'];
                            
                        }
                        
                        $database->mysql->query($query2);
                        $query3 = "INSERT INTO city_item(id_item, id_player, id_city, item_lvl, item_pos) VALUES (2, ".$_SESSION['id_player'].", ".$_SESSION['id_city'].", ".$lvlItemHarvested.", 'invent')";
                        $database->mysql->query($query3);

                        // These functions are put here so they are not executed if the player clics on the harvest btn before it disapears. 
                        $stat->updatePlayerStat('woodcutter_stat', XP_WOOD_CUTTING);
                        $statistic->updatePlayerStatistic('wood1_harvesting', 1);
                        $action->postAction('harvest-tree1', null, null, $lvlItemHarvested);
                    }
                }
                
            }
        } catch (PDOException $e){
            return $e->getMessage();
            //return false;
        }

        */

    }

    else if($_REQUEST['harvest'] == 'orange')
    {

        // Define the lvl of the harvested fruit
        $lvlItemHarvested = $_SESSION['player_stat']['farm_stat']['player_stat_lvl'];

        //adds the mastery +20% if exists
        if($isMaster == 1 && $masterStat == 'farm_stat'){
            $lvlItemHarvested += floor($lvlItemHarvested*20/100);
        }

        #region adds randomness to planted item lvl based on the current building built
        $n = rand(-2, 5);

        // Si seul le 1er niveau de collecte est construit (par défault)
        if(in_array(16, $_SESSION['id_bld']) && !in_array(17, $_SESSION['id_bld']) && !in_array(18, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de collect est construit
        else if(in_array(17, $_SESSION['id_bld']) && !in_array(18, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(18, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }

        #endregion


        $lvlItemHarvested += $n;
        
        if($lvlItemHarvested <= 0)
            $lvlItemHarvested = 1;
        
         
        //Request area string from DB
        $query = 'SELECT content FROM map 
                where id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' and y='.$_SESSION['player_pos_y'].'
                LIMIT 1';
        
        //Modify the area string to cut a tree
        try {
              $res = $database->mysql->query($query);
              if($res) {
                  while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                      //Retirer un arbre de la chaine et le remplacer par une souche
                      $content=$data['content'];
                      //https://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match#1252710 
                      $pos = strpos($content, 'o'); //first occurence of 't' in $content;
                      if ($pos !== false) {
                          $newContent = substr_replace($content, 'n', $pos, strlen('o')); //replace the char at $pos by 'n'
                          $query2 = 'UPDATE map SET content=\''.$newContent.'\' WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y='.$_SESSION['player_pos_y'];
                          $database->mysql->query($query2);
                          $query3 = "INSERT INTO city_item(id_item, id_player, id_city, item_lvl, item_pos) VALUES (3, ".$_SESSION['id_player'].", ".$_SESSION['id_city'].", ".$lvlItemHarvested.", 'invent')";
                          $database->mysql->query($query3);
                          $statistic->updatePlayerStatistic('orange_harvesting', 1);
                          $stat->updatePlayerStat('farm_stat', XP_FARM);
                          $action->postAction('harvest-orange', null, null, $lvlItemHarvested);
                      }
                  }
                  
              }
          }
          catch (PDOException $e)
          {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans une des requêtes SQL de ajax_item.php harvestOrange : '. $e->getMessage(), 'error', $_SESSION['id_player']);
            echo "error";
            die();
          }

    }

    else if($_REQUEST['harvest'] == 'honey')
    {
         
        // Define the lvl of the harvested fruit
        $lvlItemHarvested = $_SESSION['player_stat']['farm_stat']['player_stat_lvl'];

        //adds the mastery +20% if exists
        if($isMaster == 1 && $masterStat == 'farm_stat'){
            $lvlItemHarvested += floor($lvlItemHarvested*20/100);
        }
        
        #region adds randomness to planted item lvl based on the current building built
        $n = rand(-2, 5);

        // Si seul le 1er niveau de collecte est construit (par défault)
        if(in_array(16, $_SESSION['id_bld']) && !in_array(17, $_SESSION['id_bld']) && !in_array(18, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de collect est construit
        else if(in_array(17, $_SESSION['id_bld']) && !in_array(18, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(18, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }

        #endregion


        $lvlItemHarvested += $n;
        
        if($lvlItemHarvested <= 0)
            $lvlItemHarvested = 1;

        //Request area string from DB
        $query = 'SELECT content FROM map 
                where id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' and y='.$_SESSION['player_pos_y'].'
                LIMIT 1';
        
        //Modify the area string to cut a tree
        try {
              $res = $database->mysql->query($query);
              if($res) {
                  while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                      //Retirer un arbre de la chaine et le remplacer par une souche
                      $content=$data['content'];
                      //https://stackoverflow.com/questions/1252693/using-str-replace-so-that-it-only-acts-on-the-first-match#1252710 
                      $pos = strpos($content, 'h'); //first occurence of 't' in $content;
                      if ($pos !== false) {
                          $newContent = substr_replace($content, 'n', $pos, strlen('h')); //replace the char at $pos by 'n'
                          $query2 = 'UPDATE map SET content=\''.$newContent.'\' WHERE id_city='.$_SESSION['id_city'].' AND x='.$_SESSION['player_pos_x'].' AND y='.$_SESSION['player_pos_y'];
                          $database->mysql->query($query2);
                          $query3 = "INSERT INTO city_item(id_item, id_player, id_city, item_lvl, item_pos) VALUES (4, ".$_SESSION['id_player'].", ".$_SESSION['id_city'].", ".$lvlItemHarvested.", 'invent')";
                          $database->mysql->query($query3);
                          $statistic->updatePlayerStatistic('honey_harvesting', 1);
                          $stat->updatePlayerStat('farm_stat', XP_FARM);
                          $action->postAction('harvest-honey', null, null, $lvlItemHarvested);
                      }
                  }
                  
              }
          }
          catch (PDOException $e)
          {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans une des requêtes SQL de ajax_item.php harvestTree1 : '. $e->getMessage(), 'error', $_SESSION['id_player']);
            echo "error";
            die();
          }

          
    }
}

#endregion


#region Plant in farm

//Plant
if(isset($_REQUEST['plant'])) {

    if($_REQUEST['plant'] == 'orange'){

        $id = $_REQUEST['idItem'];

        //comparer l'id BDD list
        
        $idItemBDD1 = $_SESSION['invent-list-id'][$id];

        // Check that the planted item's id is valid
        $query="SELECT i.item_name, ci.item_lvl 
            FROM city_item ci JOIN item i ON ci.id_item = i.id_item
            WHERE ci.id_city_item = :id";
        
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':id' => $idItemBDD1));
                $res = $sth->fetchAll();
                $nameItem1 = $res[0]['item_name'];
                $lvlItem1 = $res[0]['item_lvl'];
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //vérifie qu'il s'agit bien d'une orange
        if ($nameItem1 != 'orange')
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }

        //supprimer l'orange de l'invent
        $query = 'UPDATE city_item SET is_alive = 0 
                WHERE id_city_item='.$idItemBDD1;
        try {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //define planted item lvl based on lvl material and farmer lvl
        $lvlItemPlanted = floor(($lvlItem1 + $_SESSION['player_stat']['farm_stat']['player_stat_lvl']) / 2);

        #region adds randomness to planted item lvl based on the current building built
        $n = 0;

        // Si seul le 1er niveau de la ferme est construit (par défault)
        if(in_array(13, $_SESSION['id_bld']) && !in_array(14, $_SESSION['id_bld']) && !in_array(15, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de la ferme est construit
        else if(in_array(14, $_SESSION['id_bld']) && !in_array(15, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(15, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }
        $lvlItemPlanted += $n;
        if($lvlItemPlanted <= 0)
            $lvlItemPlanted = 1;

        #endregion

        //ajouter de la terre dans la ferme
        $query = 'INSERT INTO city_farm(id_player, id_city, item_lvl, id_item) 
                    VALUES ('.$_SESSION['id_player'].', '.$_SESSION['id_city'].', '.$lvlItemPlanted.', 14)';
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        $action->postAction('plant-orange', null, null, $lvlItemPlanted);
        $stat->updatePlayerStat('farm_stat', XP_FARM);
    }

    else if($_REQUEST['plant'] == 'honey'){

        $id = $_REQUEST['idItem'];

        //comparer l'id BDD list
        
        $idItemBDD1 = $_SESSION['invent-list-id'][$id];

        //BDD get  material item (lvl, name)
        $query="SELECT i.item_name, ci.item_lvl 
            FROM city_item ci JOIN item i ON ci.id_item = i.id_item
            WHERE ci.id_city_item = :id";
        
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':id' => $idItemBDD1));
                $res = $sth->fetchAll();
                $nameItem1 = $res[0]['item_name'];
                $lvlItem1 = $res[0]['item_lvl'];
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //vérifie qu'il s'agit bien d'une orange
        if ($nameItem1 != 'honey')
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }

        //supprimer l'orange de l'invent
        $query = 'UPDATE city_item SET is_alive = 0 
                WHERE id_city_item='.$idItemBDD1;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //define planted item lvl based on lvl material and farmer lvl
        $lvlItemPlanted = floor(($lvlItem1 + $_SESSION['player_stat']['farm_stat']['player_stat_lvl']) / 2);

        #region adds randomness to planted item lvl based on the current building built

        $n = 0;
        // Si seul le 1er niveau de la ferme est construit (par défault)
        if(in_array(13, $_SESSION['id_bld']) && !in_array(14, $_SESSION['id_bld']) && !in_array(15, $_SESSION['id_bld'])){
            $n = rand(-2,3);
        }
        // Si le 2nd niveau de la ferme est construit
        else if(in_array(14, $_SESSION['id_bld']) && !in_array(15, $_SESSION['id_bld'])){
            $n = rand(0,10);
        }
        // Si le 3e niveau est construit
        else if(in_array(15, $_SESSION['id_bld'])){
            $n = rand(10,20);
        }
        $lvlItemPlanted += $n;
        if($lvlItemPlanted <= 0)
            $lvlItemPlanted = 1;

        #endregion

        //ajouter de la terre dans la ferme
        $query = 'INSERT INTO city_farm(id_player, id_city, item_lvl, id_item) 
                    VALUES ('.$_SESSION['id_player'].', '.$_SESSION['id_city'].', '.$lvlItemPlanted.', 17)';
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        $action->postAction('plant-honey', null, null, $lvlItemPlanted);
        $stat->updatePlayerStat('farm_stat', XP_FARM);
    }
}

#endregion


#region Harvest in farm
    
//récolter
if(isset($_REQUEST['harvest_farm'])) {

    // If sypply is master, add 20%
    if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'supply_stat'){
        $maxInvent = $_SESSION['max-invent'] + floor($_SESSION['max-invent']*20/100);
    } else {
        $maxInvent = $_SESSION['max-invent'];
    }

    //vérifier la place restante dans l'inventaire
    if($_SESSION['player-invent-size'] + 5 > $maxInvent)
    {
        $database->mysql->commit();
        echo "Pas assez de place dans l'inventaire (besoin de 5)";
        die();
    }

    // Harvest Orange bush from farm.
    if($_REQUEST['harvest_farm'] == 'orange'){

        $id = $_REQUEST['idItem'];

        //comparer l'id BDD list
        
        $idItemBDD1 = $_SESSION['invent-list-id'][$id];

        //BDD get material item (lvl, name)
        $query="SELECT i.item_name, cf.item_lvl 
            FROM city_farm cf JOIN item i ON cf.id_item = i.id_item
            WHERE cf.id_city_farm = :id";
        
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':id' => $idItemBDD1));
                $res = $sth->fetchAll();
                $nameItem1 = $res[0]['item_name'];
                $lvlItem1 = $res[0]['item_lvl'];
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //vérifie qu'il s'agit bien d'un oranger
        if ($nameItem1 != 'orange_tree')
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }

        //supprimer l'oranger de la ferme
        $query = 'UPDATE city_farm SET is_alive = 0 
                WHERE id_city_farm='.$idItemBDD1;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //définir le nombre d'oranges à recevoir selon l'ère
        if(isset($_SESSION['era']) && $_SESSION['era'] == 'red')
            $n = rand(1, 3);
        else if(isset($_SESSION['era']) && $_SESSION['era'] == 'dark')
            $n = rand(1, 2);
        else   
            $n = rand(1, 5);

        $counter=0;

        //ajouter les oranges dans l'invent
        for($i = 0; $i<$n; $i++){ //STORED PROCEDURE !
            
            // Define the lvl of the harvested fruit
            $lvlItemHarvested = ($_SESSION['player_stat']['farm_stat']['player_stat_lvl']+$lvlItem1)/2;

            //adds the mastery +20% if exists
            if($isMaster == 1 && $masterStat == 'farm_stat'){
                $lvlItemHarvested += floor($lvlItemHarvested*20/100);
            }

            //adds randomness
            $m = rand(-2, 5);
            $lvlItemHarvested += $m;

            if($lvlItemHarvested <= 0)
            $lvlItemHarvested = 1;

            $query = 'INSERT INTO city_item(id_player, id_city, item_lvl, item_pos, id_harvester, id_item) 
                        VALUES ('.$_SESSION['id_player'].', '.$_SESSION['id_city'].', '.$lvlItemHarvested.', "invent", '.$_SESSION['id_player'].', 3)';
            try
            {
                $database->mysql->query($query);
                $action->postAction('harvest-orange', null, null, $lvlItemHarvested);
                $statistic->updatePlayerStatistic('orange_farming', 1);
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "error";
                die();
            }
            $counter++;
        }

        $stat->updatePlayerStat('farm_stat', XP_FARM);

    }

    else if($_REQUEST['harvest_farm'] == 'honey'){

        $id = $_REQUEST['idItem'];

        //comparer l'id BDD list
        $idItemBDD1 = $_SESSION['invent-list-id'][$id];

        //BDD get material item (lvl, name)
        $query="SELECT i.item_name, cf.item_lvl 
            FROM city_farm cf JOIN item i ON cf.id_item = i.id_item
            WHERE cf.id_city_farm = :id";
        
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':id' => $idItemBDD1));
                $res = $sth->fetchAll();
                $nameItem1 = $res[0]['item_name'];
                $lvlItem1 = $res[0]['item_lvl'];
        }
        catch(PDOException $exception)
        { 
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        } 

        //vérifie qu'il s'agit bien d'une ruche a miel
        if ($nameItem1 != 'hive_honey')
        {
            $database->mysql->commit();
            echo "L'objet n'existe plus !";
            die();
        }

        //supprimer la ruche a miel de la ferme
        $query = 'UPDATE city_farm SET is_alive = 0 
                WHERE id_city_farm='.$idItemBDD1;
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        //définir le nombre de miel à recevoir selon l'ère
        if(isset($_SESSION['era']) && $_SESSION['era'] == 'red')
            $n = rand(1, 3);
        else if(isset($_SESSION['era']) && $_SESSION['era'] == 'dark')
            $n = 1;
        else   
            $n = rand(1, 5);

        //ajouter les miels dans l'invent
        for($i = 0; $i<$n; $i++){ //STORED PROCEDURE !

            // Define the lvl of the harvested fruit
            $lvlItemHarvested = ($_SESSION['player_stat']['farm_stat']['player_stat_lvl']+$lvlItem1)/2;

            //adds the mastery +20% if exists
            if($isMaster == 1 && $masterStat == 'farm_stat'){
                $lvlItemHarvested += floor($lvlItemHarvested*20/100);
            }

            //adds randomness
            $m = rand(-2, 5);
            $lvlItemHarvested += $m;

            if($lvlItemHarvested <= 0)
            $lvlItemHarvested = 1;

            $query = 'INSERT INTO city_item(id_player, id_city, item_lvl, item_pos, id_harvester, id_item) 
                        VALUES ('.$_SESSION['id_player'].', '.$_SESSION['id_city'].', '.$lvlItemHarvested.', "invent", '.$_SESSION['id_player'].', 4';
            try
            {
                $database->mysql->query($query);
                $statistic->updatePlayerStatistic('honey_farming', 1);
                $action->postAction('harvest-honey', null, null, $lvlItemHarvested);
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de ajax_item.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "error";
                die();
            }
            $counter++;
        }
        //echo $counter;

        $stat->updatePlayerStat('farm_stat', XP_FARM);
        
    }
}

#endregion

$database->mysql->commit();
