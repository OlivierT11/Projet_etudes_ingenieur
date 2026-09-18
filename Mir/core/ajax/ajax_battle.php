<?php
// aller chercher les stats à modifier dans la DB (force monstre, stats joueurs, stat équipement)
// Calculer en backend
// Renvoyer les nouvelles données et les afficher

if(!isset($_SESSION))
    session_start();
    
if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
    die();
}

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

require_once dirname(dirname(__FILE__))."/class/class.i18n.php";
$langPath = dirname(dirname(__FILE__)).'/i18n/lang/lang_{LANGUAGE}.ini';
$langCachePath = dirname(dirname(__FILE__)).'/i18n/langcache/';
$i18n = new i18n($langPath, $langCachePath, 'fr');
$i18n->init();


#endregion



/**
 * 
 * Logique
 * 
 * Définit si le joueur peut combattre (il y a des monstres sur la case et pas assez de joueurs qui combattent déjà)
 *  Compte le nombre d'ennemis selon la zone de combat (outside, dj...)
 *  Il n'y a pas de monstres ? Ne pas combattre et renvoyer un message d'info
 *  Compte le nombre de joueurs qui combattent
 *  Retire 1 si le joueur est déja en combat pour ne pas être compté comme un autre joueur.
 *  TODO : dans le calcul de la chaine foe_hp, vérifier la position du char souhaitée. Si elle est plus grande que la taille de la chaine, il y a eu collision dans le script.
 * 
 * Le joueur est maintenant en combat.
 *  Choix de l'ennemi à attaquer, et definition de l'atk/def/HP du monstre
 *   Si 1ere boucle du combat, il n'y a pas de pos en mémoire, dont on prend le 1er ennemi supérieur au nombre d'alliés dont les pv sont plus grand que 0.
 *    Sinon, garder le même ennemi.
 *  Calcul de la force du monstre
  *     à l'extérieur, la force du monstre est la distance à la ville (0,0)
 *      Dans un donjon, tous les monstres ont la même force, car les joueurs sont sensés mettre 1-2 jours pour terminer un étage de 20*20 cases.
 *           Ils ont le niveau de la case de carte sur laquelle est l'entrée du donjon.
 *      En enfer, les monstres ont le niveau du plus profond de l'abysse, soit le niveau max de la stat d'attaque atteignable par un joueur à l'ouverture de l'abysse, +18lvl.
 *          Les bonus bâtiments ne sont pas pris en compte (craft +10)
 *  Définit la durabilité de l'équipement du joueur avant la bataille
 *  Vérifier si le joueur porte des équipements
 *  Aller chercher les stats du joueur
 * 
 * Début du combat
 *      le monstre commence et attaque le joueur
 *          Choix de la pièce à attaquer, si aucun bouclier. Sinon, attaquer le bouclier
 *          Le joueur a-t-il succombé à l'attaque ?
 *              Si oui, Le joueur ne combat plus
 *            Le joueur perd ses items
 *            Le joueur perd de la faim
 *            Le joueur est renvoyé en ville
 *          Si le joueur n'est pas mort, le monstre attaque la partie de l'équipement choisie
 * 
 *      Attaque
 *          A ce moment, le joueur a subit une attaque, sa mort est testée, et les stats de l'équipement sont actualisées.
 *           Si le joueur a résisté à l'attaque, il attaque le monstre
 *           Réduit la durabilité de la lance équipée si elle existe.
 * 
 * 
 * Fin du combat
 *  Si le monstre est mort
 *  Le joueur n'est plus en combat
 *  Il y a un monstre en moins sur la carte
 *  ...
 *  Le joueur loot-il du métal ? (1% de chances, 100% si DJ2) ajouter le métal looté à l'inventaire
 * 
 *  Sessions
 *  $_SESSION['posFoeInString'] : le monstre à affronter sur la case. Réinitialisé si le joueur fuit, meurt ou gagne le combat.
 *      Permet de combattre le même monstre à chaque TIC.
 *      Calculé par rapport au nombre de monstres en vie sur la case MOINS le nombre de joueurs qui combattent déjà.
 * 
 *  $_SESSION['is_fighting'] : le joueur est en train de combattre. Permet de ne pas recalculer posFoeInString. Réinitialisé si le joueur fuit, meurt ou gagne le combat.
 *
 */


