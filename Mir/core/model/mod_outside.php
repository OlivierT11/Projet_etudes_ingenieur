<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if($_SESSION['player_area'] != 'outside'){
    header("Location:index.php?page=".$_SESSION['player_area']);
    die();
}

#region File inclusions

$database = new Database();
$invent = new Invent();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);
$news = new News($database, $log);
$action = new Action($database, $log);
$stat = new Stat($database, $log, $action);
$statistic = new Statistic($database, $log, $news);

#endregion

function getIdCamp($database)
{
    $query = 'SELECT id_city_camp 
                FROM city_camp 
                WHERE id_city='.$_SESSION['id_city'].' AND camp_pos_x ='.$_SESSION['player_pos_x'].' AND camp_pos_y = '.$_SESSION['player_pos_y'].' AND camp_area="outside"';
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result = $data['id_city_camp'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }

    return $result;
}

function getIdDjWhenOnEntry($database)
{
    $query = 'SELECT md.id_dj, md.dj_x, md.dj_y
                FROM map_dj md INNER JOIN map m ON m.id = md.id_area
                WHERE m.id_city='.$_SESSION['id_city'].' AND m.x ='.$_SESSION['player_pos_x'].' AND m.y = '.$_SESSION['player_pos_y'];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result['id_dj'] = $data['id_dj'];
            $result['dj_x'] = $data['dj_x'];
            $result['dj_y'] = $data['dj_y'];
        }
        
    } catch (PDOException $e){
        echo $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
        return false;
    }

    return $result;
}

//to display button fight
function getFoeData($database)
{
    $query = 'SELECT foe FROM map where id_city='.$_SESSION['id_city'].' AND x ='.$_SESSION['player_pos_x'].' AND y = '.$_SESSION['player_pos_y'];
    try {
        $res = $database->mysql->query($query);
        if($res) {
            $data=$res->fetch(PDO::FETCH_ASSOC); // LIMIT 1
            $result = $data['foe'];
        }
    } catch (PDOException $e){
        echo $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
        return false;
    }

    return $result;

}

//get the biome type to use in session. The value will then be used to display illustrations
function getBiomeType($database)
{
    $query="SELECT r,g,b
        FROM map
        WHERE x=".$_SESSION['player_pos_x']." AND y=".$_SESSION['player_pos_y']." AND id_city=".$_SESSION['id_city']." LIMIT 1"; 
    try {
        $res=$database->mysql->query($query);
        $result=[];
        $biomeType = '';
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $result['r'] = $data['r'];
                $result['g'] = $data['g']; 
                $result['b'] = $data['b']; 
            }
            //set the subarea depending on the biome type
            if( ($result['r']==182) && ($result['g']==255) && ($result['b']==0) ){
                $biomeType = "plain";
            } else if (($result['r']==76) && ($result['g']==255) && ($result['b']==0) ){
                $biomeType = "forest";
            } else if (($result['r']==0) && ($result['g']==0) && ($result['b']==0) ){
                $biomeType = "forestcenter";
            }
            return $biomeType;
        }
   } catch (PDOException $e){
       echo $e->getMessage();
   }
}
