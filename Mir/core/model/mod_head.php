<?php

/*
function getBuildingsAvailableInCityList($player_city) 
{
	MysqlConnexion(MYSQL_DATABASE, MYSQL_SERVER, MYSQL_LOGIN, MYSQL_PASSWORD);
	$query="SELECT building_name, building_wood1, building_wood2, building_wood3, 
            building_stone1, building_stone2, building_stone3, building_total_points
        FROM building b INNER JOIN building_city bc ON b.id_building = bc.id_building
        WHERE bc.is_available=1 AND bc.id_city=".$player_city;
	$count_users=[];
	try {
		$res = $database->mysql->query($query);
		if($res) {
			while($data=$res->fetch(PDO::FETCH_ASSOC)) {
				$count_users[$data['team_id']]=$data['nb_user']; //?
                $result['player_city']=$data['player_city']; //?
                $result[]=$data; //?
			}
		}
	} catch (PDOException $e){
		$_SESSION['yoda_debug']=$e->getMessage()."\n";
		return false;
	}
	return $result;
}
*/
