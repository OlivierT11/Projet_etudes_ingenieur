<?php
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

#endregion

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

if(isset($_POST['vote'])){

    if($_POST['vote'] == 'build'){

        // Database ID of the building to vote to
        $idBld = (int)($_POST['id']);

        //update the vote count
        $query = 'UPDATE building_city SET bld_city_vote = bld_city_vote + 1 
            WHERE id_city=:idCity AND id_bld=:id';
        try
        {
            $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':id', $idBld); 
            $sth->bindValue(':idCity', $_SESSION['id_city']); 
            $sth->execute();
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $log->addLog($err, 'error', $_SESSION['id_player']);
            die();
        }

        //remove a vote from the previous voted building, if the player already voted.
        $idVote = '';
        $query = "SELECT id_vote 
                  FROM player_vote
                  WHERE vote_type='bld'
                  LIMIT 1";
        try
        {
            $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute();
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $log->addLog($err, 'error', $_SESSION['id_player']);
            die();
        }

        // If a previous vote exists, remove it
        if($sth->rowCount() > 0)
        {
            $data=$sth->fetch(PDO::FETCH_ASSOC);
            $idVote = (int)$data['id_vote']; //cast to reuse in next query

            $query = 'UPDATE building_city SET bld_city_vote = bld_city_vote - 1 
                      WHERE id_city = :idCity AND id_bld = :idVote';

            $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':idCity', $_SESSION['id_city']); 
            $sth->bindValue(':idVote', $idVote); 
            $sth->execute();
        }

        //update player vote
        $query = "UPDATE player_vote 
                  SET id_vote = :idVote
                  WHERE vote_type='bld'
                  AND id_player = :idPlayer";
        try
        {
            $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':idPlayer', $_SESSION['id_player']); 
            $sth->bindValue(':idVote', $idBld); 
            $sth->execute();
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $log->addLog($err, 'error', $_SESSION['id_player']);
            die();
        }

        //returns the id of the previous element voted, if exists, to update the previous vote count w/ JS and not reload the page TODO

    }

}

$database->mysql->commit();
