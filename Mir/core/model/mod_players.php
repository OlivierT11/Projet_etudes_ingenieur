<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}


#region File inclusions

$database = new Database();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);
$news = new News($database, $log);
$action = new Action($database, $log);
$stat = new Stat($database, $log, $action);
$statistic = new Statistic($database, $log, $news);

#endregion


// Get local player list
function getPlayersList($database)
{
    // Get the allies around the player's area
    // If inside or camp, displays all the players inside. Else, displays the allies on a 9*9 square area.
    switch($_SESSION['player_area']){
        case 'inside':
            $query="SELECT p.player_name, p.player_area, p.player_thumbs, p.id_player
                    FROM player p 
                    WHERE p.id_city=".$_SESSION['id_city']." AND p.player_area='inside' AND p.id_camp=0";
            break;
        case 'camp':
            $query="SELECT p.player_name, p.player_area, p.player_thumbs, p.id_player
                    FROM player p 
                    WHERE p.id_city=".$_SESSION['id_city']." AND p.id_camp = ".$_SESSION['id_camp'];
            break;

        //if outside/dj/dj2/abyss/hell or any other area in session(player_area)
        default:
            $x = $_SESSION['player_pos_x'];
            $y = $_SESSION['player_pos_y'];
            $query="SELECT p.player_name, p.player_area, p.player_thumbs, p.id_player
                    FROM player p
                    WHERE p.id_city=".$_SESSION['id_city']." AND p.player_area = '".$_SESSION['player_area']."'";
            $query.= ' AND (p.player_pos_x BETWEEN '.($x-1).' AND '.($x+1).') AND (p.player_pos_y BETWEEN '.($y-1).' AND '.($y+1).')
                    ORDER BY p.player_pos_x ASC, p.player_pos_y ASC';
    }

    try {
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['player_name'][] = $data['player_name']; 
                $result['player_area'][] = $data['player_area']; 
                //$result['player_title'][] = $data['player_title']; 
                $result['player_thumbs'][] = (int)$data['player_thumbs']; 
                $result['id_player'][] = (int)$data['id_player']; 
            }
        }
    } catch (PDOException $e){
        echo 'error in query mod_players.php 1: ' . $e->getMessage();
        return false;
    }

    // Pour chaque id player, renvoie la stat principale et vérifie si elle est master
    $query="SELECT ps.id_player_stat, ps.player_stat_lvl,
                    s.stat_name
                FROM player_stat ps INNER JOIN stat s ON s.id_stat = ps.id_stat
                WHERE ps.id_player=:id
                ORDER BY player_stat_lvl DESC 
                LIMIT 2"; // 2 to check if master
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    foreach($result['id_player'] as $player){

        $sth->execute(array(':id' => $player));
        $res = $sth->fetchAll();

        // Create an array of best stats name and lvl
        $result['bestStatName'][] = $res[0]['stat_name'];
        $result['bestStatLvl'][] = $res[0]['player_stat_lvl'];

        // Vérifie si la meilleure stat est master
        $bestStatLvl = $res[0]['player_stat_lvl'];
        $secondStatLvl = $res[1]['player_stat_lvl'];
        if($bestStatLvl >= 2*$secondStatLvl){
            $result['is_master'][] = 1;
        } else {
            $result['is_master'][] = 0;
        }

    }

    return $result;
}

// Get general player list (entire city)
function getPlayersListGeneral($database)
{
    $query="SELECT p.player_name, p.player_area, p.player_thumbs, p.id_player
            FROM player p 
            WHERE p.id_city=".$_SESSION['id_city'];
    try {
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['player_name'][] = $data['player_name']; 
                $result['player_area'][] = $data['player_area']; 
                //$result['player_title'][] = $data['player_title']; 
                $result['player_thumbs'][] = (int)$data['player_thumbs']; 
                $result['id_player'][] = (int)$data['id_player']; 
            }
        }
    } catch (PDOException $e){
        echo 'error in query mod_players.php 1: ' . $e->getMessage();
        return false;
    }

    // Pour chaque id player, renvoie la stat principale et vérifie si elle est master
    $query="SELECT ps.id_player_stat, ps.player_stat_lvl,
                    s.stat_name
                FROM player_stat ps INNER JOIN stat s ON s.id_stat = ps.id_stat
                WHERE ps.id_player=:id
                ORDER BY player_stat_lvl DESC 
                LIMIT 2"; // 2 to check if master
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    foreach($result['id_player'] as $player){

        $sth->execute(array(':id' => $player));
        $res = $sth->fetchAll();

        // Create an array of best stats name and lvl
        $result['bestStatName'][] = $res[0]['stat_name'];
        $result['bestStatLvl'][] = $res[0]['player_stat_lvl'];

        // Vérifie si la meilleure stat est master
        $bestStatLvl = $res[0]['player_stat_lvl'];
        $secondStatLvl = $res[1]['player_stat_lvl'];
        if($bestStatLvl >= 2*$secondStatLvl){
            $result['is_master'][] = 1;
        } else {
            $result['is_master'][] = 0;
        }

    }

    return $result;
}

function getMuteList($database)
{
    $query="SELECT pv.id_player_muted
                FROM player_mute pv 
                WHERE pv.id_player=".$_SESSION['id_player'];
    try {
        $res=$database->mysql->query($query);
        $result=[];
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['id_player_muted'][] = (int)$data['id_player_muted']; 
            }
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    // Si des joueurs ont été mute
    if(!empty($result['id_player_muted'])){
        //$_SESSION['muteList'] = $result['id_player_muted'];
        $result = $result['id_player_muted'];
    } else {
        //$_SESSION['muteList'] = '';
    }

    return $result;
}