//sessions
$masterStat = $_SESSION['master_stat'];
$isMaster = $_SESSION['is_master'];

// This file uses a 'response' array to send to ajax.
/*
respone[0] = response string
response[1] = foeHp
response[2] = dmg dealt (tic)
response[3] = dmg received (tic)
response[4] = equipment hit (tic)
response[5] = player name for Action
response[6] = Action date
response[7] = new armor durability to update item dur real time;
response[8] = new spear durability to update item dur real time;
response[9] = debug

*/
$response = [];
$response[0] = '';
$response[1] = 0;
$response[2] = 0;
$response[3] = 0;
$response[4] = 0;
$response[5] = $_SESSION['player_name'];
$response[6] = date("H:i");
$response[7] = 0;

$battleHasEnded = 0;


if(!isset($_SESSION))
    session_start();

/*
//stat up (see README)
function updatePlayerAtkStat($database, $xp){

    // Check if the daily maximum is reached
    if($_SESSION['player_stat']['atk_stat']['player_stat_lvl'] >= $_SESSION['max_possible_lvl'])
        return false;

    // Define the XP to add if the max lvl is very high compared to the current lvl.
    if(isset($_SESSION['xpGainPercent'])){
        $xp += floor($xp*($_SESSION['xpGainPercent']['Combat']/100));
    }

    // Update the stat lvl and xp in DB
    $atk_stat_xp =  $_SESSION['player_stat']['atk_stat']['player_stat_xp'];
    $atk_stat_lvl = $_SESSION['player_stat']['atk_stat']['player_stat_lvl'];
    $atk_stat_xp += $xp;
    if ($atk_stat_xp >= 100){
        $atk_stat_xp -= 100;
        $atk_stat_lvl += 1;
        //postAction($database, 'lvlup', null, 'Combat - Attaque');
        postAction($database, 'lvlup', null, 'Combat');
    }
    $query = 'UPDATE player_stat SET player_stat_xp = '.$atk_stat_xp.', player_stat_lvl = '.$atk_stat_lvl.'  
                WHERE id_player='.$_SESSION['id_player'].' AND id_stat = 2'; //id stat atk
    try {
        $database->mysql->query($query);

        //update sessions to match DB
        $_SESSION['player_stat']['atk_stat']['player_stat_xp']  = $atk_stat_xp;
        $_SESSION['player_stat']['atk_stat']['player_stat_lvl'] = $atk_stat_lvl;

    } catch (PDOException $e){
        $e->getMessage();
        return false;
    }
}

//stat up (see README)
function updatePlayerDefStat($database, $xp){

    // Check if the daily maximum is reached
    if($_SESSION['player_stat']['def_stat']['player_stat_lvl'] >= $_SESSION['max_possible_lvl'])
        return false;

    // Define the XP to add if the max lvl is very high compared to the current lvl.
    if(isset($_SESSION['xpGainPercent'])){
        $xp += floor($xp*($_SESSION['xpGainPercent']['Combat']/100));
    }

    // Update the stat lvl and xp in DB
    $def_stat_xp =  $_SESSION['player_stat']['def_stat']['player_stat_xp'];
    $def_stat_lvl = $_SESSION['player_stat']['def_stat']['player_stat_lvl'];
    $def_stat_xp += $xp;
    if ($def_stat_xp >= 100){
        $def_stat_xp -= 100;
        $def_stat_lvl += 1;
        //postAction($database, 'lvlup', null, 'Combat - Défense');
    }
    $query = 'UPDATE player_stat SET player_stat_xp = '.$def_stat_xp.', player_stat_lvl = '.$def_stat_lvl.'  
                WHERE id_player='.$_SESSION['id_player'].' AND id_stat = 3'; //id stat def
    try {
        $database->mysql->query($query);

        //update sessions to match DB
        $_SESSION['player_stat']['def_stat']['player_stat_xp']  = $def_stat_xp;
        $_SESSION['player_stat']['def_stat']['player_stat_lvl'] = $def_stat_lvl;

    } catch (PDOException $e){
        $e->getMessage();
        return false;
    }
}
*/

