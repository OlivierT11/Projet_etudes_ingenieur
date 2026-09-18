<?php



/******************* */
/* GET FOOD / MORALE */
/******************* */

$_SESSION['player-food-bar'] = 0;

$query="SELECT plb.player_food, plb.player_morale
    FROM player_life_bar plb 
    WHERE plb.id_player=".$_SESSION['id_player']."";
try {
    $res=$database->mysql->query($query);
    if($res) {
       while($data=$res->fetch(PDO::FETCH_ASSOC)) {
           $_SESSION['player-food-bar'] = $data['player_food'];
           $_SESSION['player-morale-bar'] = $data['player_morale'];
       }
   }
} catch (PDOException $e){
    echo "Erreur dans la requête SESSION de templates.php";
}

$styleFoodBarCurrent = '';
$styleFoodBarCurrent .= 'width:'.($_SESSION['player-food-bar']);
$styleMoraleBarCurrent = '';
$styleMoraleBarCurrent .= 'width:'.($_SESSION['player-morale-bar']);


/********************** */
/* GET EQUIPMENT VALUES */
/********************** */

if(isset($_SESSION['player_shield'])) { // ?????
    unset($_SESSION['player_shield']);
}
if(isset($_SESSION['player_upper'])) {
    unset($_SESSION['player_upper']);
}
if(isset($_SESSION['player_lower'])) {
    unset($_SESSION['player_lower']);
}
if(isset($_SESSION['player_mask'])) {
    unset($_SESSION['player_mask']);
}
if(isset($_SESSION['player_helmet'])) {
    unset($_SESSION['player_helmet']);
}

// Get the durability of every equiped item (sessions used in ajax_battle.php)
// -> session invent or this (c'est le découplage)
$query = 'SELECT item_dur, item_lvl
    FROM city_item
WHERE id_player='.$_SESSION['id_player'].' AND item_pos = :pos AND is_alive=1 LIMIT 1';
$sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

$sth->execute(array(':pos' => "upper"));
$res = $sth->fetchAll();
if(!empty($res)){ //execute renvoie un tableau vide si aucune valeur n'est trouvée dans la BDD
    $_SESSION['player_upper_dur']= $res[0]['item_dur'];
    $_SESSION['player_upper_lvl']= $res[0]['item_lvl'];
} else {
    $_SESSION['player_upper_dur']=0;
    $_SESSION['player_upper_lvl']=0;
}

$sth->execute(array(':pos' => "lower"));
$res = $sth->fetchAll();
if(!empty($res)){
    $_SESSION['player_lower_dur']= $res[0]['item_dur'];
    $_SESSION['player_lower_lvl']= $res[0]['item_lvl'];
} else {
    $_SESSION['player_lower_dur']=0;
    $_SESSION['player_lower_lvl']=0;
}

$sth->execute(array(':pos' => "helmet"));
$res = $sth->fetchAll();
if(!empty($res)){
    $_SESSION['player_helmet_dur']= $res[0]['item_dur'];
    $_SESSION['player_helmet_lvl']= $res[0]['item_lvl'];
} else {
    $_SESSION['player_helmet_dur']=0;
    $_SESSION['player_helmet_lvl']=0;
}

$sth->execute(array(':pos' => "mask"));
$res = $sth->fetchAll();
if(!empty($res)){
    $_SESSION['player_mask_dur']= $res[0]['item_dur'];
    $_SESSION['player_mask_lvl']= $res[0]['item_lvl'];
} else {
    $_SESSION['player_mask_dur']=0;
    $_SESSION['player_mask_lvl']=0;
}

$sth->execute(array(':pos' => "shield"));
$res = $sth->fetchAll();
if(!empty($res)){
    $_SESSION['player_shield_dur']= $res[0]['item_dur'];
    $_SESSION['player_shield_lvl']= $res[0]['item_lvl'];
} else {
    $_SESSION['player_shield_dur']=0;
    $_SESSION['player_shield_lvl']=0;
}

$sth->execute(array(':pos' => "spear"));
$res = $sth->fetchAll();
if(!empty($res)){
    $_SESSION['player_spear_dur']= $res[0]['item_dur'];
    $_SESSION['player_spear_lvl']= $res[0]['item_lvl'];
} else {
    $_SESSION['player_spear_dur']=0;
    $_SESSION['player_spear_lvl']=0;
}

//display life bars
$styleShieldBarCurrent = '';
$styleShieldBarCurrent .= 'width:'.($_SESSION['player_shield_dur']*10);

$styleUpperBarCurrent = '';
$styleUpperBarCurrent .= 'width:'.($_SESSION['player_upper_dur']*10);
$styleLowerBarCurrent = '';
$styleLowerBarCurrent .= 'width:'.($_SESSION['player_lower_dur']*10);
$styleHelmetBarCurrent = '';
$styleHelmetBarCurrent .= 'width:'.($_SESSION['player_helmet_dur']*10);
$styleMaskBarCurrent = '';
$styleMaskBarCurrent .= 'width:'.($_SESSION['player_mask_dur']*10);

$styleSpearBarCurrent = '';
$styleSpearBarCurrent .= 'width:'.($_SESSION['player_spear_dur']*10);


// Kill the player when the food / morale bar is 0 or below
if($_SESSION['player-food-bar'] <= 0)
{
    if (isset($_SESSION['hit_by_wave']) && $_SESSION['hit_by_wave'] == true)
    {
        $_SESSION['death_reason'] = 'wave';
        unset($_SESSION['hit_by_wave']);
    }
    else
    {
        $_SESSION['death_reason'] = "food";
    }

    header("Location: index.php?page=death");
    die();
}
if($_SESSION['player-morale-bar'] <= 0)
{
    if (isset($_SESSION['hit_by_wave']) && $_SESSION['hit_by_wave'] == true)
    {
        $_SESSION['death_reason'] = 'wave';
        unset($_SESSION['hit_by_wave']);
    }
    else
    {
        $_SESSION['death_reason'] = "morale";
    }

    header("Location: index.php?page=death");
    die();
}

// If we have been hit by the wave (cron.php) but are still alive, unset the session
if (isset($_SESSION['hit_by_wave']) && $_SESSION['hit_by_wave'] == true)
{
    unset($_SESSION['hit_by_wave']);
}