<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if($_SESSION['player_area'] != 'inside' && $_SESSION['player_area'] != 'camp'){
    header("Location:index.php?page=".$_SESSION['player_area']);
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

function getBuildingList($database)
{
    $query="SELECT b.id_bld, b.bld_name, b.bld_descr, b.bld_cat, b.bld_wood1,
            bc.is_built, bc.is_available, bc.bld_city_vote
        FROM building b 
            INNER JOIN building_city bc ON b.id_bld = bc.id_bld
        WHERE bc.id_city=".$_SESSION['id_city']."
        ORDER BY bc.is_built DESC, bc.is_available DESC, b.bld_cat";
	$result=[];
	try {
		$res = $database->mysql->query($query);
		if($res) {
			while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $result[]=$data; //OBLIGATOIRE POUR FOREACH 
			}
		}
	} catch (PDOException $e){
        echo $e->getMessage();
		//$_SESSION['yoda_debug']=$e->getMessage()."\n";
		return false;
	}
	return $result;

}

// get rare wood
function getRareWood($database)
{
    $query="SELECT COUNT(*) as woodNbr FROM city_item
        WHERE id_city=".$_SESSION['id_city']." AND item_pos = 'bank' AND is_alive=1 AND id_item=2";
	$res = $database->mysql->prepare($query);
	$res->execute();
	$row = $res->fetchAll(PDO::FETCH_OBJ);
	$woodNbr = $row[0]->woodNbr;

	return $woodNbr;
}

//get metal
function getMetal($database)
{
    $query="SELECT COUNT(*) as metalNbr FROM city_item
        WHERE id_city=".$_SESSION['id_city']." AND item_pos = 'bank' AND is_alive=1 AND id_item=20";
	$res = $database->mysql->prepare($query);
	$res->execute();
	$row = $res->fetchAll(PDO::FETCH_OBJ);
	$metalNbr = $row[0]->metalNbr;
	
	return $metalNbr;
}

// News
$newsListHTML = $news->getCityNewsBuilding();