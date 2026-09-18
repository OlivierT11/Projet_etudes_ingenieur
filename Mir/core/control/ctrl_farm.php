<?php 
//session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

/***************/
/*INVENT PLAYER*/
/***************/

//get harvester and crafter name (for tooltip)
$query="SELECT p.player_name AS harvester_name
    FROM city_item ci JOIN player p ON ci.id_harvester = p.id_player
    WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND ci.item_pos='invent' AND ci.is_alive=1"; 
try {
    $res=$database->mysql->query($query);
    if($res) {
       while($data=$res->fetch(PDO::FETCH_ASSOC)) {
            $result['harvester_name'][]= $data['harvester_name'];
       }
   }
} catch (PDOException $e){
    echo $e->getMessage();
}
$query="SELECT p.player_name AS crafter_name
    FROM city_item ci JOIN player p ON ci.id_crafter = p.id_player
    WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND ci.item_pos='invent' AND ci.is_alive=1"; 
try {
    $res=$database->mysql->query($query);
    if($res) {
       while($data=$res->fetch(PDO::FETCH_ASSOC)) {
            if($data['crafter_name']){
                $result['crafter_name'][]= $data['crafter_name'];
            } else {
                $result['crafter_name'][] = 0;
            }
       }
    }
    
} catch (PDOException $e){
    echo $e->getMessage();
}

/*

<hr>';
                if($result['crafter_name'][$i] && $result['crafter_name'][$i] != 0){
                    $inventPlayer.='Créé par : <span class="item-crafter-label">'.$result['crafter_name'][$i].'</span><br/>';
                }
                $inventPlayer.='Récolté par : <span class="item-harvester-label">'.$result['harvester_name'][$i].'</span>
                <br/>';

*/

/*inventPlayer = tableau de comparaison entre id table et id div
   [id_item]
       0 => 3442
       1 => 3443
       2 => 3444 ...
*/

$inventPlayer='';

$query="SELECT ci.id_city_item, ci.item_lvl, ci.item_dur, 
        i.item_name
    FROM city_item ci JOIN item i ON i.id_item = ci.id_item
    WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND ci.item_pos='invent' AND ci.is_alive=1 AND item_category <> 'farm' 
    ORDER BY FIELD(item_category, 'resource', 'food'), id_city_item DESC";
try {
    $res=$database->mysql->query($query);
    $result=[];
    if($res) {
       while($data=$res->fetch(PDO::FETCH_ASSOC)) {
            $result['id_city_item'][] = $data['id_city_item']; // toutes les lignes, accessibles avec $result['id_item'][$i]
            $result['item_name'][]= $data['item_name'];
            $result['item_lvl'][]= $data['item_lvl'];
            $result['item_dur'][]= $data['item_dur'];
       }
   }
} catch (PDOException $e){
    echo $e->getMessage();
}

$indexList=0; //index de l'id BDD dans la $listeID
$indexItems=0; //id de l'item dans la div
$inventsList=[]; // Crée la variable vide pour sizeof au cas où il n'y ait pas d'item dans l'inventaire.

if (isset($result['id_city_item'])){ //s'il y a au moins 1 objet dans l'inventaire
    $n = sizeof($result['id_city_item']);
    $_SESSION['player-invent-size'] = $n;
} else {
    $n=0;
    $_SESSION['player-invent-size'] = $n;
}

if(isset($result['id_city_item'])){
    foreach ($result['id_city_item'] as $tableIdItemKey)
    {
    $inventsList[$indexList] = $tableIdItemKey;
    $indexList++;
    }
} else {}

for($i=0;$i<$n;$i++) {
    //define the color of the durability div
    $d = $result['item_dur'][$i];
    switch($d){
        case ($d <= 10 && $d > 5):
            $color = '#84DE02';
            break;
        case ($d <= 5 && $d >= 3):
            $color = 'orange';
            break;
        case ($d < 3):
            $color = 'amaranth';
            break;
        default:
    }
    //drag & drop
    $inventPlayer.="<div id=".$indexItems." class='item item-".$result['item_name'][$i]."'>";
    //lvl and life displayed on item
    $inventPlayer.="<div class='item-life-box' style='width:".($result['item_dur'][$i]*10)."%; background-color:".$color.";'></div>";
    $inventPlayer.="<div class='item-lvl-box'><strong>".$result['item_lvl'][$i]."</strong></div>";
    //tooltip info
    $inventPlayer.='<span class="tooltiptext"><strong>'.L($result['item_name'][$i]).
                '</strong><br/>
                Niveau : '.$result['item_lvl'][$i].'
                <br/>
                Vie : <span class="item-dur-label">'.$result['item_dur'][$i].'</span>/10';
                //<hr>';
                /*if($result['crafter_name'][$i] && $result['crafter_name'][$i] != 0){
                    $inventPlayer.='Créé par : <span class="item-crafter-label">'.$result['crafter_name'][$i].'</span><br/>';
                }
                $inventPlayer.='Récolté par : <span class="item-harvester-label">'.$result['harvester_name'][$i].'</span> 
                <br/>'; */
    
                //Boutons d'action dans la tooltip
                if ($result['item_name'][$i] == ('orange')){
                    //$inventPlayer.='<hr><button class="eat-orange">Manger</button><br/>'; 
                    $inventPlayer.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                }
                else if ($result['item_name'][$i] == ('honey')){
                    //$inventPlayer.='<hr><button class="eat-honey">Manger</button><br/>'; 
                    $inventPlayer.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                }
                else if ($result['item_name'][$i] == ('baked_orange')){
                    //$inventPlayer.='<hr><button class="eat-baked_orange">Manger</button><br/>'; 
                    $inventPlayer.='<br/><br/>Clic droit : <strong>manger</strong>.'; 
                }
                else if ($result['item_name'][$i] == ('baked_honey')){
                    //$inventPlayer.='<hr><button class="eat-baked_honey">Manger</button><br/>'; 
                    $inventPlayer.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                }
                else if ($result['item_name'][$i] == ('cake')){
                    //$inventPlayer.='<hr><button class="eat-cake">Manger</button><br/>'; 
                    $inventPlayer.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                }
    $inventPlayer.='</span></div>';

    $indexItems++;
}

