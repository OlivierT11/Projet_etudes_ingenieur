<?php
class Temp
{
  public function createNewCity()
  {
    // Create the city
    $this->addCity();
    
    // Create the map
    $this->createMap();
    $this->storeMapInDB();
    $this->removeFoesOnCityEntry();
    $this->addDjGates();
    $this->addMapLimits();
    
    // Create the DJ
    $this->createDJ();
    $this->storeDjInDB();
    $this->addDjGates();
    $this->removeFoesOnDjEntry();
    
    // Create the abyss
    $this->createAbyssFromPNG();
    $this->storeAbyssInDb();
    $this->addAbyssGates();
    
    // Create the DJ2 (end of the DJ)
    $this->createDJ2Content();
    $this->createAndStoreDJ2InDb();
    $this->addDJ2Entry();
    
    // Create hell (end of abyss)
    $this->createAndStoreHellInDb();
    $this->addDJ2Hell();
    $this->createCampInHellEntry();
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
    $query = 'INSERT INTO city(player_nbr, `start_date`) VALUES(1, '.$dateTest.')';
    try 
    {
        $res=$this->database->query($query);
        $newIdCity = $this->database->lastInsertId();
        $newIdDj = $newIdCity; // 1 dj par ville pour l'instant. Après il faudra une table avec id_dj en clé primaire pour avoir lastInsertId.
    }
    catch (PDOException $e)
    {
       
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
    
  }
  
  public function shiftMapIndexes()
  {
    
  }
  
  public function setMapBiomes()
  {
        // Set area type and gate as a function of area color
        //$area_type = [];

        for ($i=0; $i<$this->sizeMap; $i++)
        {
            if ($this->r[$i]=="182" && $this->g[$i]=="255" && $this->b[$i]=="0")
            {
                $this->area_type[$i]="field"; //OR $area_type[]="field";
            }
            else if ($this->r[$i]=="76" && $this->g[$i]=="255" && $this->b[$i]=="0")
            {
                $this->area_type[$i]="forest_1";
            }
            else if ($this->r[$i]=="38" && $this->g[$i]=="127" && $this->b[$i]=="0")
            {
                $this->area_type[$i]="forest_2";
            }
            else if ($this->r[$i]=="255" && $this->g[$i]=="0" && $this->b[$i]=="0")
            {
                $this->area_type[$i]="forest_2";
            }
        }
  }
  
  public function setMapContent()
  {
        //create random content selon la zone
        for ($i=0; $i<$this->sizeMap; $i++)
        {
            $content[$i]='';
            $contentString = '';
            $is_discovered[$i] = 0;

            /// Plaine arbres 5-20%, rock : 0-5% orange 0-3% honey 0-3%, rare tree 1/10000 or 1 every 100 areas
            if($this->area_type[$i]=="field")
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
            if($this->area_type[$i]=="forest_1")
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
            if($this->area_type[$i]=="forest_2")
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

            $this->mapContent[$i] = $contentString;

        }

  }
  
  public function setMapFoes()
  {
        // populate foes on map
     //   $foe_hp = [];
    //    $max_foe = [];
        
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
            
            $this->map_max_foe[$i]=$this->map_foe[$i];
        }

        

            // Populate Hordes and Titans

            /*
            // TODO Do not add foe on (0,0) (city) and Set (0,0) to discovered
            for ($i=0; $i<$n; $i++){
                if(x[$i]==0 && y[$i]==0){
                    //x and y have the same index as the area is a square
                    $foe[$i] = 0;
                    $max_foe[$i] = 0;
                    $foe_hp[$i] = '';
                    $is_discovered[$i] = 1;
                }
            }
            */

  }
  
