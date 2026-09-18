<?php

class Map {
    public $id = [];
    public $content = [];
    public $nbr_foe = [];
    public $nbr_ally = [];
    public $x = [];
    public $y = [];
    public $area = '';

    //map
    public $r = [];
    public $g = [];
    public $b = [];

    //dj walls
    public $up = [];
    public $right = [];
    public $down = [];
    public $left = [];

    public $is_discovered = [];

    //dj related
    public $idDj = 0;
    public $id_map_exit = 0;
    public $id_hell_entry = 0;

    // used to display the correct gates on areas (cave entry, camp etc)
    public $is_cave_entry = false;
    public $is_camp = false;
    public $is_cave_exit = false;
    public $is_dj2_entry = false;
    public $is_abyss_exit=false;
    public $is_hell_entry=false;
    public $is_hell_exit=false;

    //city entry
    public $is_city = false;

    public $query=''; //testing

    // get the current foe nbr in hell for victory
    public $foeNbrInHell = 1;

    // keep current pos to compute the foe lvl on each area
    public $current_x = 0;
    public $current_y = 0;

    // Objects
    private $database;
    private $log;

    // Sessions
    private $idPlayer = 0;
    private $idCity = 0;

    //public $query; //sending the query string for testing
    
    public function __construct($database, $log) {
        $this->database = $database;
        $this->log = $log;
        $this->idPlayer = $_SESSION['id_player'];
        $this->idCity = $_SESSION['id_city'];
    }
    
