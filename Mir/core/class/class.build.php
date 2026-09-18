<?php

/**
 * 
 * Class containing buidlings related methods (getlist, votes etc)
 * 
*/

class Build
{
    /**
     *  Get the list of all build buildings in the city and put them in session
     *  @param string $idCity : ID de la ville à rejoindre.
     */
    public static function getBuiltList($idCity, $database, $log, $idPlayer)
    {
        // get effects of built buildings
        $_SESSION['id_bld'] = [];

        $query="SELECT id_bld FROM building_city WHERE id_city=".$idCity." AND is_built=1";
        $result=[];
        try
        {
            $res = $database->mysql->query($query);
            if($res)
            {
                while($data=$res->fetch(PDO::FETCH_ASSOC))
                {
                    $_SESSION['id_bld'][] = $data['id_bld'];
                }
            }
        } 
        catch (PDOException $e)
        {
            $database->mysql->rollBack();

            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction getBuiltList : '.$e->getMessage();
            
            $log->addLog($err, 'error', $idPlayer);

            die();
        }
    }

    /**
     *  Set the default buildings in a new city
     *  @param string $idCity : ID de la nouvelle ville.
     */
    public static function InitializeBuildings($newIdCity, $database, $log, $idPlayer)
    {
            $query="INSERT INTO building_city(id_bld, id_city, is_available, is_built, bld_city_vote) 
            VALUES (3, ".$newIdCity.", 0, 1, 0),
                   (4, ".$newIdCity.", 1, 0, 0),
                   (5, ".$newIdCity.", 0, 0, 0),
                   (6, ".$newIdCity.", 0, 1, 0),
                   (7, ".$newIdCity.", 1, 0, 0),
                   (8, ".$newIdCity.", 0, 0, 0),
                   (10, ".$newIdCity.", 0, 1, 0),
                   (11, ".$newIdCity.", 1, 0, 0),
                   (12, ".$newIdCity.", 0, 0, 0),
                   (13, ".$newIdCity.", 0, 1, 0),  
                   (14, ".$newIdCity.", 1, 0, 0),   
                   (15, ".$newIdCity.", 0, 0, 0),   
                   (16, ".$newIdCity.", 0, 1, 0),   
                   (17, ".$newIdCity.", 1, 0, 0),   
                   (18, ".$newIdCity.", 0, 0, 0),   
                   (19, ".$newIdCity.", 0, 1, 0),   
                   (20, ".$newIdCity.", 1, 0, 0),   
                   (21, ".$newIdCity.", 0, 0, 0),   
                   (22, ".$newIdCity.", 0, 1, 0),   
                   (23, ".$newIdCity.", 1, 0, 0),   
                   (24, ".$newIdCity.", 0, 0, 0)";   
        try 
        {
            $database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();

            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction getBuiltList : '.$e->getMessage();
            
            $log->addLog($err, 'error', $idPlayer);

            die();
        }
    }
}