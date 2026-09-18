<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

function getPlayerTitles($database)
{
    //select player and city data on connection
    $query='SELECT id_title, title_name, title_descr
        FROM title t INNER JOIN player_title p ON t.id_title = p.id_title
        WHERE id_player='.$_SESSION['id_player'];
    try {
        $res=$database->mysql->query($query);
            if ($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $result['id_title']=$data['id_title']; //??
                    $result['title_name']=$data['title_name'];
                    $result['title_descr']=$data['title_descr'];
                }
            } else {
                $error = "Error : life bars introuvables";
            }
    } catch (PDOException $e){
        $_SESSION['mir_debug']=$e->getMessage()."\n";
        return false;
    }
    return $result;
}

function getPlayerMainTitle($database){
    
}
