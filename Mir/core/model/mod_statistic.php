<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

/*

function getLocalPlayerStatistics($database){

    $query="SELECT p.player_name, 
                cn.new_date, cn.new_content, cn.new_category
                FROM statistic_local_player cn INNER JOIN player p ON cn.id_player = p.id_player 
                WHERE cn.id_city=".$_SESSION['id_city']." AND cn.is_major=0 ORDER BY cn.new_date DESC LIMIT 50";
    try {
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['new_date'][] = $data['new_date'];
                $result['new_content'][] = $data['new_content'];
                $result['new_category'][] = $data['new_category'];
                $result['player_name'][] = $data['player_name'];
            }
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $result;
}

function getLocalCityStatistics($database){

    $query="SELECT p.player_name, 
                cn.new_date, cn.new_content, cn.new_category
                FROM city_new cn INNER JOIN player p ON cn.id_player = p.id_player 
                WHERE cn.id_city=".$_SESSION['id_city']." AND cn.is_major=0 ORDER BY cn.new_date DESC LIMIT 50";
    try {
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['new_date'][] = $data['new_date'];
                $result['new_content'][] = $data['new_content'];
                $result['new_category'][] = $data['new_category'];
                $result['player_name'][] = $data['player_name'];
            }
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $result;
}

function getGlobalPlayerStatistics($database){

    $query="SELECT p.player_name, 
                cn.new_date, cn.new_content, cn.new_category
                FROM city_new cn INNER JOIN player p ON cn.id_player = p.id_player 
                WHERE cn.id_city=".$_SESSION['id_city']." AND cn.is_major=0 ORDER BY cn.new_date DESC LIMIT 50";
    try {
        $res=$database->mysql->query($query);
        if($res){
            while($data=$res->fetch(PDO::FETCH_ASSOC)){
                $result['new_date'][] = $data['new_date'];
                $result['new_content'][] = $data['new_content'];
                $result['new_category'][] = $data['new_category'];
                $result['player_name'][] = $data['player_name'];
            }
        }
    } catch (PDOException $e){
        echo $e->getMessage();
        return false;
    }
    return $result;
}*/