<?php

/**
 * Methods to be called from a CRON script. Either a PHP script or a GAE job.
 */

class Cron
{
    // Classes
    public $database;
    public $log;

    // Variables
    public $idCity;
    public $idPlayer;

    // Defines the name of the cache file. If the user is inside a city, the corresponding city cache is used.
    // TODO : add a general cache (for game news etc). The logic will need to be changed.
    public function __construct($database, $log, $idCity, $idPlayer)
    {
        $this->database = $database;
        $this->log = $log;

        $this->idCity = $idCity;
        $this->idPlayer = $idPlayer;
    }

    #region Building related methods

    function buildDaily()
    {
        $rareWoodCount = 0;
        $metalCount = 0;
        $buildingCount = 0;

        // Count precious wood
        $query="SELECT COUNT(id_item) 
                FROM city_item
                WHERE id_item=2 AND item_pos='bank' AND is_alive=1 AND id_city=".$_SESSION['id_city']."
                ORDER BY item_pos";
        try
        {
            $wood1 = $this->database->mysql->query($query)->fetchColumn();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        // Count metal (can be merge with previous w/ sum(case...))
        $query="SELECT COUNT(id_item) 
                FROM city_item
                WHERE id_item=20 AND item_pos='bank' AND is_alive=1 AND id_city=".$_SESSION['id_city']."
                ORDER BY item_pos";
        try
        {
            $metal = $this->database->mysql->query($query)->fetchColumn();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    
        // Count the number of buildable buildings
        $flooredWood = 0;
        $flooredMetal = 0;

        $flooredWood = floor($rareWoodCount / Constants::$rareWoodPerBuilding); // ex : floor(55/50) = floor(1,1) = 1
        $flooredMetal = floor($metalCount / Constants::$rareMetalPerBuilding);

        if($flooredWood > 0 && $flooredMetal > 0)
        {
            if ($flooredWood > $flooredMetal)
            {
                $buildingCount = $flooredMetal;
            }
            else
            {
                $buildingCount = $flooredWood;
            }
        }

        // Si les ressources sont suffisantes pour au moins un bâtiment
        if ($buildingCount != 0)
        {
            //select the building with the most votes, with the name to add the new
            $query='SELECT bc.id_bld, b.bld_name
                FROM building_city bc INNER JOIN building b ON b.id_bld = bc.id_bld
                WHERE bc.id_city='.$_SESSION['id_city'].'
                ORDER BY bc.bld_city_vote DESC
                LIMIT '.$buildingCount;
            try
            {
                $res=$this->database->mysql->query($query);
                    if ($res)
                    {
                        while($data=$res->fetch(PDO::FETCH_ASSOC))
                        {
                            $result['id_bld'][] = $data['id_bld'];
                            $result['bld_name'][] = $data['bld_name'];
                        }
                    }
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
    
            //build the building(s)
            $queryParam='(';
            for($i=0; $i<$buildingCount; $i++){
                $queryParam .= $result['id_bld'][$i].',';
            }
            $queryParam = substr_replace($queryParam ,"", -1);
            $queryParam .= ')';
    
            $query='UPDATE building_city
                SET is_available=0, is_built=1
                WHERE id_city='.$_SESSION['id_city'].' AND id_bld IN '.$queryParam; 
                //return $query;
            try
            {
                $this->database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
    
            //reset all the player votes for this building (player table)
            $query="UPDATE player_vote
                SET id_vote=0
                WHERE id_vote IN ".$queryParam." AND vote_type='bld'";
            try
            {
                $res=$this->database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
    
            //reset all the player votes for this building (building table)
            $query="UPDATE building_city
                SET bld_city_vote=0
                WHERE id_bld IN ".$queryParam;
            try
            {
                $res=$this->database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
    
            //remove the wood
            $wood1_to_destroy = $buildingCount * 50;
            $query='DELETE FROM city_item 
                        WHERE id_item=2 AND id_city='.$_SESSION['id_city'].' AND item_pos="bank" AND id_camp=0
                        LIMIT '.$wood1_to_destroy;
            /*
             $query='UPDATE city_item SET is_alive=0
                        WHERE id_item=2 AND id_city='.$_SESSION['id_city'].'
                        LIMIT '.$wood1_to_destroy;
                        */
            try
            {
                $res=$this->database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
            //remove the metal
            $metal_to_destroy = $buildingCount * 50;
            $query='DELETE FROM city_item 
                        WHERE id_item=20 AND id_city='.$_SESSION['id_city'].' AND item_pos="bank" AND id_camp=0
                        LIMIT '.$metal_to_destroy;
            /*$query='UPDATE city_item SET is_alive=0
                        WHERE id_item=20 AND id_city='.$_SESSION['id_city'].'
                        LIMIT '.$metal_to_destroy;*/
            try
            {
                $res=$this->database->mysql->query($query);
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                echo "Une erreur est survenue, merci de recharger la page.";
                die();
            }
    
            //make the next building available, if exists
            for($i=0; $i<$buildingCount; $i++){
                $id=0;
                switch($result['id_bld'][$i]){
                    case 3:
                        $id=4;
                        break;
                    case 4:
                        $id=5;
                        break;
                    case 6:
                        $id=7;
                        break; 
                    case 7:
                        $id=8;
                        break;
                    case 8:
                        $id=9;
                        break; 
                    case 10:
                        $id=11;
                        break;
                    case 11:
                        $id=12;
                        break;
                    case 13:
                        $id=14;
                        break;
                    case 14:
                        $id=15;
                        break;
                }
                $query='UPDATE building_city
                        SET is_available=1
                        WHERE id_city='.$_SESSION['id_city'].' AND id_bld='.$id;
                    try
                    {
                        $res=$this->database->mysql->query($query);
                    } 
                    catch (PDOException $e)
                    {
                        $this->database->mysql->rollBack();
                        $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
                        echo "Une erreur est survenue, merci de recharger la page.";
                        die();
                    }
            }
    
            //add the new of new building
            for($i=0; $i<$buildingCount; $i++)
            {
                $this->news->addNew($this->database, "build", $result['bld_name'][$i], 1);
            }
    
        }
    }

    #endregion

    #region Farm related methods

    /**
     * Every item in every field in the city gets +1 state if it is watered, +0 if not.
     */
    public function updateFarmItemsDaily()
    {
        //update the watered items
        $query='UPDATE city_farm
            SET
                id_item = (CASE
                    WHEN (id_item=14 AND is_watered = 1) THEN 15
                    WHEN (id_item=15 AND is_watered = 1) THEN 16
                    WHEN (id_item=17 AND is_watered = 1) THEN 18
                    WHEN (id_item=18 AND is_watered = 1) THEN 19
                    ELSE id_item
                END)
            WHERE id_city='.$this->idCity;
        try
        {
            $res=$this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $this->idPlayer);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        //unwater watered items
        $query='UPDATE city_farm
            SET is_watered = 0
            WHERE is_alive=1 AND is_watered=1 and id_city='.$this->idCity;
        try
        {
            $res=$this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $this->idPlayer);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
    }

    #endregion

    #region Map related methods

    /**
     * Make the trees grow back on every area of the map where they have been cut. 
     * A cut tree has 33% chance to grow back at midnight.
     */
    public function makeTreesGrowBack()
    {
        /// Make trees grow back on the map (outside)
        // Get the list of content strings from all areas

        
        $mapContents = [];

        $query='SELECT content, x, y
                FROM map
                WHERE id_city='.$this->idCity.'
                ORDER BY x ASC, y ASC';
        try
        {
            $res=$this->database->mysql->query($query);
            if ($res)
            {
                while($data=$res->fetch(PDO::FETCH_ASSOC))
                {
                    $mapContents['content'][] = $data['content'];
                    $mapContents['x'][] = $data['x'];
                    $mapContents['y'][] = $data['y'];
                }
            }
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $this->idPlayer);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }
        
        

        // Change the content of each area
        
        $newMapContents = [];

        foreach ($mapContents as $area)
        {
            $newContent = "";
            $arrayItems = str_split($area['content']);
            foreach ($arrayItems as $item)
            {
                // Si c'est une souche, 1 chance sur 3 de devenir un arbuste. Si c'est un arbuste, devient un arbre
                switch ($item)
                {
                    case "s" :
                        $n = 0;
                        $n = rand(1,3);
                        if ($n == 1)
                        {
                            $item = "b"; // arbuste ?
                        }
                    break;
                    case "b" :
                        $item = "a";
                    break;
                    default :
                    break;
                }

                $newContent .= $item;
            }

            $newMapContents['content'] = $newContent;
            $newMapContents['x'] = $area['x'];
            $newMapContents['y'] = $area['y'];
        }

        // Put the content back into the MAP table

        $query='UPDATE map SET content = :content
                WHERE id_city='.$this->idCity.'
                  AND x = :x
                  AND y = :y';

        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            foreach ($newMapContents as $area)
            {
                $sth->execute(array(':content' => $area['content'], ':x' => $area['x'], ':y' => $area['y']));
            }
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de cron.php : '. $e->getMessage() . ' /// ' . $query, 'error', $this->idPlayer);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }


    }

    #endregion

    #region City related methods

    /**
     * Call the DestroyCity() method from the City.php class.
     */
    public function callDestroyCity()
    {
        // Get the list of cities that ended but haven't been destroyed

        // For each city, call the DestroyCity() method with the ID city as parameter
    }

    #endregion


}