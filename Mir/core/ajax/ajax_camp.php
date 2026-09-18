<?php

if(!isset($_SESSION))
    session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
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

#endregion

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

if (isset($_REQUEST["build"]) && $_REQUEST["build"]=="camp") {

    $area = $_SESSION['player_area'];

    // Vérifie que le joueur a les 30 bois requis
    $query="SELECT COUNT(ci.id_item) FROM city_item ci
                JOIN item i ON i.id_item = ci.id_item
                WHERE ci.id_player=".$_SESSION['id_player']." AND i.item_name='wood' AND ci.is_alive=1 
                ORDER BY ci.id_city_item ASC 
                LIMIT 30";
    $result['wood_nbr'] = $database->mysql->query($query)->fetchColumn();
    
    
    if($result['wood_nbr'] >= 30)
    { 

        //construction du camp
        $query="INSERT INTO city_camp(id_city, camp_pos_x, camp_pos_y, camp_area)
                 VALUES (".$_SESSION['id_city'].", ".$_SESSION['player_pos_x'].", ".$_SESSION['player_pos_y'].", '".$area."')";
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_camp.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //retrait des 30 bois de l'inventaire

        // !!! mysql n'autorise pas LIMIT dans UPDATE !!!
        // En attendant une meilleure solution -> 2 requêtes dont une préparée.

        // Selectionne les ids des 30 premiers bois du joueur
        $query="SELECT id_city_item 
                FROM city_item
                WHERE id_player=".$_SESSION['id_player']." AND id_item=1 AND item_pos='invent' AND is_alive=1
                ORDER BY id_city_item ASC
                LIMIT 30"; 
        try
        {
            $res=$database->mysql->query($query);
            $result=[];
            if($res){
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $result[] = $data['id_city_item'];
                }
            }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de ajax_camp.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
        
        // Détruit les 30 bois en suivant le tableau précédent
        $query="UPDATE city_item 
                SET is_alive=0
                WHERE id_city_item = :id"; 
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        $n = sizeof($result);
        for($i=0; $i<$n; $i++){
        
            try
            {
                $sth->execute(array(':id' => $result[$i]));
            }
            catch (PDOEXception $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de ajax_camp.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }

        }

    }
    else {
        echo "Vous n'avez pas assez de bois !";
    }
        
}

$database->mysql->commit();
