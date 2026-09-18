<?php

/**
 * 
 * Class to handle the account data and actions
 * 
 */

class Account
{
    private $userTokenId = '';

    private $database;
    private $log;
    
    public function __construct($database, $log, $id_token)
    {
        // Objects
        $this->database = $database;
        $this->log = $log;

        // Variables
        $this->userTokenId = $id_token;
    }

    /**
     * Check ig the player already created an account using its google account ID
     */
    public function checkIfAccountExists() : string
    {
        $query="SELECT acc_google_token_id FROM account
            WHERE acc_google_token_id = :token_id
            LIMIT 1";
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':token_id' => $this->userTokenId));
        }
        catch (PDOException $e)
        {
            //$this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
            die();
        }
        
        if($sth->rowCount() > 0)
        {
            return "1";
        }
        else
        {
            return "0";
        }
    }

    /**
     * Get user data and store them in session
     */
    public function loginUser()
    {
        $this->database->mysql->beginTransaction();

        // Envoie le joueur vers home.php s'il n'est dans aucune ville
        $playerIsInsideCity = $this->checkIfPlayerInCity();
    
        // If the user is not inside a city. Connect the user with the data not linked to a city
        if ($playerIsInsideCity == 0)
        {
            $query='SELECT p.id_player, 
                           p.id_city, 
                           p.id_dj, 
                           p.id_abyss, 
                           p.id_camp, 
                           p.player_area, 
                           p.player_pos_x, 
                           p.player_pos_y, 
                           p.player_name, 
                           p.player_thumbs,
                           a.is_new, 
                           a.acc_token, 
                           a.email
                    FROM player p INNER JOIN account a ON a.id_player = p.id_player
                    WHERE a.acc_google_token_id= :token_id';
    
            try
            {
                $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $sth->execute(array(':token_id' => $this->userTokenId));  
                $data = $sth->fetchAll();
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
                die();
            }
    
            //player vars
            $_SESSION['id_player'] = $data[0]['id_player'];
            $_SESSION['id_city'] = $data[0]['id_city'];
            $_SESSION['player_area'] = $data[0]['player_area'];
            //$_SESSION['player_sub_area'] = $data[0]['player_area'];
    
            //sub area (plain, forest) will be checked on map loading.
            //sub area (dj, abyss, dj2, hell) will be checked on page loading
            $_SESSION['player_sub_area'] = '';
    
            $_SESSION['player_pos_x'] = $data[0]['player_pos_x'];
            $_SESSION['player_pos_y'] = $data[0]['player_pos_y'];
            $_SESSION['cb_token'] = $data[0]['acc_token'];
            $_SESSION['email'] = $data[0]['email'];
    
            $_SESSION['player_name'] = $data[0]['player_name'];

            $_SESSION['id_bld'] = [];
    
            //Redirect the user to the home page (only if not called w/ ajax)
            //header("Location: index.php?page=home");
            //die();
        }

        // If the player is inside a city, select player AND city data on connection
        else
        {
            $query='SELECT p.id_player, p.id_city, p.id_dj, p.id_abyss, p.id_camp, p.player_area, p.player_pos_x, p.player_pos_y, 
                        p.player_name, p.player_thumbs,
                        plb.player_food, plb.player_morale,
                        a.is_new, a.acc_token, a.email,
                        city.city_name, city.city_img_path
                    FROM (((player p
                        INNER JOIN account a           ON a.id_player = p.id_player)
                        INNER JOIN city                ON city.id_city = p.id_city)
                        INNER JOIN player_life_bar plb ON p.id_player = plb.id_player)
                        WHERE a.acc_google_token_id= :token_id';
        
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
            try
            {
                $sth->execute(array(':token_id' => $this->userTokenId));
                $data = $sth->fetchAll();
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
                die();
            }
        
            // Check if the user exists (normally already done in the dedicated method)
            if($sth->rowCount() == 0)
            { 
                $this->database->mysql->rollBack();
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                $err = 'Aucune ligne renvoyée avec l\'ID token fournit.';         
                $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
                die();
            }
        
            //player vars
            $_SESSION['id_player'] = $data[0]['id_player'];
            $_SESSION['id_city'] = $data[0]['id_city'];
            $_SESSION['player_area'] = $data[0]['player_area'];
            $_SESSION['player_sub_area'] = $data[0]['player_area'];
        
            //sub area (plain, forest) will be checked on map loading.
            //sub area (dj, abyss, dj2, hell) will be checked on page loading
            $_SESSION['player_sub_area'] = '';
        
            $_SESSION['player_pos_x'] = $data[0]['player_pos_x'];
            $_SESSION['player_pos_y'] = $data[0]['player_pos_y'];
            $_SESSION['cb_token'] = $data[0]['acc_token'];
            $_SESSION['email'] = $data[0]['email'];
        
            //if the player is inside a dj. Used in loadMap() to load the dj area
            if($data[0]['id_dj'] != 0){
                $_SESSION['id_dj'] = $data[0]['id_dj'];
            }
        
            //if the player is inside dj2
            if($_SESSION['player_area'] == 'dj2'){
                $_SESSION['id_dj2'] = $data[0]['id_dj'];
            }
        
            //if the player is inside the abyss
            if($data[0]['id_abyss'] != 0){
                $_SESSION['id_abyss'] = $data[0]['id_abyss'];
            }
        
            //if the player is inside hell
            if($_SESSION['player_area'] == 'hell'){
                $_SESSION['id_hell'] = $data[0]['id_abyss'];
            }
        
            // Si le joueur est dans un camp
            if($data[0]['id_camp'] != 0){
        
                // Vérifier que le camp existe toujours
        
                
                $_SESSION['player_area'] = 'camp';
            }
            
            $_SESSION['player_name'] = $data[0]['player_name'];
            //$_SESSION['player_title'] = $data[0]['player_title'];
            
            //life indicators -> dans ctrl_life_bars.php car appel à chaque page.
            //  $_SESSION['player_food'] = $data['player_food'];
            //  $_SESSION['player_morale'] = $data['player_morale'];
            //  $_SESSION['player_shield'] = $data['player_shield'];
            //  $_SESSION['player_head'] = $data['player_head'];
            //  $_SESSION['player_mask'] = $data['player_mask'];
            //  $_SESSION['player_upper'] = $data['player_upper'];
            //  $_SESSION['player_lower'] = $data['player_lower'];
            
            //city vars
            $_SESSION['city_name'] = $data[0]['city_name'];
            $_SESSION['city_img_path'] = $data[0]['city_img_path'];
            
            $_SESSION['is_new'] = $data[0]['is_new'];
        

            #region Player Stats
        
            $action = new Action($this->database, $this->log);
            $stat = new Stat($this->database, $this->log, $action);
        
            // Get the stat list
            $_SESSION['player_stat'] = $stat->getPlayerStats();
        
            // Define the max invent size based on the "carry" stat
            $_SESSION['max-invent'] = $stat->defineMaxInventSize($_SESSION['player_stat']);
        
            // Define the master session, if the lvl of the best stat is worth twice the value of the second.
            $_SESSION['is_master'] = 0;
            $_SESSION['master_stat'] = '';
        
            $stat->defineMasterStat($_SESSION['player_stat'], $_SESSION['is_master'], $_SESSION['master_stat']);
        
            
            #endregion
        
            
            // Get the local player's statistics
            $_SESSION['player_local_statistics'] = array();
            
            $query='SELECT slp.amount, s.statistic_name, s.statistic_descr
                        FROM `statistic` s INNER JOIN statistic_local_player slp ON slp.id_statistic = s.id_statistic
                        WHERE slp.id_player = '.$_SESSION['id_player'].'
                        ORDER BY slp.id_statistic';
            try 
            {
                $res=$this->database->mysql->query($query);
                $name = '';
                if ($res) 
                {
                    while($data=$res->fetch(PDO::FETCH_ASSOC)) 
                    {
                        $name = $data['statistic_name'];
                        $_SESSION['player_local_statistics'][$name]['amount'] = $data['amount'];
                        $_SESSION['player_local_statistics'][$name]['descr'] = $data['statistic_descr'];
                    }
                    unset($name);
                }
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
                die();
            }
        
            //go to the correct page
            /*
            if($_SESSION['is_new']){
                $query = "UPDATE account SET is_new=0";
                $database->mysql->query($query);
                unset($_SESSION['is_new']);
                header("location: index.php?page=horlogis");
            } else {
                header("location: index.php?page=".$_SESSION['player_area']);
            }*/
        
            //get max-city-invent 
            
            // get effects of built buildings if inside a city
            $_SESSION['id_bld'] = [];
            if($_SESSION['id_city'] != 0)
            {
                $query="SELECT id_bld FROM building_city WHERE id_city=".$_SESSION['id_city']." AND is_built=1";
                $result=[];
                try
                {
                    $res = $this->database->mysql->query($query);
                    if($res)
                    {
                        // Empty array is no building built
                        //if(count($res->fetchAll(PDO::FETCH_ASSOC)) > 0)
                        //{
                            while($data=$res->fetch(PDO::FETCH_ASSOC))
                            {
                                $_SESSION['id_bld'][] = $data['id_bld']; // access with in_array
                            }
                        //}
                        //else
                        //{
                    //     $_SESSION['id_bld'] = [];
                    // }
                    }
                } 
                catch (PDOException $e)
                {
                    $this->database->mysql->rollBack();
                    $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                    $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                    $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
                    die();
                }
            }
        }

        //login session variables
        $_SESSION['player_is_logged_in'] = 1;

        $this->database->mysql->commit();
    }
    
    /**
     * Create a new account with the received Token ID.
     */
    public function registerUser($username)
    {
        $this->database->mysql->beginTransaction();
        
        // The username must be below 20 chars
        $maxUserNameLenght = 20;
        if(strlen($username) > $maxUserNameLenght)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = 'Votre nom d\'utilisateur doit faire moins de 20 charactères.';
            header("location: index.php?page=login");
            die();
        }
    
        // The username must start with a capital.
        if(preg_match('/[^A-Z]/', substr( $username, 0, 1 ) ))
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = 'Votre nom d\'utilisateur doit commencer par une majuscule.';
            header("location: index.php?page=login");
            die();
        }
    
        // The username must not contain any spacial char or number, except space
        if (preg_match('/[^a-zA-Z\s]+/', $username))
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = 'Votre nom d\'utilisateur ne doit pas contenir de charactères spéciaux, d\'accents ou de chiffres.';
            header("location: index.php?page=login");
            die();
        }
    
        // sanitize inputs
        $sanitizedUsername = Tools::validateInput($username);
    
        // Assign a unique #ID to the username to allow copies
        // TODO LATER
    

    
        //get register date to fit with the elkarte DB (10 digits)
        //$registerDate = (int) microtime(true); //equivalent
        $registerDate = time();
    
        //create account token to connect to node.js
        $bytes = bin2hex(random_bytes(255)); //more than 255 chars
        $token = substr($bytes,255); //255 chars for DB
    
        ///amèl : use transactions https://stackoverflow.com/questions/5178697/mysql-insert-into-multiple-tables-database-normalization 
    
        //populate the player table
        $query="INSERT INTO player(player_name)
                VALUES (:username)";
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    
        try
        {
            $sth->execute(array(':username' => $sanitizedUsername));
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
            die();
        }
    
        //select the last account/player id
        $newIdPlayer = $this->database->mysql->lastInsertId();
    
        //populate the account table
        $query="INSERT INTO account(id_player, acc_username, acc_password, acc_token, user_ip, register_date, is_new, acc_google_token_id)
                VALUES (".$newIdPlayer.", :username, :passwd, '".$token."', :ip, :regDate, 1, :id_token)";
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    
        try
        {
            $sth->execute(array
            (
                ':username' => $sanitizedUsername, 
                ':passwd' => '', 
                ':ip' => '',
                ':regDate' => $registerDate,
                ':id_token' => $this->userTokenId
            ));
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
            die();
        }
    
        //populate the player stat table
        $query="INSERT INTO player_stat(id_player, id_stat)
                VALUES (:id, :id_stat)";
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        for($i=1; $i<=Constants::$numberOfStats; $i++)
        {
            try
            {
                $sth->execute(array(':id' => $newIdPlayer, ':id_stat' => $i));
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
                die();
            }
        }
    
        //populate the player global statistics table
        // TODO ?

        // Populate the player vote table with empty votes for each vote category
        $query="INSERT INTO player_vote(id_player, id_vote, vote_type)
                VALUES (:idPlayer, :idVote, :voteType)";
        try
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':idPlayer' => $newIdPlayer, ':idVote' => 0, ':voteType' => "bld"));
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
            die();
        }
    
        // Registration complete ! 
        $_SESSION['validMsg'] = 'Votre compte a été créé avec succès.';

        $this->database->mysql->commit();
    }

    /**
     * On connection, check if the player is inside a city. If yes, load the player and city data. If not, only load the player data.
     */
    public function checkIfPlayerInCity() : int
    {
        $query="SELECT p.id_city
                FROM player p INNER JOIN account a ON p.id_player = a.id_player
                WHERE a.acc_google_token_id= :token_id
                LIMIT 1";
    
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    
        try
        {
            $sth->execute(array(':token_id' => $this->userTokenId));
            $data = $sth->fetchAll();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
            die();
        }
    
        if($data[0]['id_city'] == "0")
            return 0;
        else   
            return 1;
    
    }
}