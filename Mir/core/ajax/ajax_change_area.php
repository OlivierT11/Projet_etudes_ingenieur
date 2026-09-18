<?php

#region Sessions

if(!isset($_SESSION))
session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
    die();
}

#endregion

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

#region Start SQL Transaction
// Begins the transaction for the entire file.
$database->mysql->beginTransaction();
#endregion

// On clic sur boutton pou changer d'area
//update la BDD et les sessions (player_area, x et y)
if(isset($_REQUEST['area']) && $_REQUEST['area'] != '') {
    $area  = $_REQUEST['area'];

    #region Reset the previous direction

    //reset the previous area (the player can't be blocked on area entries as there are no monsters here)
    $query = "UPDATE player 
        SET prev_dir='0'
        WHERE id_player =".$_SESSION['id_player'];

    try
    {
        $database->mysql->query($query);
        $_SESSION['player_prev_dir'] = '0';
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
        $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
        echo "Une erreur est survenue, merci de recharger la page.";
        die();
    }

    #endregion


    #region Enter city

    if ($area == "inside") {

        //from outside
        if($_SESSION['player_area'] == 'outside'){

            // Change area
            $query = 'UPDATE player 
            SET player_area="'.$area.'"
            WHERE id_player ='.$_SESSION['id_player'];

            try
            {
                $database->mysql->query($query);
                $_SESSION['player_area'] = 'inside';
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }

            // Update the ally number on previous area
            $x = $_SESSION['player_pos_x'];
            $y = $_SESSION['player_pos_y'];
            $query = 'UPDATE map SET ally = ally-1
                WHERE x='.$x.' AND y='.$y.' AND id_city='.$_SESSION['id_city'];
            try
            {
                $database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }

        }

        //from abyss
        else if($_SESSION['player_area'] == 'abyss'){
            
            //get abyss entry coordinate
            $query = 'SELECT abyss_x, abyss_y
                FROM abyss_city 
                WHERE id_abyss ='.$_SESSION['id_abyss'];
            try
            {
                $res=$database->mysql->query($query);
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $x = $data['abyss_x'];
                    $y = $data['abyss_y'];
                }
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }

            // Update the ally number on previous area
            $x = $_SESSION['player_pos_x'];
            $y = $_SESSION['player_pos_y'];
            $query = 'UPDATE abyss SET abyss_ally = abyss_ally-1
                WHERE abyss_x='.$x.' AND abyss_y='.$y.' AND id_abyss='.$_SESSION['id_city'];
            try
            {
                $database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }

            //Update player pos x, y, id_abyss and player_area
            $query = 'UPDATE player 
                        SET player_pos_x=0, player_pos_y=0, id_abyss=0, player_area="inside" 
                        WHERE id_player ='.$_SESSION['id_player'];
            try
            {
                $database->mysql->query($query);
                $_SESSION['player_area'] = 'inside';
                $_SESSION['player_pos_x'] = 0;
                $_SESSION['player_pos_y'] = 0;
                unset($_SESSION['id_abyss']);
            }
            catch (PDOException $e)
            {
                $database->mysql->rollBack();
                $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
        }

    }

    #endregion


    #region Leave city

    else if ($area == "outside") {

        $query = 'UPDATE player 
        SET player_area="'.$area.'"
        WHERE id_player ='.$_SESSION['id_player'];

        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'outside';
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on previous area
        $query = 'UPDATE map SET ally = ally+1
            WHERE x=0 AND y=0 AND id_city='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    #endregion
        

    #region Enter dj

    else if ($area == "dj") {

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE map SET ally = ally-1
            WHERE x='.$x.' AND y='.$y.' AND id_city='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //récupérer les coordonnées de l'entrée dans le donjon (ie de la sortie)
        $id_dj = $_SESSION['id_dj'];
        $dj_x = $_SESSION['dj_x'];
        $dj_y = $_SESSION['dj_y'];

        //Update player pos x, y, id_dj and player_area
        $query = 'UPDATE player 
                    SET player_pos_x='.$dj_x.', player_pos_y='.$dj_y.', id_dj='.$id_dj.', player_area="dj" 
                    WHERE id_player ='.$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'dj';
            $_SESSION['player_pos_x'] = $dj_x;
            $_SESSION['player_pos_y'] = $dj_y;
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on next area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE dj SET dj_ally = dj_ally+1
            WHERE dj_x='.$x.' AND dj_y='.$y.' AND id_dj='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    }

    #endregion

    
    #region Leave dj

    else if ($area == "outside-from-dj") {

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE dj SET dj_ally = dj_ally-1
            WHERE dj_x='.$x.' AND dj_y='.$y.' AND id_dj='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }


        //get dj coordinate on map
        $query = 'SELECT m.x, m.y 
            FROM map m INNER JOIN map_dj mdj ON mdj.id_area = m.id 
            WHERE mdj.id_dj ='.$_SESSION['id_dj'];
        try
        {
            $res=$database->mysql->query($query);
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $x = $data['x'];
                $y = $data['y'];
            }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //update player with the new coordinates
        $query = 'UPDATE player 
        SET player_area="outside", player_pos_x = '.$x.', player_pos_y='.$y.', id_dj=0
        WHERE id_player ='.$_SESSION['id_player'];

        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'outside';
            $_SESSION['player_pos_x'] = $x;
            $_SESSION['player_pos_y'] = $y;
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on next area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE map SET ally = ally+1
            WHERE x='.$x.' AND y='.$y.' AND id_city='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    #endregion


    #region Enter abyss

    else if ($area == "abyss-from-city") {

        //récupérer les coordonnées de l'entrée dans l'abysse
        $id_abyss = $_SESSION['id_abyss'];
        $abyss_x = $_SESSION['abyss_x'];
        $abyss_y = $_SESSION['abyss_y'];

        //Update player pos x, y, id_dj and player_area
        $query = "UPDATE player 
                    SET player_pos_x=".$abyss_x.", player_pos_y=".$abyss_y.", id_abyss=".$id_abyss.", player_area='abyss' 
                    WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'abyss';
            $_SESSION['player_pos_x'] = $abyss_x;
            $_SESSION['player_pos_y'] = $abyss_y;
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on next area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE abyss SET abyss_ally = abyss_ally+1
            WHERE abyss_x='.$x.' AND abyss_y='.$y.' AND id_abyss='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    #endregion
    
    
    #region Enter hell

    else if ($area == "hell") {

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE abyss SET abyss_ally = abyss_ally-1
            WHERE abyss_x='.$x.' AND abyss_y='.$y.' AND id_abyss='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //récupérer les coordonnées de l'entrée de l'enfer dans l'abysse
        $id_hell = $_SESSION['id_hell'];
        $hell_x = $_SESSION['hell_x'];
        $hell_y = $_SESSION['hell_y'];

        //Update player pos x, y, id_dj and player_area
        $query = "UPDATE player 
                    SET player_pos_x=0, player_pos_y=0, id_abyss=".$id_hell.", player_area='hell' 
                    WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'hell';
            $_SESSION['player_pos_x'] = 0;
            $_SESSION['player_pos_y'] = 0;
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE hell SET hell_ally = hell_ally+1
            WHERE hell_x='.$x.' AND hell_y='.$y.' AND id_hell='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    }

    #endregion

    
    #region Leave hell

    else if($area == 'abyss-from-hell'){ 

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE hell SET hell_ally = hell_ally-1
            WHERE hell_x='.$x.' AND hell_y='.$y.' AND id_hell='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
            
        //get abyss entry coordinate
        $query = 'SELECT id_abyss, abyss_x, abyss_y
            FROM abyss_hell 
            WHERE id_abyss ='.$_SESSION['id_hell'];
        try
        {
            $res=$database->mysql->query($query);
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $x = $data['abyss_x'];
                $y = $data['abyss_y'];
                $id_abyss = $data['id_abyss'];
            }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //Update player info
        $query = 'UPDATE player 
                    SET player_pos_x='.$x.', player_pos_y='.$y.', id_abyss='.$id_abyss.', player_area="abyss" 
                    WHERE id_player ='.$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'abyss';
            $_SESSION['player_pos_x'] = $x;
            $_SESSION['player_pos_y'] = $y;
            unset($_SESSION['id_hell']);
            unset($_SESSION['hell_x']);
            unset($_SESSION['hell_y']);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE abyss SET abyss_ally = abyss_ally+1
            WHERE abyss_x='.$x.' AND abyss_y='.$y.' AND id_abyss='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    }

    #endregion


    #region Enter camp

    else if ($area == "enter-camp") {

        // Quand un joueur rejoint un camp, ses coordonnées x,y et sa position player_area restent les mêmes
        // ctrl_login garde id_camp en memoire si différent de 0, et log in dans le bon camp.
        
        // update la nouvelle position hors du camp
        $query = "UPDATE player SET id_camp=".$_SESSION['id_camp']."  
                    WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    #endregion


    #region Leave camp

    else if ($area == "leave-camp") {

        // Quand un joueur quitte un camp, ses coordonnées x,y et sa position player_area restent les mêmes

        // Selectionne la position du joueur hors du camp, gardée en mémoire dans player_area 
        //(la session a besoin de savoir qu'on est dans un camp pour les illustrations, la BDD le sait grâce à id_camp pour se connecter dans le camp)
        $query = "SELECT player_area
                    FROM player 
                    WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $res=$database->mysql->query($query);
            if($res){
                $data=$res->fetch(PDO::FETCH_ASSOC);
                $_SESSION['player_area'] = $data['player_area']; //?
            }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // met l'id_camp à 0 pour se connecter hors du camp à a prochaine connexion
        $query = "UPDATE player set id_camp=0  
                    WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    #endregion


    #region Enter dj2

    else if ($area == "dj2") {

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE map SET ally = ally-1
            WHERE x='.$x.' AND y='.$y.' AND id_city='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        /*
        //get dj2 entry coordinate
        $query = 'SELECT id_dj2, dj2_x, dj2_y
            FROM dj_dj2
            WHERE id_dj ='.$_SESSION['id_dj'];
        try{
            $res=$database->mysql->query($query);
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $x = $data['dj2_x'];
                $y = $data['dj2_y'];
                $id_dj2 = $data['id_dj2'];
                $_SESSION['dj2_x'] = $x;
                $_SESSION['dj2_y'] = $y;
            }
        } catch (PDOException $e){
            echo $e->getMessage();
        }*/

        //Update player info
        $query = 'UPDATE player 
                    SET player_pos_x=0, player_pos_y=0, player_area="dj2" 
                    WHERE id_player ='.$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'dj2';
            $_SESSION['player_pos_x'] = 0;
            $_SESSION['player_pos_y'] = 0;
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE dj2 SET dj2_ally = dj2_ally-1
            WHERE dj2_x='.$x.' AND dj2_y='.$y.' AND id_dj2='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

    }

    #endregion

    
    #region Leave dj2

    else if ($area == "dj-from-dj2") {

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE dj2 SET dj2_ally = dj2_ally-1
            WHERE dj2_x='.$x.' AND dj2_y='.$y.' AND id_dj2='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //get dj entry to d2 coordinate
        $query = 'SELECT dj2_x, dj2_y
            FROM dj_dj2 
            WHERE id_dj2 ='.$_SESSION['id_dj2'];
        try
        {
            $res=$database->mysql->query($query);
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $x = $data['dj2_x'];
                $y = $data['dj2_y'];
                $_SESSION['dj_x'] = $x;
                $_SESSION['dj_y'] = $y;
            }
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //Update player info
        $query = 'UPDATE player 
                    SET player_pos_x='.$x.', player_pos_y='.$y.', player_area="dj" 
                    WHERE id_player ='.$_SESSION['id_player'];
        try
        {
            $database->mysql->query($query);
            $_SESSION['player_area'] = 'dj';
            $_SESSION['player_pos_x'] = $x;
            $_SESSION['player_pos_y'] = $y;
           // unset($_SESSION['id_hell']);
          //  unset($_SESSION['hell_x']);
           // unset($_SESSION['hell_y']);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Update the ally number on previous area
        $x = $_SESSION['player_pos_x'];
        $y = $_SESSION['player_pos_y'];
        $query = 'UPDATE dj SET dj_ally = dj_ally+1
            WHERE dj_x='.$x.' AND dj_y='.$y.' AND id_dj='.$_SESSION['id_city'];
        try
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            $log->addLog('Erreur dans la requête SQL de change_area : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    }

    #endregion
}

$database->mysql->commit();