    public function getAreasDataMap($area, $x, $y)
    {
        $this->area = $area;
        
        $query = 'SELECT id, content, foe, x, y, r, g, b, is_discovered 
            FROM '.$area.' 
            WHERE id_city = '.$this->idCity.' AND x BETWEEN '.($x-1).' AND '.($x+1).' AND y BETWEEN '.($y-1).' AND '.($y+1).'
            ORDER BY y DESC, x ASC';
        //$this->query = $query;
        try {
            $err = '';
            $res = $this->database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $this->id[] = $data['id'];
                    $this->content[] = $data['content'];
                    $this->nbr_foe[] = $data['foe'];
                    $this->x[] = $data['x'];
                    $this->y[] = $data['y'];
                    $this->r[] = $data['r'];
                    $this->g[] = $data['g'];
                    $this->b[] = $data['b'];
                    $this->is_discovered[] = $data['is_discovered'];
                }
            }
        }
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction getAreasDataMap 1 : '.$e->getMessage();
        }

        //get the number of allies on the 9 areas arround the player
        $this->getAllyNbr();

        //check if the area is the city entry ( (0,0) + outside )
        if($this->area == 'map' && $this->x[4]=='0' && $this->y[4]=='0'){
            $this->is_city = true;
        }

        // compute the foe strengh of current position to display on map
        //$foeAtk = $foeDef = floor( sqrt( pow($_SESSION['x']-result['x'],2) + pow($_SESSION['y']-result['y'], 2) ) );
        
        
        //check if the area is a dj entry
        $query = 'SELECT id_dj FROM map_dj WHERE id_area = '.$this->id[4].' LIMIT 1'; //Limit 1 ou alors indexer la colonne
        try {
            $res = $this->database->mysql->query($query);
            if($res) {
                $data=$res->fetch(PDO::FETCH_ASSOC);
                if($res->rowCount() > 0){  //s'il y a un dj ici
                    $this->idDj = $data['id_dj'];
                    $this->is_cave_entry = true;
                }
            }
        }
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction getAreasDataMap 2 : '.$e->getMessage();
        }

        // check if the area has a camp built on it
        $query = 'SELECT id_city_camp FROM city_camp where id_city='.$this->idCity.' AND camp_area = "outside" AND camp_pos_x ='.$x.' AND camp_pos_y = '.$y;
        try {
            $res = $this->database->mysql->query($query);
            if($res) {
                $data=$res->fetch(PDO::FETCH_ASSOC);
                if($res->rowCount() > 0){
                    $this->idCamp = $data['id_city_camp'];
                    $this->is_camp = true;
                }
            }
        }
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction getAreasDataMap 3 : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);
        
    }

    public function getAreasDataDj($area, $x, $y, $id_dj)
    {
        /*
         $query = 'SELECT id_dj, dj_content, dj_x, dj_y, dj_u, dj_r, dj_d, dj_l, dj_foe 
                    FROM '.$area.' 
                    WHERE(
                        (dj_x = '.$x.' AND dj_y ='.$y.')
                    AND
                        CASE 
                            WHEN dj_u = 0 THEN (dj_x = '.$x.' AND dj_y ='.($y+1).')
                        END
                    AND
                        CASE 
                            WHEN dj_l = 0 THEN (dj_x = '.($x-1).' AND dj_y ='.$y.')
                        END
                    AND
                        CASE 
                            WHEN dj_d = 0 THEN (dj_x = '.$x.' AND dj_y ='.($y-1).')
                        END
                    AND
                        CASE 
                            WHEN dj_r = 0 THEN (dj_x = '.($x+1).' AND dj_y ='.$y.')
                        END
                    AND
                        id_dj='.$id_dj.')';


            */

        //get map content and coordinates on 9 areas around the player.
        $query = 'SELECT id_dj, dj_content, dj_x, dj_y, dj_u, dj_r, dj_d, dj_l, dj_foe, is_discovered
                FROM '.$area.' 
                WHERE id_dj='.$id_dj.' AND (dj_x BETWEEN '.($x-1).' AND '.($x+1).') AND (dj_y BETWEEN '.($y-1).' AND '.($y+1).') 
                ORDER BY dj_y DESC, dj_x ASC';
        //$this->query = $query;
        try {
            $res = $this->database->mysql->query($query);
            // If less than 9 areas are found, the player is on a map egde limit. Then only load 1 area. 
            // amèl : add exterior walls.
            if($res->rowCount() == 9){
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    if($data){
                        $this->id[] = $data['id_dj'];
                        $this->content[] = $data['dj_content'];
                        $this->nbr_foe[] = $data['dj_foe'];
                        $this->x[] = $data['dj_x'];
                        $this->y[] = $data['dj_y'];
                        $this->up[] = $data['dj_u'];
                        $this->right[] = $data['dj_r'];
                        $this->down[] = $data['dj_d'];
                        $this->left[] = $data['dj_l'];
                        $this->is_discovered[] = $data['is_discovered'];
                    }
                }
                //get the number of allies on the 9 areas arround the player
                $this->getAllyNbr();
            } else { 
                $query = 'SELECT id_dj, dj_content, dj_x, dj_y, dj_u, dj_r, dj_d, dj_l, dj_foe, is_discovered
                    FROM '.$area.' 
                    WHERE id_dj='.$id_dj.' AND dj_x = '.$x.' AND dj_y = '.$y;
                    //$this->query = $query;
                    try {
                        $res = $this->database->mysql->query($query);
                        $data=$res->fetch(PDO::FETCH_ASSOC);
                        if($data) {
                            $this->id[] = $data['id_dj'];
                            $this->content[] = $data['dj_content'];
                            $this->nbr_foe[] = $data['dj_foe'];
                            $this->x[] = $data['dj_x'];
                            $this->y[] = $data['dj_y'];
                            $this->up[] = $data['dj_u'];
                            $this->right[] = $data['dj_r'];
                            $this->down[] = $data['dj_d'];
                            $this->left[] = $data['dj_l'];
                            $this->is_discovered[] = $data['is_discovered'];
                        }
                    } catch (PDOException $e){
                        echo $e->getMessage();
                        //$_SESSION['yoda_debug']=$e->getMessage()."\n";
                        return false;
                    }
                //get the number of allies on the 9 areas arround the player
                $this->getAllyNbr(1);
            }
        } catch (PDOException $e){
             $e->getMessage();
                //$_SESSION['yoda_debug']=$e->getMessage()."\n";
             return false;
        }

        
        //check if the area is the dj exit
        $query = 'SELECT id_map_area FROM map_dj WHERE dj_x='.$_SESSION['player_pos_x'].' AND dj_y='.$_SESSION['player_pos_y'].' LIMIT 1'; //Limit 1 ou alors indexer la colonne
        try {
            $res = $this->database->mysql->query($query);
            if($res) { //s'il y a un dj ici
                $data=$res->fetch(PDO::FETCH_ASSOC);
                if($res->rowCount() > 0){
                    $this->id_map_exit = $data['id_map_area'];
                    $this->is_cave_exit = true;
                }
            }
        } catch (PDOException $e){
            $e->getMessage();
            return false;
        }

        //check if the area is the dj2 entry
        $query = 'SELECT id_dj_dj2 FROM dj_dj2 WHERE dj2_x='.$_SESSION['player_pos_x'].' AND dj2_y='.$_SESSION['player_pos_y'].' LIMIT 1'; //Limit 1 ou alors indexer la colonne
        try {
            $res = $this->database->mysql->query($query);
            if($res) { //s'il y a un dj ici
                $data=$res->fetch(PDO::FETCH_ASSOC);
                if($res->rowCount() > 0){
                    $this->id_map_exit = $data['id_area'];
                    $this->is_dj2_entry = true;
                }
            }
        } catch (PDOException $e){
            $e->getMessage();
            return false;
        }
        
    }

    public function getAreasDataAbyss($area, $x, $y, $id_abyss)
    {
        $query = 'SELECT id_abyss, abyss_content, abyss_foe, abyss_x, abyss_y, abyss_r, abyss_g, abyss_b, is_discovered
                    FROM abyss
                    WHERE id_abyss='.$id_abyss.' AND abyss_x BETWEEN '.($x-1).' AND '.($x+1).' AND abyss_y BETWEEN '.($y-1).' AND '.($y+1).'
                    ORDER BY abyss_y ASC, abyss_x ASC'; // Y inversé par rapport à la map et au dj.
        //$this->query = $query;
        try {
            $err = '';
            $res = $this->database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $this->id[] = $data['id_abyss'];
                    $this->content[] = $data['abyss_content'];
                    $this->nbr_foe[] = $data['abyss_foe'];
                    $this->x[] = $data['abyss_x'];
                    $this->y[] = $data['abyss_y'];
                    $this->r[] = $data['abyss_r'];
                    $this->g[] = $data['abyss_g'];
                    $this->b[] = $data['abyss_b'];
                    $this->is_discovered[] = $data['is_discovered'];
                }
                
                /*if((sizeof($this->id)) < 9){
                    $query = 'SELECT id_abyss, abyss_content, abyss_foe, abyss_x, abyss_y, abyss_r, abyss_g, abyss_b, abyss_a 
                        FROM '.$area.' 
                        WHERE id_abyss='.$id_abyss.' AND abyss_x '.$x.' AND abyss_y '.$y;
                    //$this->query = $query;
                    try {
                        $res = $this->database->mysql->query($query);
                        if($res) {
                            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                                $this->id[] = $data['id_abyss'];
                                $this->content[] = $data['abyss_content'];
                                $this->nbr_foe[] = $data['abyss_foe'];
                                $this->x[] = $data['abyss_x'];
                                $this->y[] = $data['abyss_y'];
                                $this->r[] = $data['abyss_r'];
                                $this->g[] = $data['abyss_g'];
                                $this->b[] = $data['abyss_b'];
                                $this->a[] = $data['abyss_a'];
                            }
                        }
                    } catch (PDOException $e){
                        $e->getMessage();
                           //$_SESSION['yoda_debug']=$e->getMessage()."\n";
                        return false;
                    }
                }*/
            }
        }
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction add action : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);

        //get the number of allies on the 9 areas arround the player
        $this->getAllyNbr();

        //check if the area the abyss exit
        $query = 'SELECT id_city FROM abyss_city WHERE id_abyss='.$_SESSION['id_abyss'].' AND abyss_x='.$_SESSION['player_pos_x'].' AND abyss_y='.$_SESSION['player_pos_y'].' LIMIT 1'; //Limit 1 ou alors indexer la colonne
        try {
            $res = $this->database->mysql->query($query);
            if($res) { //s'il y a une sortie d'abysse ici
                $data=$res->fetch(PDO::FETCH_ASSOC);
                if($res->rowCount() > 0){
                    $this->id_map_exit = $data['id_city'];
                    $this->is_abyss_exit = true;
                }
            }
        } catch (PDOException $e){
             $e->getMessage();
                //$_SESSION['yoda_debug']=$e->getMessage()."\n";
             return false;
        }

        //check if the area the hell entry
        $query = 'SELECT id_hell FROM abyss_hell WHERE id_abyss='.$_SESSION['id_abyss'].' AND abyss_x='.$_SESSION['player_pos_x'].' AND abyss_y='.$_SESSION['player_pos_y'].' LIMIT 1'; //Limit 1 ou alors indexer la colonne
        try {
            $res = $this->database->mysql->query($query);
            if($res) { //s'il y a une entrée d'enfer ici
                $data=$res->fetch(PDO::FETCH_ASSOC);
                if($res->rowCount() > 0){
                    $this->id_hell_entry = $data['id_hell'];
                    $this->is_hell_entry=true;
                }
            }
        } catch (PDOException $e){
             $e->getMessage();
                //$_SESSION['yoda_debug']=$e->getMessage()."\n";
             return false;
        }
    }

    public function getAreasDataHell($area, $x, $y, $id_hell){

        
        
        $query = 'SELECT id_hell, hell_content, hell_foe, hell_x, hell_y
                    FROM '.$area.' 
                    WHERE id_hell='.$id_hell.' AND (hell_x BETWEEN '.($x-1).' AND '.($x+1).') AND (hell_y BETWEEN '.($y-1).' AND '.($y+1).')
                    ORDER BY hell_y DESC, hell_x ASC';
        //$this->query = $query;
        try {
            $res = $this->database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $this->id[] = $data['id_hell'];
                    $this->content[] = $data['hell_content'];
                    $this->nbr_foe[] = (int)$data['hell_foe'];
                    $this->x[] = $data['hell_x'];
                    $this->y[] = $data['hell_y'];
                }
                
                /*if((sizeof($this->id)) < 9){ //if map limit
                    $query = 'SELECT id_hell, hell_content, hell_foe, hell_x, hell_y, hell_r, hell_g, hell_b, hell_a 
                    FROM '.$area.'  
                        WHERE id_hell='.$id_hell.' AND hell_x '.$x.' AND hell_y '.$y;
                    //$this->query = $query;
                    try {
                        $res = $this->database->mysql->query($query);
                        if($res) {
                            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                                $this->id[] = $data['id_hell'];
                                $this->content[] = $data['hell_content'];
                                $this->nbr_foe[] = $data['hell_foe'];
                                $this->x[] = $data['hell_x'];
                                $this->y[] = $data['hell_y'];
                                $this->r[] = $data['hell_r'];
                                $this->g[] = $data['hell_g'];
                                $this->b[] = $data['hell_b'];
                                $this->a[] = $data['hell_a'];
                            }
                        }
                    } catch (PDOException $e){
                        $e->getMessage();
                           //$_SESSION['yoda_debug']=$e->getMessage()."\n";
                        return false;
                    }
                }*/
            }
        } catch (PDOException $e){
             $e->getMessage();
                //$_SESSION['yoda_debug']=$e->getMessage()."\n";
             return false;
        }

        // Win the game if there are no more foes in hell BUG
        //$this->checkIfVictory();

        //get the number of allies on the 9 areas arround the player
        $this->getAllyNbr();

        //check if the area the hell exit
        if($this->area == 'hell' && $this->x[4]=='0' && $this->y[4]=='0'){
            $this->is_hell_exit = true;
        }

    }

    public function getAreasDataDj2($area, $x, $y, $id_dj2)
    {
        $query = 'SELECT id_dj2, dj2_content, dj2_foe, dj2_x, dj2_y
                    FROM '.$area.' 
                    WHERE id_dj2='.$id_dj2.' AND (dj2_x BETWEEN '.($x-1).' AND '.($x+1).') AND (dj2_y BETWEEN '.($y-1).' AND '.($y+1).')
                    ORDER BY dj2_y DESC, dj2_x ASC';

        $this->query = $query;
        try {
            $res = $this->database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $this->id[] = $data['id_dj2'];
                    $this->content[] = $data['dj2_content'];
                    $this->nbr_foe[] = $data['dj2_foe'];
                    $this->x[] = $data['dj2_x'];
                    $this->y[] = $data['dj2_y'];
                }
            }
        } catch (PDOException $e){
             echo $e->getMessage();
             return false;
        }

        //check if the area is the dj2 exit and get the id of the dj
        $query = 'SELECT id_dj FROM dj_dj2 WHERE dj2_x='.$_SESSION['player_pos_x'].' AND dj2_y='.$_SESSION['player_pos_y'].' LIMIT 1'; //Limit 1 ou alors indexer la colonne
        try {
            $res = $this->database->mysql->query($query);
            if($res) { //s'il y a une sortie de dj ici
                $data=$res->fetch(PDO::FETCH_ASSOC);
                $this->id_map_exit = $data['id_dj'];
            }
        } catch (PDOException $e){
             echo $e->getMessage();
             return false;
        }

        //get the number of allies on the 9 areas arround the player
        $this->getAllyNbr();
    }

    protected function getAllyNbr($is_border=0)
    {
        $posx = $_SESSION['player_pos_x'];
        $posy = $_SESSION['player_pos_y'];
        $city = $_SESSION['id_city'];
        $area = $_SESSION['player_area'];

        // Check if the player is on map border (DJ only)
        if($is_border == 0){

            // https://stackoverflow.com/questions/12789396/how-to-get-multiple-counts-with-one-sql-query 
            // COUNT multiple columns (no perf problems, even if no index, as no subquery)
            $query = 'SELECT id_player,
                            sum(case when (player_pos_x = '.$posx.' and player_pos_y = '.$posy.' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS centerAreaCount,
                            sum(case when (player_pos_x = '.($posx+1).' and player_pos_y = '.$posy.' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS eastAreaCount,
                            sum(case when (player_pos_x = '.($posx-1).' and player_pos_y = '.$posy.' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS westAreaCount,
                            sum(case when (player_pos_x = '.$posx.' and player_pos_y = '.($posy+1).' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS northAreaCount,
                            sum(case when (player_pos_x = '.$posx.' and player_pos_y = '.($posy-1).' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS southAreaCount,
                            sum(case when (player_pos_x = '.($posx+1).' and player_pos_y = '.($posy+1).' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS northEastAreaCount,
                            sum(case when (player_pos_x = '.($posx+1).' and player_pos_y = '.($posy-1).' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS southEastAreaCount,
                            sum(case when (player_pos_x = '.($posx-1).' and player_pos_y = '.($posy+1).' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS northWestAreaCount,
                            sum(case when (player_pos_x = '.($posx-1).' and player_pos_y = '.($posy-1).' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS southWestAreaCount
                        FROM player
                        GROUP BY id_player';
            $stmt = $this->database->mysql->prepare($query);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            $centerAreaCount = $row[0]->centerAreaCount -1; //the -1 removes the player itself
            $eastAreaCount = $row[0]->eastAreaCount;
            $westAreaCount = $row[0]->westAreaCount;
            $northAreaCount = $row[0]->northAreaCount;
            $southAreaCount = $row[0]->southAreaCount;
            $northEastAreaCount = $row[0]->northEastAreaCount;
            $southEastAreaCount = $row[0]->southEastAreaCount;
            $northWestAreaCount = $row[0]->northWestAreaCount;
            $southWestAreaCount = $row[0]->southWestAreaCount;
            //$centerAreaCount = $row[0]["centerAreaCount"]; // only if  fetchAll(PDO::FETCH_BOTH ou ASSOC); (other method)

            // return the array in the right order (north-west to south-east)
            $this->nbr_ally[] = $northWestAreaCount;
            $this->nbr_ally[] = $northAreaCount;
            $this->nbr_ally[] = $northEastAreaCount;
            $this->nbr_ally[] = $westAreaCount;
            $this->nbr_ally[] = $centerAreaCount;
            $this->nbr_ally[] = $eastAreaCount;
            $this->nbr_ally[] = $southWestAreaCount;
            $this->nbr_ally[] = $southAreaCount;
            $this->nbr_ally[] = $southEastAreaCount;

        }  else  {

            $query = 'SELECT id_player,
                            sum(case when (player_pos_x = '.$posx.' and player_pos_y = '.$posy.' and id_city='.$city.' and player_area="'.$area.'") then 1 else 0 end) AS centerAreaCount
                        FROM player
                        GROUP BY id_player';
            $stmt = $this->database->mysql->prepare($query);
            $stmt->execute();
            $row = $stmt->fetchAll(PDO::FETCH_OBJ);
            
            $centerAreaCount = $row[0]->centerAreaCount -1; //the -1 removes the player itself

            // return the array 
            $this->nbr_ally[] = $centerAreaCount;

        }

    }

    protected function checkIfVictory()
    {
        $query = 'SELECT SUM(hell_foe) as foeCount 
                    FROM hell 
                    WHERE id_hell='.$id_hell;
                    
        try
        {
            $this->$foeNbrInHell = $this->database->mysql->query($query)->fetchColumn();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $this->log->addLog('Erreur dans la requête SQL de destroyCity : '. $e->getMessage() . ' /// ' . $query, 'error', $_SESSION['id_player']);
            echo "Une erreur est survenue, merci de recharger la page.";
            die();
        }

        if($this->$foeNbrInHell == 0){
          //  header('Location:index.php?page=victory');
            $this->log->addLog('test ok : il y a 0 foe in hell. décommenter la redirection vers victory', 'database');
            die();
        }

    }

}