  public function storeMapInDB()
  {
        $query="INSERT INTO map (id_city, x, y, r, g, b, content, foe, max_foe, foe_hp, area_type) values ";
        for ($i=0; $i<$n; $i++)
        {
           $query.="(".$this->newIdCity.", 
                ".$this->map_x[$i].", 
                ".$this->map_y[$i].", 
                ".$this->map_r[$i].", 
                ".$this->map_g[$i].", 
                ".$this->map_b[$i].", 
                '".$this->map_content[$i]."', 
                ".$this->map_foe[$i].", 
                ".$thies->map_max_foe[$i].", 
                '".$thies->map_foe_hp[$i]."', 
                '".$this->map_area_type[$i]."'),";
        }
        $query = substr($query, 0, -1);
        try
        {
            $this->database->query($query);
        }
        catch (PDOException $e)
        {
       
        }

  
  }
  
  public function removeFoesOnCityEntry()
  {
        // Remove the foes on city entry (0,0)
        $query="UPDATE map SET foe=0, max_foe=0, foe_hp='' 
                WHERE x=0 AND y=0 AND id_city=".$this->newIdCity;
        try
        {
            $res=$this->database->query($query);
        }
        catch (PDOException $e)
        {
          
        }

  }
  
  public function addDjGates()
  {
        // Define the DJ entry (OUTSIDE dj). It is the only red dot on map.
        //$djEntryXout = 0;
        //$djEntryYout = 0;
        for ($i=0; $i<$n; $i++)
        {
            if ($r[$i]==255 && $g[$i]==0 && $b[$i]==0)
            {
                $this->dj_EntryXout = $this->map_x[$i];
                $this->dj_EntryYout = $this->map_y[$i];
            }
        }
  }
  
  public function addMapLimits()
  {
        // Create walls on map limits by setting the content value of limit areas to '0'.
       // $limitLeftX = -49;
       // $limitRightX = 50;
       // $limitUpY = 50;
     //   $limitDownY = -49;
        $query="UPDATE map SET content='' 
                WHERE x=".$this->map_limitLeftX." 
                   OR x=".$this->map_limitRightX." 
                   OR y=".$this->map_limitUpY." 
                   OR y=".$this->map_limitDownY;
        try
        {
            $res=$this->database->query($query);
        }
        catch (PDOException $e)
        {
           
        }

  }
  
  #region DJ
  
  public function createDj()
  {
    $this->createRandomMaze();
    $this->shiftDjIndexes();
    $this->setDjFoes();
  }
  
  public function createRandomMaze()
  {
    
  }
  
  public function shiftDjIndexes()
  {
    
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
            $res=$this->database->query($query);
        }
        catch (PDOException $e)
        {
           
        }

  }
  
  public function addDjGates()
  {
        // set the dj entry (INSIDE dj)
        //$djEntryXin = 0;
        //$djEntryYin = -8;

        // set the dj entry INSIDE AND OUTSIDE
        $query="INSERT INTO map_dj(id_city, id_dj, id_area, dj_x, dj_y) 
                VALUE(".$this->newIdCity.", 
                      ".$newIdDj.", 
                      ".$id.", 
                      ".$djEntryXin.", 
                      ".$djEntryYin.")";
        try
        {
            $res=$this->database->query($query);
        }
        catch (PDOException $e)
        {
          
        }
        
        // set the dj exit to dj2
        $djEntryDj2X = 0;
        $djEntryDj2Y = 9;
        $query="INSERT INTO dj_dj2(id_dj, id_dj2, dj2_x, dj2_y) VALUE(".$newIdDj.", ".$newIdDj.", ".$djEntryDj2X.", ".$djEntryDj2Y.")";
        try {
            $res=$mysql->query($query);
        } catch (PDOException $e){
            echo "Erreur dans la requête ajax_admin set DJ entry " . $e->getMessage();
        }

  }
  
  public function removeFoesOnDjEntry()
  {
    
  }
  
  #endregion
  
  #region Abyss
  
  public function createAbyss()
  {
    $this->createAbyssFromPNG();
    $this->shiftAbyssIndexes();
    $this->setAbyssFoes();
  }
  
  public function createAbyssFromPNG()
  {
    
  }
  
  public function shiftAbyssIndexes()
  {
    
  }
  
  public function setAbyssFoes()
  {
    
  }
  
  public function storeAbyssInDb()
  {
    
  }
  
  public function addAbyssGates()
  {
    
  }
  
  #endregion
  
  public function removePlayerFromCity()
  {
    // Change the player's city and area
    $query="UPDATE player SET id_city=0, player_area='home'
                WHERE id_player=".$this->idPlayer;
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
        
    }
    
    // Remove 1 player from the city
    $query="UPDATE city SET player_nbr = player_nbr - 1
                WHERE id_city=".$this->idCity;
    try 
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
       
    }

    // reinitialize the player stats
    $query="UPDATE player_stat SET player_stat_lvl = 1, player_stat_xp = 0
                WHERE id_player=".$this->idPlayer;
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
       
    }

    // reinitialize the player's local statistics
    $query="UPDATE statistic_local_player SET amount = 0
                WHERE id_player=".$this->idPlayer;
    try
    {
        $mysql->query($query);
    }
    catch (PDOException $e)
    {
        
    }

    // Rendre les objets de l'inventaire à la ville
    $query="UPDATE city_item SET item_pos='bank' 
            WHERE id_player=".$this->idPlayer." 
               AND is_alive=1 
               AND item_pos IN ('invent','shield','upper','lower','mask','helmet','spear')";
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
       
    }

    /// Les objets détruits sont inacessibles par les autres joueurs, et seront détruits à la fin de la ville.

    //delete farm items for the player
    $query="UPDATE city_farm SET is_alive = 0
                WHERE id_player=".$this->idPlayer;
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
       
    }

    //reinitialize the player vote
    $query="UPDATE player_vote SET id_vote = 0, vote_type = ''
                WHERE id_player = ".$this->idPlayer;
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
       
    }

    // Empêcher le joueur de rejoindre la ville qu'il vient de quitter
    $query="UPDATE account SET prev_id_city = ".$this->idCity."
                WHERE id_player = ".$this->idPlayer;
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
       
    }

  }
}