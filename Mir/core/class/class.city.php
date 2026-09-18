<?php

/**
 * 
 * Class containing city related methods (create, join, leave etc)
 * 
*/

include(dirname(__FILE__)."/class.build.php");

class City
{
    // Classes
    private $database;
    private $log;
    private $news;
    
    // Sessions
    private $idCity;
    private $idPlayer;
    private $playerName;

    /// Propriétés
    
    // City
    public $newIdCity;

    /**
     * Id of the DJ to be created. For now, 1 DJ per map
     */
    private $newIdDj; 

    // Map
    private $map_size;
    private $map_x = [];
    private $map_y = [];
    private $map_r = [];
    private $map_g = [];
    private $map_b = [];
    private $map_content = [];
    private $map_area_type = [];
    private $map_foe = [];
    private $map_foe_hp = [];
    private $map_max_foe = [];
    
    private $map_limitLeftX;
    private $map_limitRightX;
    private $map_limitUpY;
    private $map_limitDownY;

    // Dj
    private $dj_size = [];
    private $dj_x = [];
    private $dj_y = [];
    private $dj_up = [];
    private $dj_right = [];
    private $dj_down = [];
    private $dj_left = [];
    private $dj_content = [];
    //private $dj_area_type = [];
    private $dj_foe = [];
    private $dj_foe_hp = [];
    private $dj_max_foe = [];
    
    private $dj_EntryOnMapX;
    private $dj_EntryOnMapY;

    // Abyss
    private $abyss_size;
    private $abyss_x = [];
    private $abyss_y = [];
    private $abyss_r = [];
    private $abyss_g = [];
    private $abyss_b = [];
    private $abyss_content = [];
    private $abyss_foe = [];
    private $abyss_foe_hp = [];
    private $abyss_max_foe = [];

    // Gates
    /**
     * X coordinate of DJ entry inside the DJ
    */
    private $djEntryInsideX = 8;
    
    /**
     * Y coordinate of DJ entry inside the DJ
    */
    private $djEntryInsideY = -8;

    /**
     * X coordinate of abyss entry inside the abysss
    */
    private $abyssEntryInsideX;

    /**
     * Y coordinate of abyss entry inside the abyss
    */
    private $abyssEntryInsideY;

    /**
     * X coordinate of hell entry inside the abyss
    */
    private $hellEntryOutsideX;

    /**
     * Y coordinate of hell entry inside the abyss
    */
    private $hellEntryOutsideY;



    public function __construct($database, $log, $news)
    {
        $this->database = $database;
        $this->log = $log;
        $this->news = $news;

        $this->idPlayer = $_SESSION['id_player'];
        $this->idCity = $_SESSION['id_city'];
    }

    #region Liste