// Adds empty items to reach max-invent
// If sypply is master, add 20%
if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'supply_stat'){
    $maxInvent = $_SESSION['max-invent'] + floor($_SESSION['max-invent']*20/100);
    $emptyItemsToAdd = $maxInvent - sizeof($inventsList);
} else {
    $emptyItemsToAdd = $_SESSION['max-invent'] - sizeof($inventsList);
}

for($i=0; $i<$emptyItemsToAdd;$i++){
    $inventPlayer .= "<div class ='item item-void'></div>";
}

// Store the current invent size, for harvesting limit
$_SESSION['invent-size'] = sizeof($inventsList);


/************************************ */
/* ITEM QUANTITIES IN BUTTONS (plant) */
/************************************ */

$orangeCount=0;
$honeyCount=0;

// If there is no items returned
if(!isset($result['item_name']))
    $n=0;
else
    $n = sizeof($result['item_name']);
for ($i=0; $i<$n; $i++){
    //count orange ready quantity
    if($result['item_name'][$i]=='orange'){
        $orangeCount++;
    }
    //count honey ready quantity
    else if ($result['item_name'][$i]=='honey'){
        $honeyCount++;
    }
}

/************* */
/* INVENT FARM */
/************* */

$inventFarm=''; 

//Get the player's planted items
//if($_SESSION['player_area'] == 'inside'){

$query="SELECT cf.id_city_farm, 
            i.item_name, cf.item_lvl
            FROM city_farm cf JOIN item i ON i.id_item = cf.id_item
            WHERE cf.id_player=".$_SESSION['id_player']." AND cf.id_city=".$_SESSION['id_city']." AND is_alive = 1
            ORDER BY cf.id_city_farm DESC";

//} 

/* Pas de fermes dans les camps, sauf amèl éventuelle
else if ($_SESSION['player_area'] == 'camp'){

    $query="SELECT ci.id_city_item, i.item_name
        FROM city_item ci JOIN item i ON i.id_item = ci.id_item
        WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND ci.item_pos='city' AND ci.is_alive=1 AND id_camp=".$_SESSION['id_camp'];
}
*/

try {
    $res=$database->mysql->query($query);
    $result=[];
    if($res) {
       while($data=$res->fetch(PDO::FETCH_ASSOC)) {
           $result['id_city_farm'][] = $data['id_city_farm'];
           $result['item_name'][] = $data['item_name'];
           $result['item_lvl'][] = $data['item_lvl'];
       }
   }
} catch (PDOException $e){
    echo "Erreur dans la requête INVENT PLAYER de ctrl_forge.php";
}

if (isset($result['id_city_farm'])){ //if there is at least 1 item in farmland
   $n = sizeof($result['id_city_farm']);
} else {$n=0;}

if (isset($result['id_city_farm'])){ 
   foreach ($result['id_city_farm'] as $tableIdItemKey)
   {
       $inventsList[$indexList] = $tableIdItemKey;
       $indexList++;
   }
} else {}

for($i=0;$i<$n;$i++) {
   $inventFarm.="<div id=".$indexItems." class=item-".$result['item_name'][$i].">";
   $inventFarm.='<span class="tooltiptext"><strong>'.L($result['item_name'][$i]).
                '</strong><br/>
                Niveau : '.$result['item_lvl'][$i];
   $inventFarm.='</span></div>';
   $indexItems++;
}

//Create the session $listID array for all invents. Usable in ajax files.
$_SESSION['invent-list-id'] = $inventsList;
//var_dump($_SESSION['invent-list-id']);


/************************************** */
/* ITEM QUANTITIES IN BUTTONS (harvest) */
/************************************** */

$orangeReadyCount=0;
$honeyReadyCount=0;

// If there is no items returned
if(!isset($result['item_name']))
    $n=0;
else
    $n = sizeof($result['item_name']);


for ($i=0; $i<$n; $i++){
    //count orange ready quantity
    if($result['item_name'][$i]=='orange_tree'){
        $orangeReadyCount++;
    }
    //count honey ready quantity
    else if ($result['item_name'][$i]=='hive_honey'){
        $honeyReadyCount++;
    }
}


/************** */
/* ITEM HISTORY */
/************** */

$invent = new Invent();
$itemsList = $invent->getItemsHistory();
$itemsHistory = $invent->formatItemsHistory($itemsList);

/************** */
/* WATERING ALL */
/************** */

$isNotWatered = getUnwateredItemsList($database);

// Display the player's actions
$actionsListHTML = $action->getAllActions(); 