if (isset($_REQUEST["action"]) && $_REQUEST["action"]=="fight") {

    // Begins the transaction for the entire file.
    $database->mysql->beginTransaction();
    
    //Définit si le joueur peut combattre (il y a des monstres et pas assez de joueurs qui combattent déjà)
        
    //Compte le nombre d'ennemis selon la zone de combat (outside, dj...).
    //TODO : amel SESSION. unset quand session(is_fighting)=0
    switch($_SESSION['player_area']){
        case 'outside':
            $query="SELECT foe, foe_hp FROM map
            WHERE x=".$_SESSION['player_pos_x']." AND y=".$_SESSION['player_pos_y']." AND id_city=".$_SESSION['id_city'];
        break;
        case 'dj':
            $query="SELECT dj_foe as foe, dj_foe_hp as foe_hp FROM dj
            WHERE dj_x=".$_SESSION['player_pos_x']." AND dj_y=".$_SESSION['player_pos_y']." AND id_dj=".$_SESSION['id_dj'];
            break;
        case 'abyss':
            $query="SELECT abyss_foe as foe, abyss_foe_hp as foe_hp FROM abyss
            WHERE abyss_x=".$_SESSION['player_pos_x']." AND abyss_y=".$_SESSION['player_pos_y']." AND id_abyss=".$_SESSION['id_city'];
            break;
        case 'dj2':
            $query="SELECT dj2_foe as foe, dj2_foe_hp as foe_hp FROM dj2
            WHERE dj2_x=".$_SESSION['player_pos_x']." AND dj2_y=".$_SESSION['player_pos_y']." AND id_dj2=".$_SESSION['id_dj'];
            break;
        case 'hell':
            $query="SELECT hell_foe as foe, hell_foe_hp as foe_hp FROM hell
            WHERE hell_x=".$_SESSION['player_pos_x']." AND hell_y=".$_SESSION['player_pos_y']." AND id_hell=".$_SESSION['id_city'];
            break;
        default:
            echo 'player area non reconnue';
    }
    $result=[];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $result['foe_nbr'] = $data['foe'];
                $result['foe_hp'] = $data['foe_hp']; //string(34612AA3A...)
            }
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    // Il n'y a pas de monstres ? Ne pas combattre.
    if ($result['foe_nbr'] == 0)
    {
        $database->mysql->rollBack();
        echo "nofoe";
        //response[0] = 'nofoe';
        //echo response;
        die();
    }

    //définit le nom de la table pour les requêtes
    switch($_SESSION['player_area']){
        case 'outside':
            $areaBDD = 'map';
        break;
        default:
           $areaBDD = $_SESSION['player_area'];
    }

    //Compte le nombre de joueurs qui combattent (autres que le joueur)
    $query="SELECT COUNT(id_player) FROM player
        WHERE is_fighting = 1 AND player_pos_x=".$_SESSION['player_pos_x'];
    $query .= " AND player_pos_y=".$_SESSION['player_pos_y']." AND id_city=".$_SESSION['id_city']." AND player_area='".$areaBDD."'";
    $result['players_fighting'] = $database->mysql->query($query)->fetchColumn();

    // Retire 1 si le joueur est déja en combat pour ne pas être compté comme un autre joueur.
    if(isset($_SESSION['is_fighting']) && $_SESSION['is_fighting'] == 1){ 
        $result['players_fighting'] -= 1;
    }

    // Il ne peut pas y avoir moins de 0 joueurs qui combattent !
    if($result['players_fighting'] < 0)
        $result['players_fighting'] = 0;

    // Vérifie si le combat est possible
    if ($result['players_fighting'] >= $result['foe_nbr'])
    {
        $database->mysql->rollBack();
        echo "allFight";
        die();
    }

    // Le joueur est maintenant en combat. A mettre le plus près possible du début du script, pour éviter la collision si plusieurs joueurs combattent sur la même case.
    if(!isset($_SESSION['is_fighting']) || $_SESSION['is_fighting'] == 0){
        $_SESSION['is_fighting']=1;
        $query="UPDATE player SET is_fighting=1
            WHERE id_player=".$_SESSION['id_player'];
        try
        { 
            $res = $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }
    }

    //TODO : dans le calcul de la chaine foe_hp, vérifier la position du char souhaitée. Si elle est plus grande que la taille de la chaine, il y a eu collision.
    // SI collision au centre de la chaine, alors 2 joueurs combattent le même monstre, pas de pb majeur.

    //Choix de l'ennemi à attaquer, et atk/def/HP du monstre
    $array_foe_hp = str_split($result['foe_hp']);


    // Si 1ere boucle du combat, il n'y a pas de pos en mémoire, dont on prend le 1er ennemi supérieur au nombre d'alliés dont les pv sont plus grand que 0.
    // Sinon, garder le même ennemi.
    if(!isset($_SESSION['posFoeInString'])) {
        $posInString = $result['players_fighting'];
        $foeHp = $array_foe_hp[ $result['players_fighting'] ]; //1er foe dispo (indice 4 si 4 joueurs combattent)
        if($foeHp == '0'){
            while($foeHp == '0' && isset($array_foe_hp[ $posInString ])){ //isset avoid unimited loop
                $posInString += 1;
                $foeHp = $array_foe_hp[ $posInString ]; //1er foe dispo (indice 4 si 4 joueurs combattent)
            }
        }
    }
    else {
        $posInString = $_SESSION['posFoeInString'];
        $foeHp = $array_foe_hp[ $posInString ];
    }

    // Store the string position in session, or the player can fight a new foe on each setinterval tic.
    $_SESSION['posFoeInString'] = $posInString;
    
    if ($foeHp == 'A'){
        $foeHp = 10;
    } else {
        $foeHp = (int)$foeHp;
    }

    //Calcul de la force du monstre
    // à l'extérieur, la force du monstre est la distance à la ville (0,0)
    if($_SESSION['player_area']=='outside'){
        $foeAtk = $foeDef = floor( sqrt( pow($_SESSION['player_pos_x'],2)+ pow($_SESSION['player_pos_y'],2) ) );
    }

    // Dans un donjon, tous les monstres ont la même force, car les joueurs sont sensés mettre 1-2 jours pour terminer un étage de 20*20 cases.
    // Ils ont le niveau de la case de carte sur laquelle est l'entrée du donjon.
    else if($_SESSION['player_area']=='dj'){

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
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }
        $foeAtk = $foeDef = floor( sqrt( pow($_SESSION['x']-result['x'],2) + pow($_SESSION['y']-result['y'], 2) ) );
    }
    
    // Dans une abysse, tous les monstres ont la même force, car les joueurs sont sensés mettre 1-2 jours pour terminer un étage de 20*20 cases.
    // Ils ont le niveau du maximum de la stat d'attaque en cours quand l'abysse s'ouvre. Le niveau augmente de 1 par unité de profondeur. Soit 1*18 = 18lvl à faire en 4 jours.
    else if($_SESSION['player_area']=='abyss'){

        // détermine la valeur max possible de la statistique d'attaque des joueurs à l'ouverture de l'abysse (4 jours avant la fin)
        $maxStat = 10 + (MAX_CITY_DAYS - 4) * 5;

        $foeAtk = $foeDef = $maxStat;
    }

    // En enfer, les monstres ont le niveau du plus profond de l'abysse, soit le niveau max de la stat d'attaque atteignable par un joueur à l'ouverture de l'abysse, +18lvl.
    // Les bonus bâtiments ne sont pas pris en compte (craft +10)
    else if($_SESSION['player_area']=='hell'){
        
        $maxStat = 10 + (MAX_CITY_DAYS - 4) * 5;

        $foeAtk = $foeDef = $maxStat + ABYSS_DEEPNESS;
    }
    

    //Définit la durabilité de l'équipement du joueur avant la bataille
    $shieldDur = $upperDur = $lowerDur = $maskDur = $helmetDur = $spearDur = 0;
    $shieldLvl = $upperLvl = $lowerLvl = $maskLvl = $helmetLvl = $spearLvl = 0;

    // Si le joueur est équipé des différents équipements
    //sessions définies dans ctrl_life_bars.php
    if (isset($_SESSION['player_shield_dur'])){
        $shieldDur = $_SESSION['player_shield_dur'];
        $shieldLvl = $_SESSION['player_shield_lvl'];
    }
    if (isset($_SESSION['player_upper_dur'])){
        $upperDur = $_SESSION['player_upper_dur'];
        $upperLvl = $_SESSION['player_upper_lvl'];
    }
    if (isset($_SESSION['player_lower_dur'])){
        $lowerDur = $_SESSION['player_lower_dur'];
        $lowerLvl = $_SESSION['player_lower_lvl'];
    }
    if (isset($_SESSION['player_helmet_dur'])){
        $helmetDur = $_SESSION['player_helmet_dur'];
        $helmetLvl = $_SESSION['player_helmet_lvl'];
    }
    if (isset($_SESSION['player_mask_dur'])){
        $maskDur = $_SESSION['player_mask_dur'];
        $maskLvl = $_SESSION['player_mask_lvl'];
    }
    if (isset($_SESSION['player_spear_dur'])){
        $spearDur = $_SESSION['player_spear_dur'];
        $spearLvl = $_SESSION['player_spear_lvl'];
    }

    //aller chercher les stats du joueur
    //définis dans ctrl_login.php
    $playerStatAtk = $_SESSION['player_stat']['atk_stat']['player_stat_lvl'];
    $playerStatDef = $_SESSION['player_stat']['def_stat']['player_stat_lvl'];

    /* *************** */
    /* Début du combat */
    /* *************** */

    $foeIsDead = $playerIsDead = 0;
    $partToAtk = '';
    

    //le monstre commence et attaque le joueur

    //Choix de la pièce à attaquer, si aucun bouclier. Sinon, attaquer le bouclier
    if($shieldDur != 0){
        $partToAtk = 'shield';
    } else {
        $n = rand(0,3);
        switch($n){
            case 0:
                $partToAtk='upper';
                if ($upperDur == 0){
                    $playerIsDead = 1;
                    $_SESSION['death_reason'] = "fightUpper";
                }
                break;
            case 1:
                $partToAtk='lower';
                if ($lowerDur == 0){
                    $playerIsDead = 1;
                    $_SESSION['death_reason'] = "fightLower";
                }
                break;
            case 2:
                $partToAtk='helmet';
                if ($helmetDur == 0){
                    $playerIsDead = 1;
                    $_SESSION['death_reason'] = "fightHead";
                }
                break;
            case 3:
                $partToAtk='mask';
                if ($maskDur == 0){
                    $playerIsDead = 1;
                    $_SESSION['death_reason'] = "fightMask";
                }
                break;
        }
    }

    $response[4] = L($partToAtk);

    // Le joueur a-t-il succombé à l'attaque ?
    if ($playerIsDead == 1){

        $_SESSION['is_fighting'] = 0;

        // Le joueur ne combat plus
        $query="UPDATE player SET is_fighting=0
            WHERE id_player=".$_SESSION['id_player'];
        try {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        // Le joueur est renvoyé en ville
        $query="UPDATE player SET player_pos_x=0, player_pos_y=0, player_area='inside', id_area=0, id_abyss=0, id_dj=0, prev_dir=''
            WHERE id_player =".$_SESSION['id_player'];
        try {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        // Le joueur perd de la faim et du moral
        $food = $_SESSION['player-food-bar'];
        $food > 20 ? ($food=20) : ($food=5);
        $morale = $_SESSION['player-morale-bar'];
        $morale > 20 ? ($morale=20) : ($morale=5);

        $query="UPDATE player_life_bar SET player_food=".$food.", player_morale=".$morale."
            WHERE id_player =".$_SESSION['id_player'];
        try {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        // delete all equipement and items
        $query="UPDATE city_item SET is_alive=0
            WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }
        
        // Finally returns "die" to ajax request and end the script.
        $database->mysql->commit();
        echo "die";
        die();
    }

    //Si le joueur n'est pas mort, le monstre attaque la partie de l'équipement choisie
    switch ($partToAtk){
        case 'shield':
            $playerDef = floor(($playerStatAtk + $shieldLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat'){
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0){
                $lostDur = ($foeAtk - $playerDef);
            // Si le joueur est plus fort que le monstre, il son équipement perd quand même de la durabilité.
            } else {
                $lostDur = 1;
            }
            $shieldDur -= $lostDur;
            $_SESSION['player_shield_dur'] = $shieldDur;
        
            $action->postAction('def-shield', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $shieldDur;

            // Si l'équipement attaqué est détruit par l'attaque, renseigner la BDD
            if ($shieldDur <= 0){
                $_SESSION['player_shield_lvl'] = 0;
                $_SESSION['player_shield_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='shield' AND is_alive=1";
            }
            // Sinon, update seulement la durabilité
            else {
                $query="UPDATE city_item SET item_dur=".$shieldDur."
                WHERE id_player=".$_SESSION['id_player']." AND item_pos='shield' AND is_alive=1";
            }
        break;
        case 'upper':
            $playerDef = floor(($playerStatAtk + $upperLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat'){
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0){
                $lostDur = ($foeAtk - $playerDef);
            } else {
                $lostDur = 1;
            }
            $upperDur -= $lostDur;
            $_SESSION['player_upper_dur'] = $upperDur;
            $action->postAction('def-upper', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $upperDur;

            if ($upperDur <= 0){
                $_SESSION['player_upper_lvl'] = 0;
                $_SESSION['player_upper_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='upper' AND is_alive=1";
            } else {
                $query="UPDATE city_item SET item_dur=".$upperDur."
                    WHERE id_player=".$_SESSION['id_player']." AND item_pos='upper' AND is_alive=1";
            }
        break;
        case 'lower':
            $playerDef = floor(($playerStatAtk + $lowerLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat'){
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0){
                $lostDur = ($foeAtk - $playerDef);
            } else {
                $lostDur = 1;
            }
            $lowerDur -= $lostDur;
            $_SESSION['player_lower_dur'] = $lowerDur;
            $action->postAction('def-lower', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $lowerDur;

            if ($lowerDur <= 0){
                $_SESSION['player_lower_lvl'] = 0;
                $_SESSION['player_lower_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='lower' AND is_alive=1";
            } else {
                $query="UPDATE city_item SET item_dur=".$lowerDur."
                WHERE id_player=".$_SESSION['id_player']." AND item_pos='lower' AND is_alive=1";
            }
        break;
        case 'helmet':
            $playerDef = floor(($playerStatAtk + $helmetLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat'){
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0){
                $lostDur = ($foeAtk - $playerDef);
            } else {
                $lostDur = 1;
            }
            $helmetDur -= $lostDur;
            $_SESSION['player_helmet_dur'] = $helmetDur;
            $action->postAction('def-helmet', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $helmetDur;

            if ($helmetDur <= 0){
                $_SESSION['player_helmet_lvl'] = 0;
                $_SESSION['player_helmet_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='helmet' AND is_alive=1";
            } else {
                $query="UPDATE city_item SET item_dur=".$helmetDur."
                WHERE id_player=".$_SESSION['id_player']." AND item_pos='helmet' AND is_alive=1";
            }

        break;
        case 'mask':
            $playerDef = floor(($playerStatAtk + $maskLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat'){
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0){
                $lostDur = ($foeAtk - $playerDef);
            } else {
                $lostDur = 1;
            }
            $maskDur -= $lostDur;
            $_SESSION['player_mask_dur'] = $maskDur;
            $action->postAction('def-mask', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $maskDur;

            if ($maskDur <= 0){
                $_SESSION['player_mask_lvl'] = 0;
                $_SESSION['player_mask_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='mask' AND is_alive=1";
            } else {
                $query="UPDATE city_item SET item_dur=".$maskDur."
                WHERE id_player=".$_SESSION['id_player']." AND item_pos='mask' AND is_alive=1";
            }
        break;
    }
    try
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    $response[3] = $lostDur;
        
    /******** */
    /* Attack */
    /******** */

    //A ce moment, le joueur a subit une attaque, sa mort est testée, et les stats de l'équipement sont actualisées.

    //Si le joueur a résisté à l'attaque, il attaque le monstre
    $playerAtk = floor(($playerStatAtk + $spearLvl) / 2); //$spearLvl = 0 si non equipé

    // Add the +20% Master bonus
    if($isMaster == 1 && $masterStat == 'atk_stat'){
        $playerAtk += floor($playerAtk*20/100);
    }

    //foe hp
    if(($foeDef - $playerAtk) > 0){
        $lostHp = ($foeDef - $playerAtk);
    } else {
        $lostHp = 1;
    }
    $foeHp -= $lostHp;

    $response[2] = $lostHp;
    
    // "Vous infligez X dégâts à l'Ombre"
    $action->postAction('atk', 0,0,0,0,0,0,0, $lostHp);

    // Renvoyer les nouveaux PV du monstre pour actualiser la barre de vie.
    //echo $foeHp;
    $response[1] = $foeHp;

    // Réduit la durabilité de la lance équipée si elle existe.
    if($spearDur > 0){
        if($foeDef - $playerAtk > 0){
            $lostDur = ($foeDef - $playerAtk);
        // Si le joueur est plus fort que le monstre, il son équipement perd quand même de la durabilité.
        } else {
            $lostDur = 1;
        }
        $spearDur -= $lostDur;
        $action->postAction('atk-spear', 0, 0, 0, 0, 0, 0, 0, $lostDur);
        $response[8] = $spearDur;

        // Si la lance est brisée
        if ($spearDur <= 0){
            $_SESSION['player_spear_lvl'] = 0;
            $_SESSION['player_spear_dur'] = 0;
            $query="UPDATE city_item SET item_pos='', is_alive=0
                WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='spear' AND is_alive=1";
            //TOBEDONE postaction : votre lance est brisée
        } else {
            //update la vie de la lance
            $_SESSION['player_spear_dur'] = $spearDur;
            $query="UPDATE city_item SET item_dur=".$spearDur."
                WHERE id_player=".$_SESSION['id_player']." AND item_pos='spear' AND is_alive=1
                LIMIT 1";
        }
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }
    }

    //update foe_hp
    //mettre le char qui correspond aux PV du monstre à 0 (ne pas supprimer le char, car si un autre joueur modifie la chaine avant nous, la pos du char dans la string sera modifiée)
    //en plus ça donne le nombre de monstres initial pour le respawn !

    //hexa
    if($foeHp==10){
        $foeHp='A';
    }
    else if($foeHp<0){
        $foeHp='0';
    }

    // Crée la nouvelle chaine foeHP pour la case avec la nouvelle valeur des PV du monstre affronté.
    $newFoeHpList = substr_replace($result['foe_hp'], $foeHp, $posInString, 1); //fct can do many things https://www.php.net/manual/en/function.substr-replace.php

    switch($_SESSION['player_area']){
        case 'outside':
            $query="UPDATE map SET foe_hp='".$newFoeHpList."'
                WHERE id_city=".$_SESSION['id_city']." AND x=".$_SESSION['player_pos_x']." AND y=".$_SESSION['player_pos_y'];
        break;
        case 'dj':
            $query="UPDATE dj SET dj_foe_hp='".$newFoeHpList."'
                 WHERE id_dj=".$_SESSION['id_city']." AND dj_x=".$_SESSION['player_pos_x']." AND dj_y=".$_SESSION['player_pos_y'];
            break;
        case 'abyss':
            $query="UPDATE abyss SET abyss_foe_hp='".$newFoeHpList."'
                WHERE id_abyss=".$_SESSION['id_city']." AND abyss_x=".$_SESSION['player_pos_x']." AND abyss_y=".$_SESSION['player_pos_y'];
            break;
        case 'dj2':
            $query="UPDATE dj2 SET dj2_foe_hp='".$newFoeHpList."'
                WHERE id_dj2=".$_SESSION['id_city']." AND dj2_x=".$_SESSION['player_pos_x']." AND dj2_y=".$_SESSION['player_pos_y'];
            break;
        case 'hell':
            $query="UPDATE hell SET hell_foe_hp='".$newFoeHpList."'
                WHERE id_hell=".$_SESSION['id_city']." AND hell_x=".$_SESSION['player_pos_x']." AND hell_y=".$_SESSION['player_pos_y'];
            break;
        default:
            echo 'player area non reconnue';
    }
    try
    {
        $database->mysql->query($query);
        //echo $newFoeHpList;
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "error";
        die();
    }

    //test mort foe
    if($foeHp <= 0){

        // Fin du combat

        $foeHp = 0; //pour BDD

        // Le joueur n'est plus en combat, supprimer la session (fait aussi dans model.php, car tout changement de page pendant un combat est une fuite).
        // Ce n'est pas obligatoire ici car la session est actualisée à chaque clic sur le bouton combat.
        // Necessaire dans model.php pour éviter qu'un joueur n'arrive sur une nouvelle case avec cette session existante.
        unset($_SESSION['posFoeInString']);
    
        // Le joueur n'est plus en combat
        $query="UPDATE player SET is_fighting=0
            WHERE id_player=".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['is_fighting'] = 0; //Tester au changement de case pour repérer si le joueur fuit (getPosX())
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        // Il y a un monstre en moins sur la carte
        switch($_SESSION['player_area']){
            case 'outside':
                $query="UPDATE map SET foe = foe-1
                WHERE id_city=".$_SESSION['id_city']." AND x=".$_SESSION['player_pos_x']." AND y=".$_SESSION['player_pos_y'];
            break;
            case 'dj':
                $query="UPDATE dj SET dj_foe = dj_foe-1
                WHERE id_city=".$_SESSION['id_city']." AND dj_x=".$_SESSION['player_pos_x']." AND dj_y=".$_SESSION['player_pos_y'];
                break;
            case 'abyss':
                $query="UPDATE abyss SET abyss_foe = abyss_foe-1
                WHERE id_city=".$_SESSION['id_city']." AND abyss_x=".$_SESSION['player_pos_x']." AND abyss_y=".$_SESSION['player_pos_y'];
                break;
            case 'dj2':
                $query="UPDATE dj2 SET dj2_foe = dj2_foe-1
                WHERE id_city=".$_SESSION['id_city']." AND dj2_x=".$_SESSION['player_pos_x']." AND dj2_y=".$_SESSION['player_pos_y'];
                break;
            case 'hell':
                $query="UPDATE hell SET hell_foe = hell_foe-1
                WHERE id_city=".$_SESSION['id_city']." AND hell_x=".$_SESSION['player_pos_x']." AND hell_y=".$_SESSION['player_pos_y'];
                break;
            default:
                echo 'player area non reconnue';
        }
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "error";
            die();
        }

        // Le joueur loot-il du métal ? (1% de chances, 100% si DJ2)
        $lootMetal = false;
        if($_SESSION['player_area'] == 'dj2'){
            $lootMetal = true;
        } else {
            $m = rand(1,100);
            if($m == 100){
                $lootMetal = true;
            }
        }

        // ajouter le métal looté à l'inventaire
        if($lootMetal){

            // If sypply is master, add 20%
            if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'supply_stat'){
                $maxInvent = $_SESSION['max-invent'] + floor($_SESSION['max-invent']*20/100);
            } else {
                $maxInvent = $_SESSION['max-invent'];
            }

            //vérifier la place restante dans l'inventaire
            if($_SESSION['player-invent-size'] >= $maxInvent){
                $database->mysql->rollBack();
                echo "Inventaire plein.";
                die();
            }

            $query="INSERT INTO city_item(id_city, id_player, id_item, item_lvl, item_pos) VALUE(".$_SESSION['id_city'].", ".$_SESSION['id_player'].", 20, 999, 'invent')";
            try
            {
                $database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de ajax_battle.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "error";
                die();
            }
            $lootMetal = false;
            $statistic->updatePlayerStatistic('metal_looted', 1);
            $action->postAction('loot-metal');
        }

        $statistic->updatePlayerStatistic('foe_killed', 1);

        $battleHasEnded = 1;
        //response[0] = 'endfight';
        //echo response;
    }
    

    //bouclage sur attaque
    //sortie boucle = mort du joueur OU foe_hp = O
    
    //update les stats dans la BDD
    //et les sessions

    //player stops fighting
    
    //finally create the json array response.
    // If the response sent is a single string, it is an error
    if($battleHasEnded == 0){
        $responseJSON = json_encode($response);
        echo $responseJSON;
    } else {
        $action->postAction('foedead');
        $stat->updatePlayerStat('atk_stat', XP_ATK);
        $stat->updatePlayerStat('def_stat', XP_DEF);
        echo 'endfight';
    }

    $database->mysql->commit();
}


