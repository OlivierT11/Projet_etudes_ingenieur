<?php

#region File inclusions

$database = new Database();
$log = new Log();

//$cache = new Cache($_SESSION['id_city']);

#endregion


// unused
// TODO : retrait des SQL dans ctrl
function getPlayerParams($database) 
{
    //MysqlConnexion(MYSQL_DATABASE, MYSQL_SERVER, MYSQL_LOGIN, MYSQL_PASSWORD);
    //$query="SELECT building_name, building_wood1, building_wood2, building_wood3, 
    //        building_stone1, building_stone2, building_stone3, building_total_points
    //    FROM building b INNER JOIN building_city bc ON b.id_building = bc.id_building
    //    WHERE bc.is_available=1 AND bc.id_city=".$player_city;
	$query='SELECT id_account FROM account 
        WHERE acc_username='.$_POST['username'].' AND acc_password='.$_POST['password'];
    try {
		$res = $database->mysql->query($query);
		if($res) {
			while($data=$res->fetch(PDO::FETCH_ASSOC)) {
				$result=$data['id_account'];
			}
		}
	} catch (PDOException $e){
		$_SESSION['mir_debug']=$e->getMessage()."\n";
		return false;
	}
    return $result;
}