    /**
     * Get the list of all cities alive
     */
    public function getAvailableCitiesList() : array
    {
        $query = 'SELECT c.*, cc.currday
                  FROM city c INNER JOIN cron_city cc ON c.id_city = cc.id_city
                  WHERE c.has_ended = 0';
        try
        {
            $sth = $this->database->mysql->prepare($query);
            $sth->execute();

            while($data=$sth->fetch(PDO::FETCH_ASSOC))
            {
                if ($data)
                {
                    $result[] = $data;
                }
            }
        }
        catch (PDOException $e)
        {
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";       
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();      
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        return $result;
    }

    #endregion

    #region join

    /**
     *  Check the first city available for the player, then join. If no city is available, create a new one.
     */
    // OLD : Rejoindre une ville au hasard. Remplacé par la sélection de ville.
    // public function joinCity()
    // {
    //     $prevIdCity = '';
    //     $err = '';

    //     // Check if the player has previously left the city, to make him go to another one.
    //     $query="SELECT prev_id_city FROM account
    //             WHERE id_player=".$this->idPlayer;
    //     try
    //     {
    //         $res=$this->database->mysql->query($query);
    //         if($res)
    //         {
    //             $data=$res->fetch(PDO::FETCH_ASSOC);
    //             $prevIdCity = $data['prev_id_city'];
    //         }
    //     }
    //     catch (PDOException $e)
    //     {
    //         $this->database->mysql->rollBack();
    //         $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";       
    //         $err = 'Erreur dans la requête SQL de la fonction joinCity1 : '.$e->getMessage();        
    //         $this->log->addLog($err, 'error', $this->idPlayer);
    //         die();
    //     }

    //     //check for free city, other than the previously selected
    //     //TODO also check for timer
    //     $query="SELECT id_city FROM city
    //         WHERE (player_nbr < ".MAX_CITY_PLAYERS.") AND id_city<>".$prevIdCity." AND has_ended = 0 LIMIT 1";
    //     try 
    //     {
    //         $res=$this->database->mysql->query($query);
    //         if($res->rowCount() > 0)
    //         {
    //             $data=$res->fetch(PDO::FETCH_ASSOC);
    //             $idCity = $data['id_city'];
    //             $this->addPlayerToCity($idCity);
    //         } 
    //         else
    //         {
    //             // No city is available
    //             $this->createNewCity();
    //         }
    //     }
    //     catch (PDOException $e)
    //     {
    //         $this->database->mysql->rollBack();

    //         $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
    //         $err = 'Erreur dans la requête SQL de la fonction joinCity2 : '.$e->getMessage();
            
    //         $this->log->addLog($err, 'error', $this->idPlayer);

    //         die();
    //     }
    // }

    /**
     *  The player joins the already existing city and all his data are initialized for the city
     *  @param string $idCity : ID de la ville à rejoindre.
     */
    public function addPlayerToCity($idCity)
    {
        //update player(id_city)
        $query="UPDATE player SET id_city=".$idCity.", player_area='inside'
                    WHERE id_player=".$this->idPlayer;
        try 
        {
            $this->database->mysql->query($query);
            $_SESSION['id_city'] = $idCity;
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        //update city(player_nbr)
        $query="UPDATE city SET player_nbr = player_nbr + 1
                    WHERE id_city=".$idCity;
        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        $this->initializePlayer($idCity);

        Build::getBuiltList($idCity, $this->database, $this->log, $this->idPlayer);

        // Finally add the new player in the news section
        $this->news->addNew("newPlayer", $_SESSION['player_name'], 1, $idCity, $this->idPlayer); // TODO remove player_name moche (provient de login)

    }

     /**
     *  Initialize the player's stats, statistics and inventory
     */
    public function initializePlayer($idCity)
    {
        // reinitialize the player stats
        $query1="UPDATE player_stat SET player_stat_lvl = 1, player_stat_xp = 0
                    WHERE id_player=".$this->idPlayer;

        // reinitialize the player's local statistics
        $query2="UPDATE statistic_local_player SET amount = 0
                    WHERE id_player=".$this->idPlayer;

        // initialize the player's inventory with basic items
        $query3="INSERT INTO city_item(id_item, id_city, id_player, item_pos) 
                VALUES
                    (1, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (3, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (4, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (5, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (6, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (7, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (8, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (9, ".$idCity.", ".$this->idPlayer.", 'invent'),
                    (10, ".$idCity.", ".$this->idPlayer.", 'invent')";

        //delete farm items for the player
        $query4="UPDATE city_farm SET is_alive = 0
                    WHERE id_player=".$this->idPlayer;

        //initialize the player life bars 
        $query5="UPDATE player_life_bar SET player_food = 100, player_morale=100
                    WHERE id_player=".$this->idPlayer;

        //reinitialize the player votes (every type)
        $query6="UPDATE player_vote SET id_vote = 0
                    WHERE id_player = ".$this->idPlayer;

        try
        {
            $this->database->mysql->query($query1);
            $this->database->mysql->query($query2);
            $this->database->mysql->query($query3);
            $this->database->mysql->query($query4);
            $this->database->mysql->query($query5);
            $this->database->mysql->query($query6);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();   
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }

    #endregion

    #region Create the City

    /**
     *  Comment
     *  @param string $myString : var descr
     *  @param array $myArray : var descr
     *  @param array $myArray : var descr
     */
    public function createNewCity()
    {
        $this->log->addLog('ok', 'error', $_SESSION['id_player']);

        // Create the city
        $this->addCity();

        $this->log->addLog('ok1', 'error', $_SESSION['id_player']);

        // Create the map
        $this->createMap();
        $this->storeMapInDB();
        $this->removeFoesOnCityEntry();
        $this->addDjEntry();
        $this->addMapLimits();

        $this->log->addLog('ok2', 'error', $_SESSION['id_player']);
        
        // Create the DJ
        $this->createDJ();
        $this->storeDjInDB();
        $this->addDjGates();
        $this->removeFoesOnDjEntry();

        $this->log->addLog('ok3', 'error', $_SESSION['id_player']);
        
        // Create the abyss
        $this->createAbyss();
        $this->log->addLog('ok31', 'error', $_SESSION['id_player']);
        $this->storeAbyssInDb();
        $this->log->addLog('ok32', 'error', $_SESSION['id_player']);
        $this->addAbyssGates();

        $this->log->addLog('ok4', 'error', $_SESSION['id_player']);
        
        // Create the DJ2 (end of the DJ)
        $this->createDJ2Content();
        $this->createAndStoreDJ2InDb();

        $this->log->addLog('ok5', 'error', $_SESSION['id_player']);
        
        // Create hell (end of abyss)
        $this->createAndStoreHellInDb();
        $this->createCampInHellEntry();

        $this->log->addLog('ok6', 'error', $_SESSION['id_player']);

        // Initialise buildings
        Build::InitializeBuildings($this->newIdCity, $this->database, $this->log, $this->idPlayer);

        $this->log->addLog('ok7', 'error', $_SESSION['id_player']);

        // add news of the city creation
        $this->news->addNew('newCity', 0, 1, $this->newIdCit, $this->idPlayer);

        $this->log->addLog('ok8', 'error', $_SESSION['id_player']);
    }
    
    public function addCity()
    {
        // define the start date (13 digits) and round it to the previous midnight.
        //$date = microtime(true);
        //$dateStart = (int)$date*1000;
        
        //TODO test à la création de la ville.
        $dateTest = time(); // nombre de secondes depuis janvier 1 1970 0:00
        $dateTest = $dateTest - ($dateTest % (60*60*24)); // nbre de secondes, jours compris MOINS nombre de secondes, jours non compris = nombre de jour arrondi à minuit.
        $dateTest *= 1000; // seconds to milliseconds, to fit the database lecture in cron.php (13 digits). Maybe use time() everywhere ?
        
        // create new city in DB and get the last inserted ID to avoid congruent requests
        $query = 'INSERT INTO city(player_nbr, `start_date`) VALUES(0, '.$dateTest.')';
        try 
        {
            $this->database->mysql->query($query);
            $this->newIdCity = $this->database->mysql->lastInsertId();
            $this->newIdDj = $this->newIdCity; // 1 dj par ville pour l'instant. Après il faudra une table avec id_dj en clé primaire pour avoir lastInsertId.
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        // Create a new cron for the city
        $query = 'INSERT INTO cron_city(id_city, date_begin, currday) 
                  VALUES('.$this->newIdCity.', 
                         '.$dateTest.',
                          1)';
        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }
    
    public function createMap()
    {
      $this->createMapFromPNG();
      $this->shiftMapIndexes();
      $this->setMapBiomes();
      $this->setMapContent();
      $this->setMapFoes();
    }
    
    public function createMapFromPNG()
    {
        // Get the array of pixel colors from the stored image
        $img = imagecreatefromstring(file_get_contents("../img/maps/map_100_100.png"));

        $width = imagesx($img);
        $height = imagesy($img);

        $this->map_size = $width * $height;

        for($x = 0; $x < $width; $x++)
        {
            for($y = 0; $y < $height; $y++)
            {
                // pixel color at (x, y)
                $color_index = imagecolorat($img, $x, $y);
                // make it human readable
                $color = imagecolorsforindex($img, $color_index);

                $this->map_r[] = $color["red"];
                $this->map_g[] = $color["green"];
                $this->map_b[] = $color["blue"];
                $this->map_x[] = $x;
                $this->map_y[] = $y;
            }
        }
    }
    
    public function shiftMapIndexes()
    {
        // Chgt d'indice pour avoir (0,0) au centre de la carte
        
        /*
        Before : 
            (0,0)   ...   (25,0)  ... (49,0) 
            ...
            (0,25)  ...   (25,25) ... (49,25) 
            ...
            (0,49)  ...   (25,49) ... (49,49) 

        After :
            (-25,25)   ...   (0,25)  ... (25,25) 
            ...             
            (-25,0)    ...   (0,0)   ... (25,0) 
            ...
            (-25,-25)  ...   (0,-25) ... (25,-25) 
        */

        $this->log->addLog($this->map_size, 'error', $this->idPlayer);
        $test = 'x avt : ' . implode(",", $this->map_x) . "\r\n";
        $test .= 'y avt : ' . implode(",", $this->map_y) . "\r\n";

        $maxCityX = 99; //to have -24 -> 25
        $maxCityY = 100; //to have 25 -> -24
        $newY = floor($maxCityY / 2);
        $compteurPosY = 0;
        // Pour tout x : x - floor(49/2) = x - 24.
        for($i=0;$i<$this->map_size;$i++)
        {
            $this->map_x[$i] -= floor($maxCityX/2);
        }
        // Pour tout y : toutes les suites de 49 cases prennent le même y. Diminue de 1 à la 49e case.
        for($i=0;$i<$this->map_size;$i++)
        {
            $this->map_y[$i] = $newY;
            if($compteurPosY == $maxCityY)
            {
                $newY -=1;
                $compteurPosY=0;
            }
            $compteurPosY++; // when it reaches 49, the next Y will have an offset $newY reduced by 1.
        }

        // for DEBUG only
        $test .= 'x apr : ' . implode(",", $this->map_x) . "\r\n";
        $test .= 'y apr : ' . implode(",", $this->map_y) . "\r\n";
        $this->log->addLog($test, 'error', $this->idPlayer);
        $this->database->mysql->rollBack();
        die();
    }
    
    public function setMapBiomes()
    {
          // Set area type and gate as a function of area color
          //$area_type = [];
  
          for ($i=0; $i < $this->map_size; $i++)
          {
              if ($this->map_r[$i]=="182" && $this->map_g[$i]=="255" && $this->map_b[$i]=="0")
              {
                  $this->map_area_type[$i]="field"; //OR $map_area_type[]="field";
              }
              else if ($this->map_r[$i]=="76" && $this->map_g[$i]=="255" && $this->map_b[$i]=="0")
              {
                  $this->map_area_type[$i]="forest_1";
              }
              else if ($this->map_r[$i]=="38" && $this->map_g[$i]=="127" && $this->map_b[$i]=="0")
              {
                  $this->map_area_type[$i]="forest_2";
              }
              else if ($this->map_r[$i]=="255" && $this->map_g[$i]=="0" && $this->map_b[$i]=="0")
              {
                  $this->map_area_type[$i]="forest_2";
              }
          }
    }
    
    public function setMapContent()
    {
          //create random content selon la zone
          for ($i=0; $i<$this->map_size; $i++)
          {
              $content[$i]='';
              $contentString = '';
              $is_discovered[$i] = 0;
  
              /// Plaine arbres 5-20%, rock : 0-5% orange 0-3% honey 0-3%, rare tree 1/10000 or 1 every 100 areas
              if($this->map_area_type[$i]=="field")
              {
                  for($j=0; $j<100; $j++)
                  {
                      $m = rand(1,10000);
                      if ($m>=500 && $m<2000)
                      {
                          $contentString .= 't';
                      }
                      else if ($m >= 2000 && $m<2500)
                      {
                          $contentString .= 'r';
                      }
                      else if ($m >= 2500 && $m <2800)
                      {
                          $contentString .= 'o';
                      }
                      else if ($m >= 2800 && $m <3100)
                      {
                          $contentString .= 'h';
                      }
                      // Rare tree
                      else if ($m == 9999)
                      {
                          $contentString .= 'u';
                      }
                      else
                      {
                          $contentString .= 'n';
                      }
                  }
              }
  
              /// Foret arbres 50-70 rock 0-5 orange 0-3 honey 0-3 rare tree 1/1000 or 1/10 areas.
              else if($this->map_area_type[$i]=="forest_1")
              {
                  for($j=0; $j<100; $j++)
                  {
                      $m = rand(1,10000);
                      if ($m>=100 && $m<7000)
                      {
                          $contentString .= 't';
                      }
                      else if ($m >= 7000 && $m <7500)
                      {
                          $contentString .= 'r';
                      }
                      else if ($m >= 7500 && $m <7800)
                      {
                          $contentString .= 'o';
                      }
                      else if ($m >= 7800 && $m <8100)
                      {
                          $contentString .= 'h';
                      }
                      // Rare tree
                      else if ($m >= 9990)
                      {
                          $contentString .= 'u';
                      }
                      else
                      {
                          $contentString .= 'n';
                      }
                  }
              }
  
              // Foret deep(2) :  arbres 25-40, arbres rares 40%, rock 0-2, orange 0, honey 0
              else if($this->map_area_type[$i]=="forest_2")
              {
                  for($j=0; $j<100; $j++)
                  {
                      $m = rand(1,10000);
                      if ($m>=100 && $m<4000)
                      {
                          $contentString . 't';
                      }
                      else if ($m>=4000 && $m<9000)
                      {
                          $contentString . 'u';
                      }
                      else if ($m >= 9000 && $m <9200)
                      {
                          $contentString . 'r';
                      }
                      else
                      {
                          $contentString . 'n';
                      }
                  }
              }
  
              /*
              // Montagne arbres 0-5 rocks 50-70 orange 0-2 honey 0-2
              if($area_type[$i]=="mountain"){
                  for($j=0; $j<100; $j++){
                      $n = rand(1,100);
                      if ($n>=1 && $n<6){
                          $content[$i] . 't';
                      }
                      else if ($n >= 6 && $n <64){
                          $content[$i] . 'r';
                      }
                      else if ($n >= 64 && $n <66){
                          $content[$i] . 'o';
                      }
                      else if ($n >= 66 && $n <68){
                          $content[$i] . 'h';
                      }
                      else {
                          $content[$i] . 'n';
                      }
                  }
              }
              */
  
              $this->map_content[$i] = $contentString;
  
          }
  
    }
    
    public function setMapFoes()
    {
        // populate foes on map 
        for ($i=0; $i<$this->map_size; $i++)
        {
            $this->map_foe[$i]=0;
            $this->map_foe_hp[$i]='';
            $this->map_max_foe[$i]=0;
      
            //40% empty, 30% 1 foe, 20% 2 foes, 10% 3 foes.
            $m = rand(1,100);
            switch($m)
            {
                case ($m <= 40):
                    break;
                case ($m > 40 && $m <= 70):
                    $this->map_foe[$i] = 1;
                    break;
                case ($m > 70 && $m <= 90):
                    $this->map_foe[$i] = 2;
                    break;
                default:
                    $this->map_foe[$i] = 3;
            }
      
            $foeNbr = $this->map_foe[$i];
            for($j=0; $j<$foeNbr; $j++)
            {
                $this->map_foe_hp[$i].='A';
            }
            
            // Set max number of foes for repop calculations
            $this->map_max_foe[$i]=$this->map_foe[$i];
        }
  
        // Populate Hordes and Titans
  
        // Do not add foe on (0,0) (city)
        for ($i=0; $i<$this->map_size; $i++)
        {
            if($this->map_x[$i]==0 && $this->map_y[$i]==0)
            {
                //x and y have the same index as the area is a square
                $this->map_foe[$i] = 0;
                $this->map_foe_hp[$i] = '';
                $this->map_max_foe[$i] = 0;
            }
        }
  
    }
    
    public function storeMapInDB()
    {
        $query="INSERT INTO map (id_city, x, y, r, g, b, content, foe, max_foe, foe_hp, area_type) values ";
        for ($i=0; $i<$this->map_size; $i++)
        {
           $query.="(".$this->newIdCity.", 
                ".$this->map_x[$i].", 
                ".$this->map_y[$i].", 
                ".$this->map_r[$i].", 
                ".$this->map_g[$i].", 
                ".$this->map_b[$i].", 
                '".$this->map_content[$i]."', 
                ".$this->map_foe[$i].", 
                ".$this->map_max_foe[$i].", 
                '".$this->map_foe_hp[$i]."', 
                '".$this->map_area_type[$i]."'),";
        }
        $query = substr($query, 0, -1);

        $this->log->addLog('map size : ' . $this->map_size, 'error', $this->idPlayer);

        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();   
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }
    
    public function removeFoesOnCityEntry()
    {
        // Remove the foes on city entry (0,0)
        $query="UPDATE map SET foe=0, max_foe=0, foe_hp='' 
                WHERE x=0 AND y=0 AND id_city=".$this->newIdCity;
        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
  
    }
    
    public function addDjEntry()
    {
        // Define the DJ entry (OUTSIDE dj). It is the only red dot on map.
        //$djEntryXout = 0;
        //$djEntryYout = 0;
        for ($i=0; $i < $this->map_size; $i++)
        {
            if ($this->map_r[$i]==255 && $this->map_g[$i]==0 && $this->map_b[$i]==0)
            {
                $this->dj_EntryOnMapX = $this->map_x[$i];
                $this->dj_EntryOnMapY = $this->map_y[$i];
            }
        }

        $this->log->addLog('dj_EntryOnMapX' . $this->dj_EntryOnMapX, 'error', $this->idPlayer);
        $this->log->addLog('dj_EntryOnMapX' . $this->dj_EntryOnMapY, 'error', $this->idPlayer);
        $this->log->addLog('dj_EntryOnMapX' . $this->map_size, 'error', $this->idPlayer);

        // Select the id area of defined X and Y
        $query="SELECT id FROM map 
                WHERE id_city=".$this->newIdCity." 
                  AND x=".$this->dj_EntryOnMapX." 
                  AND y=".$this->dj_EntryOnMapY;

        $res = $this->database->mysql->query($query);

        try
        {
            $res->execute();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        $row = $res->fetchAll(PDO::FETCH_OBJ);
        $this->idAreaDjEntry = $row[0]->id;
    }
    
    public function addMapLimits()
    {
        // Create walls on map limits by setting the content value of limit areas to '0'.
        $this->map_limitLeftX = -49;
        $this->map_limitRightX = 50;
        $this->map_limitUpY = 50;
        $this->map_limitDownY = -49;

        $query="UPDATE map SET content='' 
                WHERE x=".$this->map_limitLeftX." 
                   OR x=".$this->map_limitRightX." 
                   OR y=".$this->map_limitUpY." 
                   OR y=".$this->map_limitDownY;
        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
  
    }

    #endregion
    
    #region Create the DJ
    
    public function createDj()
    {
      $this->createRandomMaze();
      $this->shiftDjIndexes();
      $this->setDjFoes();
    }
    
    public function createRandomMaze()
    {
        //TODO
        // - add e, r, d, l as arrays
        // - add X, Y as arrays

        // Fonctionnement
        // Si on est parti au nord, on va avoir :
        // étape 1 :
            //sur la case d'où on est parti : $maze[$position] = 10111 // visited, N (sans mur) S (mur) E (mur) W (mur)
            // sur la case où on va : $maze[$position2] = 01011 // en cours, N (mur) S (sans mur) E (mur) W (mur)
        // étape 2 :
            // si on va encore au nord, on obtient pour la case en cours : $maze[$position2] = 10011 // visited, N (sans mur) S (sans mur) E (mur) W (mur)
        // > on a bien deux directions possibles sur chaque case. On a besoin de deux passages de boucle par case, chaque passage modifiant une direction de la case en cours et une direction de la case suivante.

        // ---
        // Pour avoir r[$i] etc, on prend la chaine $maze[$position] = 01001
        // Pour avoir x[$i] et y(i), il faut boucler sur dim_x et dim_z (déja fait dans le changement d'indices à la création)

        // $dim_x = Constants::$maze_dim_x;
        // $dim_z = Constants::$maze_dim_z;
        $dim_x = 50;
        $dim_z = 50;

        $maze = array();
        $moves = array();

        $cell_count = $dim_x*$dim_z;

        // Initialize the array with no direction available (the "1" are walls)
        for($position=0; $position<$cell_count; $position++)
        {
            $maze[$position] = "01111"; // visited, NSEW
        }

        $pos=0;
        $maze[0]{0} = 1; /// initial
        $visited = 1;

        // determine possible directions
        while($visited<$cell_count){
            $possible = ""; 
            if((floor($pos/$dim_x)==floor(($pos-1)/$dim_x)) and ($maze[$pos-1]{0}==0)){
                $possible .= "W";
            }
            if((floor($pos/$dim_x)==floor(($pos+1)/$dim_x)) and ($maze[$pos+1]{0}==0)){
                $possible .= "E";
            }
            if((($pos+$dim_x)<$cell_count) and ($maze[$pos+$dim_x]{0}==0)){
                $possible .= "S";
            }
            if((($pos-$dim_x)>=0) and ($maze[$pos-$dim_x]{0}==0)){
                $possible .= "N";
            }
            if($possible)
            {
                // Une nouvelle case a été visitée
                $visited ++;

                // keep the current position in a pile for backtracking
                array_push($moves,$pos); 

                // Choix d'une direction au hasard
                $direction = $possible{rand(0,strlen($possible)-1)};

                // Déplacement
                switch($direction)
                {
                    case "N":
                        // Le caractère position 1 (Nord) de la case en cours prend la valeur 0 à la place de 1 : il n'y a pas de mur au nord vu qu'on y va.
                        $maze[$pos]{1} = 0;

                        // Le caractère position 2 (Sud) de la case suivante prend la valeur 0 à la place de 1 : : il n'y a pas de mur au sud vu qu'on en vient.
                        $maze[$pos-$dim_x]{2} = 0;

                        // La nouvelle position est la précédente moins la largeur : on "monte" d'une case
                        $pos -= $dim_x;
                        break;
                    case "S":
                        $maze[$pos]{2} = 0;
                        $maze[$pos+$dim_x]{1} = 0;
                        $pos += $dim_x;
                        break;
                    case "E":
                        $maze[$pos]{3} = 0;
                        $maze[$pos+1]{4} = 0;
                        $pos ++;
                        break;
                    case "W":
                        $maze[$pos]{4} = 0;
                        $maze[$pos-1]{3} = 0;
                        $pos --;
                        break;
                }

                // La case en cours a été visitée, sa valeur position 0 passe à 1
                $maze[$pos]{0} = 1;
            }
            else
            {
                // backtracking : go to the last position
                $pos = array_pop($moves); 
            }
        }

        // The maze has been generated, now populate the important arrays
        for($position=0; $position<$cell_count; $position++) // pour 50*50, $position va de 0 à 2499
        { 
            $this->dj_x[] = fmod($position, $dim_x); // augmente de 1 en 1 jusqu'à dim_x puis repasse à 0
            $this->dj_y[] = floor($position / $dim_z); // n'augmente de 1 que quand on dépasse un multiple de dim_y

            //$maze[$position] = "01111"; // visited, NSEW
            $this->dj_up[] = substr($maze[$position], 1, 1);
            $this->dj_left[] = substr($maze[$position], 2, 1);
            $this->dj_down[] = substr($maze[$position], 3, 1);
            $this->dj_right[] = substr($maze[$position], 4, 1);
        }

        // for DEBUG only
        // $test = 'x : ' . implode(",", $this->dj_x) . "\r\n";
        // $test .= 'y : ' . implode(",", $this->dj_y) . "\r\n";
        // $test .= implode(",", $this->dj_up) . "\r\n";
        // $test .= implode(",", $this->dj_left) . "\r\n";
        // $test .= implode(",", $this->dj_down) . "\r\n";
        // $test .= implode(",", $this->dj_right) . "\r\n";
        //  $this->log->addLog($test, 'error', $this->idPlayer);
        // $this->database->mysql->rollBack();
        // die();
    }
    
    public function shiftDjIndexes()
    {
        //TODO AMEL MULTIPLE DJ ON MAP
        
        $this->dj_size = sizeof($this->dj_x) - 1;

        // TODO
        //adds empty areas around the maze with walls to deal w/ the map limit problem. So the real size is 21*21
        // -> Plus compliqué, non nécessaire. Pour l'instant, ne pas charger les 9 cases si elles n'existent pas. De toutes façons on ne mettra pas les monstres sur
        // les side map.

        // ...


        // Move the center from top-right to middle
        /*
        Before : 
            (0,0)   ...   (25,0)  ... (49,0) 
            ...
            (0,25)  ...   (25,25) ... (49,25) 
            ...
            (0,49)  ...   (25,49) ... (49,49) 


        After :
            (-25,25)   ...   (0,25)  ... (25,25) 
            ...             
            (-25,0)    ...   (0,0)   ... (25,0) 
            ...
            (-25,-25)  ...   (0,-25) ... (25,-25) 
        */

        $maxMazeX = 19; //to have -19 -> 20
        $maxMazeY = 20; //to have 20 -> -19
        $newY = floor($maxMazeY / 2);
        $compteurPosY = 0;

        // Pour tout x : x - floor(20/2) = x - 10.
        for($i=0;$i<$this->dj_size;$i++)
        {
            $this->dj_x[$i] -= floor($maxMazeX/2);
        }
        
        // Pour tout y : toutes les suites de 20 cases prennent le même y. Diminue de 1 à la 20e case.
        for($i=0;$i<$this->dj_size;$i++)
        {
            $this->dj_y[$i] = $newY;

            if($compteurPosY == $maxMazeY-1)
            { // the -1 is because $maxmazeY is 20 and Y goes from 0 to 19.
                $newY -=1;
                $compteurPosY=0;
            }
            $compteurPosY++; // when it reaches 20, the next Y will have an offset $newY reduced by 1.
        }
        
        // for DEBUG only
        // $test =  implode(",", $this->dj_x) . "\r\n";
        // $test .= implode(",", $this->dj_y) . "\r\n";
        // $this->log->addLog($test, 'error', $this->idPlayer);
        // $this->database->mysql->rollBack();
        // die();

    }
    
    public function setDjFoes()
    {
          // populate foes in dj, 10 foes everywhere
         // $foe_hp = [];
        //  $max_foe = [];
        
        for ($i=0; $i<$this->dj_size; $i++)
        {
            $this->dj_foe[$i]=0;
            $this->dj_foe_hp[$i]='';
            $this->dj_max_foe[$i]=0;
  
            //10 foes everywhere
            $this->dj_foe[$i] = 10;
            
            $foeNbr = $this->dj_foe[$i];
            for($j=0; $j<$foeNbr; $j++)
            {
                $this->dj_foe_hp[$i].='A';
            }
            
            $this->dj_max_foe[$i]=$this->dj_foe[$i];
        }
  
    }
    
    public function storeDjInDB()
    {
        $query="INSERT INTO dj(id_city, dj_x, dj_y, dj_u, dj_r, dj_d, dj_l, dj_foe, dj_max_foe, dj_foe_hp) values ";
        for ($i=0; $i<$this->dj_size; $i++)
        {
            $query.="(".$this->newIdCity.", 
                      ".$this->dj_x[$i].", 
                      ".$this->dj_y[$i].", 
                      ".$this->dj_up[$i].", 
                      ".$this->dj_right[$i].", 
                      ".$this->dj_down[$i].", 
                      ".$this->dj_left[$i].", 
                      ".$this->dj_foe[$i].", 
                      ".$this->dj_max_foe[$i].", 
                      '".$this->dj_foe_hp[$i]."'),";
        }
        //kill the last coma
        $query = substr($query, 0, -1);
        
        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
  
    }
    
    public function addDjGates()
    {
          
          // set the dj entry INSIDE (X,Y) AND OUTSIDE (area)
          $query="INSERT INTO map_dj(id_city, id_dj, id_area, dj_x, dj_y) 
                  VALUE(".$this->newIdCity.", 
                        ".$this->newIdDj.", 
                        ".$this->idAreaDjEntry.", 
                        ".$this->djEntryInsideX.", 
                        ".$this->djEntryInsideY.")";
          try
          {
              $this->database->mysql->query($query);
          }
          catch (PDOException $e)
          {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
          }
          
          // set the dj exit to dj2
          $djEntryDj2X = 0;
          $djEntryDj2Y = 9;
          $query="INSERT INTO dj_dj2(id_dj, id_dj2, dj2_x, dj2_y) 
                  VALUE(".$this->newIdDj.", 
                        ".$this->newIdDj.", 
                        ".$djEntryDj2X.", 
                        ".$djEntryDj2Y.")";
          try
          {
              $this->database->mysql->query($query);
          }
          catch (PDOException $e)
          {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
          }
  
    }
    
    public function removeFoesOnDjEntry()
    {
        // Remove the foes on dj entry
        $query="UPDATE dj SET dj_foe=0, dj_max_foe=0, dj_foe_hp='' 
                WHERE dj_x=".$this->djEntryInsideX." AND dj_y=".$this->djEntryInsideY." AND id_city=".$this->newIdCity;

        try 
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }
    
    #endregion
    
    #region Create the Abyss
    
    public function createAbyss()
    {
      $this->createAbyssFromPNG();
      $this->shiftAbyssIndexes();
      $this->setAbyssFoes();
    }
    
    public function createAbyssFromPNG()
    {
        // Get the array of pixel colors from the stored image
        $img = imagecreatefromstring(file_get_contents("../img/maps/abyss_50_50_test.png"));

        $width = imagesx($img);
        $height = imagesy($img);

        $this->abyss_size = $width * $height;

        for($x = 0; $x < $width; $x++)
        {
            for($y = 0; $y < $height; $y++)
            {
                // pixel color at (x, y)
                $color_index = imagecolorat($img, $x, $y);
                // make it human readable
                $color = imagecolorsforindex($img, $color_index);

                $this->abyss_r[] = $color["red"];
                $this->abyss_g[] = $color["green"];
                $this->abyss_b[] = $color["blue"];
                $this->abyss_x[] = $x;
                $this->abyss_y[] = $y;
            }
        } 
    }
    
    public function shiftAbyssIndexes()
    {
        // Difference with map : only X position switch.

        $n = sizeof($this->abyss_x)-1;

        // Change indexes
        $maxAbyssX = 19; //to have -19 -> 20
        //$newY = floor($maxAbyssX / 2);
        //$compteurPosY = 0;

        // Pour tout x : x - floor(20/2) = x - 10.
        for($i=0;$i<$n;$i++)
        {
            $this->abyss_x[$i] -= floor($maxAbyssX/2);
        }
    }
    
    public function setAbyssFoes()
    {
        // populate foes in abyss, 20 foes everywhere
        //$foe_hp = [];
        //$max_foe = [];
        
        for ($i=0; $i < $this->abyss_size; $i++)
        {
            $this->abyss_foe[$i]=0;
            $this->abyss_foe_hp[$i]='';
            $this->abyss_max_foe[$i]=0;

            //20 foes everywhere
            $this->abyss_foe[$i] = 20;
            
            $foeNbr = $this->abyss_foe[$i];
            for($j=0; $j<$foeNbr; $j++)
            {
                $this->abyss_foe_hp[$i].='A';
            }
            
            $this->abyss_max_foe[$i]=$this->abyss_foe[$i];
        }
    }
    
    public function storeAbyssInDb()
    {
        $query="INSERT INTO abyss(id_abyss, abyss_x, abyss_y, abyss_r, abyss_g, abyss_b, abyss_foe, abyss_max_foe, abyss_foe_hp) 
                values ";
        for ($i=0; $i < $this->abyss_size; $i++)
        {
            $query.="(".$this->newIdCity.", 
                      ".$this->abyss_x[$i].", 
                      ".$this->abyss_y[$i].", 
                      ".$this->abyss_r[$i].", 
                      ".$this->abyss_g[$i].", 
                      ".$this->abyss_b[$i].", 
                      ".$this->abyss_foe[$i].", 
                      ".$this->abyss_max_foe[$i].", 
                      '".$this->abyss_foe_hp[$i]."'),";

            //Find which pixel is the entrance (the blue pixel)
            if($this->abyss_r[$i] == 0 && $this->abyss_g[$i] == 0 && $this->abyss_b[$i] == 255)
            {
                $this->abyssEntryInsideX = $this->abyss_x[$i];
                $this->abyssEntryInsideY = $this->abyss_y[$i];
            }

            //Find which pixel is the hell entry (the red pixel)
            else if($this->abyss_r[$i] == 255 && $this->abyss_g[$i] == 0 && $this->abyss_b[$i] == 0)
            {
                $this->hellEntryOutsideX = $this->abyss_x[$i];
                $this->hellEntryOutsideY = $this->abyss_y[$i];
            }
        }
        //kill the last coma
        $query = substr($query, 0, -1);

        // $this->log->addLog($query, 'error', $this->idPlayer);

        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }
    
    public function addAbyssGates()
    {
        //Définir l'entrée de l'abysse.

        // TODO amel : case de couleur spéciale pour l'entrée.
        //$abyss_entry_x = 1;
        //$abyss_entry_y=1;

        $query="INSERT INTO abyss_city(id_city, id_abyss, abyss_x, abyss_y) 
                VALUE(".$this->newIdCity.", 
                      ".$this->newIdCity.", 
                      ".$this->abyssEntryInsideX.", 
                      ".$this->abyssEntryInsideY.")";
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        try
        {
            $sth->execute();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        //Définir l'entrée de l'enfer. C'est la premiere case rouge.

        // $query="SELECT abyss_x, abyss_y 
        //             FROM abyss 
        //             WHERE id_abyss=".$this->newIdCity." AND abyss_r = 255 AND abyss_g = 0 AND abyss_b = 0
        //             ORDER BY abyss_y DESC, abyss_x ASC
        //             LIMIT 1";

        // $sth = $this->database->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        // $sth->execute();
        // $row = $sth->fetchAll(PDO::FETCH_OBJ);
        // $hell_entry_x = $row[0]->abyss_x;
        // $hell_entry_y = $row[0]->abyss_y;

        $query="INSERT INTO abyss_hell(id_abyss,id_hell,abyss_x,abyss_y) 
                VALUE(".$this->newIdCity.", 
                      ".$this->newIdCity.", 
                      ".$this->hellEntryOutsideX.", 
                      ".$this->hellEntryOutsideY.")";

        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        try
        {
            $sth->execute();
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }

    #endregion

    #region Create the DJ2 (end of the DJ)
    public function createDJ2Content()
    {
        // Create random content (always 40 trees)
        //create random content selon la zone
        for ($i=0; $i<9; $i++)
        {
            $this->dj2_content[$i]='';

            for($j=0; $j<100; $j++)
            {
                $m = rand(1,100);
                if ($m>=1 && $m<=40)
                {
                    $this->dj2_content[$i] .= 'u';
                }
                else
                {
                    $this->dj2_content[$i] .= 'n';
                }
            }
            
        }
    }

    public function createAndStoreDJ2InDb()
    {
        $newIdDj2 = $this->newIdCity;
        $query="INSERT INTO dj2 (id_dj2, dj2_x, dj2_y, dj2_content, dj2_foe, dj2_max_foe, dj2_foe_hp) 
                VALUES
                    (".$newIdDj2.", -2, -2, '', 0,  0, ''),
                    (".$newIdDj2.", -2, -1, '', 0,  0, ''),
                    (".$newIdDj2.", -2, 0, '', 0,  0, ''),
                    (".$newIdDj2.", -2, 0, '', 0,  0, ''),
                    (".$newIdDj2.", -2, 2, '', 0,  0, ''),
                    (".$newIdDj2.", -1, -2, '', 0,  0, ''),
                    (".$newIdDj2.", -1, -1, '".$this->dj2_content[0]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", -1, 0, '".$this->dj2_content[1]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", -1, 1, '".$this->dj2_content[2]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", -1, 2, '', 0,  0, ''),
                    (".$newIdDj2.", 0, -2, '', 0,  0, ''),
                    (".$newIdDj2.", 0, -1, '".$this->dj2_content[3]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", 0, 0, '".$this->dj2_content[4]."', 0,  0, ''),
                    (".$newIdDj2.", 0, 1, '".$this->dj2_content[5]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", 0, 2, '', 0,  0, ''),
                    (".$newIdDj2.", 1, -2, '', 0,  0, ''),
                    (".$newIdDj2.", 1, -1, '".$this->dj2_content[6]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", 1, 0, '".$this->dj2_content[7]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", 1, 1, '".$this->dj2_content[8]."', 40,  40, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
                    (".$newIdDj2.", 1, 2, '', 0,  0, ''),
                    (".$newIdDj2.", 2, -2, '', 0,  0, ''),
                    (".$newIdDj2.", 2, -1, '', 0,  0, ''),
                    (".$newIdDj2.", 2, 0, '', 0,  0, ''),
                    (".$newIdDj2.", 2, 1, '', 0,  0, ''),
                    (".$newIdDj2.", 2, 2, '', 0,  0, '')";
        try 
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }

    #endregion
    
    #region Create hell (end of abyss)

    public function createAndStoreHellInDb()
    {
        $newIdCity = $this->newIdCity;

        $query="INSERT INTO hell (id_hell, hell_x, hell_y, hell_content, hell_foe, hell_max_foe, hell_foe_hp) 
        VALUES
            (".$newIdCity.", -2, -2, '0', 0,  0, ''),
            (".$newIdCity.", -2, -1, '0', 0,  0, ''),
            (".$newIdCity.", -2, 0, '0', 0,  0, ''),
            (".$newIdCity.", -2, 0, '0', 0,  0, ''),
            (".$newIdCity.", -2, 2, '0', 0,  0, ''),
            (".$newIdCity.", -1, -2, '0', 0,  0, ''),
            (".$newIdCity.", -1, -1, '0', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", -1, 0, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", -1, 1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", -1, 2, '0', 0,  0, ''),
            (".$newIdCity.", 0, -2, '0', 0,  0, ''),
            (".$newIdCity.", 0, -1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", 0, 0, '', 0,  0, ''),
            (".$newIdCity.", 0, 1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", 0, 2, '0', 0,  0, ''),
            (".$newIdCity.", 1, -2, '0', 0,  0, ''),
            (".$newIdCity.", 1, -1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", 1, 0, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", 1, 1, '', 100,  100, 'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'),
            (".$newIdCity.", 1, 2, '0', 0,  0, ''),
            (".$newIdCity.", 2, -2, '0', 0,  0, ''),
            (".$newIdCity.", 2, -1, '0', 0,  0, ''),
            (".$newIdCity.", 2, 0, '0', 0,  0, ''),
            (".$newIdCity.", 2, 1, '0', 0,  0, ''),
            (".$newIdCity.", 2, 2, '0', 0,  0, '')";

        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }

    public function createCampInHellEntry()
    {
        // put a camp at hell (0,0)
        $query="INSERT INTO city_camp(id_city, camp_pos_x, camp_pos_y, camp_area) 
            VALUE(".$this->newIdCity.", 0, 0, 'hell')";

        try 
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();  
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
    }

    #endregion
    
    #region Leave city
    
    public function removePlayerFromCity()
    {
        // Change the player's city and area
        $query1="UPDATE player SET id_city=0, player_area='home', player_pos_x=0, player_pos_y=0, is_fighting=0
                    WHERE id_player=".$this->idPlayer;

        // Remove 1 player from the city
        $query2="UPDATE city SET player_nbr = player_nbr - 1
                    WHERE id_city=".$this->idCity;

        // reinitialize the player stats
        $query3="UPDATE player_stat SET player_stat_lvl = 1, player_stat_xp = 0
                    WHERE id_player=".$this->idPlayer;
    
        // reinitialize the player's local statistics
        $query4="UPDATE statistic_local_player SET amount = 0
                    WHERE id_player=".$this->idPlayer;
    
        // Rendre les objets de l'inventaire à la ville
        $query5="UPDATE city_item SET item_pos='bank' 
                WHERE id_player=".$this->idPlayer." 
                   AND is_alive=1 
                   AND item_pos IN ('invent','shield','upper','lower','mask','helmet','spear')";
    
        /// Les objets détruits sont inacessibles par les autres joueurs, et seront détruits à la fin de la ville.
    
        //delete farm items for the player
        $query6="UPDATE city_farm SET is_alive = 0
                    WHERE id_player=".$this->idPlayer;
    
        //reinitialize the player vote
        $query7="UPDATE player_vote SET id_vote = 0, vote_type = ''
                    WHERE id_player = ".$this->idPlayer;
    
        // Empêcher le joueur de rejoindre la ville qu'il vient de quitter
        $query8="UPDATE account SET prev_id_city = ".$this->idCity."
                    WHERE id_player = ".$this->idPlayer;

        try
        {
            $this->database->mysql->query($query1);
            $this->database->mysql->query($query2);
            $this->database->mysql->query($query3);
            $this->database->mysql->query($query4);
            $this->database->mysql->query($query5);
            $this->database->mysql->query($query6);
            $this->database->mysql->query($query7);
            $this->database->mysql->query($query8);
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }
  
    }

    #endregion

    #region Destroy city

    /**
     * Called from defeat or victory screen when the city's timer exceeds the time limit or the Hell area has been emptied of monsters.
     * Do not reinitialize the players' specific data (stats, statistics). Only do it in joincity().
     */
    public function destroyCity()
    {
        //When the city is destroyed, remove everything about the city from the database, except :
        // - the row in the CITY table (for the players who have not yet left the city, and for the statistics)
        // - the city's statistics (for History)

        // First, check if the city has already been destroyed by another player.
        // TODO : remplacer par un test sur has_ended
        $query="SELECT has_ended FROM city 
                    WHERE id_city=".$this->idCity;
        $hasEnded='';
        try
        {
            $res = $this->database->mysql->query($query);
            if($res)
            {
                $data=$res->fetch(PDO::FETCH_ASSOC);
                $hasEnded = $data['has_ended'];  
            }
        }
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $this->idPlayer);
            die();
        }

        // Delete the city only if not deleted
        if($hasEnded == 0)
        { 
            // Reset the city's player nbr
            $query1="UPDATE city SET player_nbr = 0, has_ended = 1
                        WHERE id_city=".$this->idCity;

            // Delete items for the city
            $query2="DELETE FROM city_item 
                    WHERE id_city=".$this->idCity;

                        // Reset players' life bars
            $query3="UPDATE player_life_bar plb
                    JOIN player p ON plb.id_player = p.id_player
                    SET plb.player_food = 100, plb.player_morale = 100
                    WHERE p.id_city=".$this->idCity;

            // Reset players' mute (exemple de delete avec jointure)
            $query4="DELETE player_mute FROM player_mute
                        JOIN player  ON player_mute.id_player = player.id_player
                        WHERE player.id_city=".$this->idCity;

            // Delete farm items for the city
            $query5="DELETE FROM city_farm 
                        WHERE id_city=".$this->idCity;

            //reinitialize the player vote
            $query6="UPDATE player_vote pv
                        JOIN player p ON pv.id_player = p.id_player
                        SET pv.id_vote = 0, pv.vote_type = ''
                        WHERE p.id_city = ".$this->idCity;

            // Delete buildings
            $query7="DELETE FROM building_city
                        WHERE id_city = ".$this->idCity;

            // Delete chatbox messages
            $query8="DELETE FROM chatbox_msg
                        WHERE id_city = ".$this->idCity;

            // Delete city actions
            $query9="DELETE FROM city_action
                        WHERE id_city = ".$this->idCity;

            // Delete city camps
            $query10="DELETE FROM city_camp
                        WHERE id_city = ".$this->idCity;

            // Delete city news
            $query11="DELETE FROM city_new
                        WHERE id_city = ".$this->idCity;

            // Delete city cron
            $query12="DELETE FROM cron_city
                        WHERE id_city = ".$this->idCity;

            // Delete map, dj, dj2, abyss, hell
            $query13="DELETE FROM map 
                        WHERE id_city=".$this->idCity;

            $query14="DELETE FROM dj 
                        WHERE id_dj=".$this->idCity;

            $query15="DELETE FROM dj2 
                        WHERE id_dj2=".$this->idCity;

            $query16="DELETE FROM abyss 
                        WHERE id_abyss=".$this->idCity;

            $query17="DELETE FROM hell 
                        WHERE id_hell=".$this->idCity;

            // Delete the links between areas for the city
            $query18="DELETE FROM abyss_city 
                        WHERE id_city=".$this->idCity;

            $query19="DELETE FROM abyss_hell 
                        WHERE id_hell=".$this->idCity;

            $query20="DELETE FROM dj_dj2 
                        WHERE id_dj=".$this->idCity;

            $query21="DELETE FROM map_dj 
                        WHERE id_city=".$this->idCity;

            // Change the players' city and area
            // AT THE VERY END, as the player table is used in JOIN
            $query22="UPDATE player 
                        SET id_city=0, player_area='home'
                        WHERE id_city=".$this->idCity;
            
            try
            {
                $this->database->mysql->query($query1);
                $this->database->mysql->query($query2);
                $this->database->mysql->query($query3);
                $this->database->mysql->query($query4);
                $this->database->mysql->query($query5);
                $this->database->mysql->query($query6);
                $this->database->mysql->query($query7);
                $this->database->mysql->query($query8);
                $this->database->mysql->query($query9);
                $this->database->mysql->query($query10);
                $this->database->mysql->query($query11);
                $this->database->mysql->query($query12);
                $this->database->mysql->query($query13);
                $this->database->mysql->query($query14);
                $this->database->mysql->query($query15);
                $this->database->mysql->query($query16);
                $this->database->mysql->query($query17);
                $this->database->mysql->query($query18);
                $this->database->mysql->query($query19);
                $this->database->mysql->query($query20);
                $this->database->mysql->query($query21);
                $this->database->mysql->query($query22);
            }
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
                $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                $this->log->addLog($err, 'error', $this->idPlayer);
                die();
            }

        }

    }

    #endregion

}
  