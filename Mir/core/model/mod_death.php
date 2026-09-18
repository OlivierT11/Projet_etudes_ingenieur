<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}


#region File inclusions

//require dirname(dirname(__FILE__))."/tools/tools.php";

//require dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

//require dirname(dirname(__FILE__))."/class/class.invent.php";
//$invent = new Invent();

//require dirname(dirname(__FILE__))."/class/class.map.php";

//require dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

//require dirname(dirname(__FILE__))."/class/class.cache.php";
$cache = new Cache($_SESSION['id_city']);

//require dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

//require dirname(dirname(__FILE__))."/class/class.action.php";
$action = new Action($database, $log);

//require dirname(dirname(__FILE__))."/class/class.stat.php";
$stat = new Stat($database, $log, $action);

//require dirname(dirname(__FILE__))."/class/class.statistic.php";
$statistic = new Statistic($database, $log, $news);

#endregion

// reinitilize table player : id_city, x, y, dj etc
function reinitializePlayerLocation($database, $log)
{
    $query='UPDATE player 
       SET player_pos_x =0, player_pos_y=0, player_area="inside", id_camp=0, id_dj=0, id_abyss=0, prev_dir=0
       WHERE id_player='.$_SESSION['id_player'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
       $database->mysql->rollBack();
       $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
       $log->addLog('Erreur dans la requête SQL de mod_death.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
       header("Location: ".$_SERVER['PHP_SELF']);
       die();
    }
}

// delete the player's inventory items. Don't touch the farm !
function reinitializePlayerItems($database, $log)
{
    $query="UPDATE city_item SET is_alive=0
    WHERE id_player=".$_SESSION['id_player']." AND (item_pos='invent' OR item_pos='shield' OR item_pos='upper' OR item_pos='lower' OR item_pos='helmet' OR item_pos='mask' OR item_pos='spear') AND is_alive=1";
    try 
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
       $database->mysql->rollBack();
       $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
       $log->addLog('Erreur dans la requête SQL de mod_death.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
       header("Location: ".$_SERVER['PHP_SELF']);
       die();
    }
}

// update life bars
function reinitializePlayerLifeBars($database, $log)
{
    $query="UPDATE player_life_bar SET player_food=5, player_morale=100
    WHERE id_player=".$_SESSION['id_player'];
    try
    {
        $res=$database->mysql->query($query);
    }
    catch (PDOException $e)
    {
       $database->mysql->rollBack();
       $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
       $log->addLog('Erreur dans la requête SQL de mod_death.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
       header("Location: ".$_SERVER['PHP_SELF']);
       die();
    }
}

// reunitialize the Ally number on the dying area.
function updateMapAllies($database, $log)
{
    $area = $_SESSION['player_area'];
    $x = $_SESSION['player_pos_x'];
    $y = $_SESSION['player_pos_y'];

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

    try 
    {
        $database->mysql->query($query);
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $log->addLog('Erreur dans la requête SQL de mod_death.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }
}
