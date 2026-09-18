<?php

class Battle
{
  private $battleHasEnded=false;
  private $maxCityDay=10;
  private $abyssDeepness=18;
  
  private $foeNbr;
  private $playerFightingCount;
  
  // amel : obj en entrée avec les stats du joueur (lvl, master...) et les stats de l'équipement (lvl, dur...) pour éviter de passer par la session.
  // TODO : sql préparées ou sanitizer session data dans constrcteur (player pos x etc)
  // TODO relire et ajouter les variables private
  public function doBattle()
  {
    $areaBDD = '';
    
    $this->countFoesOnArea();
    
    // Il n'y a pas de monstres ? Ne pas combattre.
    if ($this->foeNbr == 0)
    {
        return "nofoe";
        //response[0] = 'nofoe';
        //echo response;
        //die();
    }
    
    $areaBDD = $this->defineAreaTableDB();
    $this->countFightingPlayers($areaBDD);
    
    //Vérifie si le combat est possible
    if ($this->current_player_fighting_count >= $this->foe_nbr)
    {
        echo "allFight";
        die();
    }
    
    $this->chooseFoeToFight();
    $this->computeFoeStrength();
    $this->definePlayerGearDurability();
    $this->getPlayerCombatStats();
    
    // The attack starts here
    $this->foeAttacksPlayer();
    $this->playerAttacksFoe();
    
  }
  
  // TODO : object Area en entrée avec foe et foe_hp déja renseignés quand on arrive sur la case
  public function countFoesOnArea()
  {
     //Définit si le joueur peut combattre (il y a des monstres et pas assez de joueurs qui combattent déjà)
        
    //Compte le nombre d'ennemis selon la zone de combat (outside, dj...).
    //TODO : amel SESSION. unset quand session(is_fighting)=0
    switch($this->player_area)
    {
        case 'outside':
            $query="SELECT foe, foe_hp FROM map
                    WHERE x=".$this->player_pos_x." 
                      AND y=".$this->player_pos_y." 
                      AND id_city=".$this->id_city;
        break;
        case 'dj':
            $query="SELECT dj_foe as foe, dj_foe_hp as foe_hp FROM dj
                    WHERE dj_x=".$this->player_pos_x." 
                      AND dj_y=".$this->player_pos_y." 
                      AND id_dj=".$this->id_dj;
            break;
        case 'abyss':
            $query="SELECT abyss_foe as foe, abyss_foe_hp as foe_hp FROM abyss
                    WHERE abyss_x=".$this->player_pos_x." 
                      AND abyss_y=".$this->player_pos_y." 
                      AND id_abyss=".$this->id_city;
            break;
        case 'dj2':
            $query="SELECT dj2_foe as foe, dj2_foe_hp as foe_hp FROM dj2
                    WHERE dj2_x=".$this->player_pos_x." 
                      AND dj2_y=".$this->player_pos_y." 
                      AND id_dj2=".$this->id_dj;
            break;
        case 'hell':
            $query="SELECT hell_foe as foe, hell_foe_hp as foe_hp FROM hell
                    WHERE hell_x=".$this->player_pos_x." 
                      AND hell_y=".$this->player_pos_y." 
                      AND id_hell=".$this->id_city;
            break;
        default:
            //throw
    }
    $result=[];
    try
    {
        $res = $this->database->query($query);
        if($res)
        {
            while($data=$res->fetch(PDO::FETCH_ASSOC))
            {
                $this->current_foe_nbr = $data['foe'];
                $this->current_foe_hp = $data['foe_hp']; //string(34612AA3A...)
            }
        }
    }
    catch (PDOException $e)
    {
       
    }

  }
  
  /**
   * Define the table name to be used in queries.
   */
  public function defineAreaTableDB()
  {
    $areaBDD = '';
        //définit le nom de la table pour les requêtes
    switch($this->player_area)
    {
        case 'outside':
            $areaBDD = 'map';
        break;
        default:
           $areaBDD = $this->player_area;
    }
    
    return $areaBDD;

  }
  
  public function countFightingPlayers($areaBDD)
  {
        //Compte le nombre de joueurs qui combattent (autres que le joueur)
    $query="SELECT COUNT(id_player) FROM player
        WHERE is_fighting = 1 
          AND player_pos_x=".$this->player_pos_x."
          AND player_pos_y=".$this->player_pos_y." 
          AND id_city=".$this->id_city." 
          AND player_area='".$areaBDD."'";
          
    $this->current_player_fighting_count = $this->database->query($query)->fetchColumn();

    // Retire 1 si le joueur est déja en combat pour ne pas être compté comme un autre joueur.
    if(isset($_SESSION['is_fighting']) && $_SESSION['is_fighting'] == 1)
    { 
        $this->current_player_fighting_count -= 1;
    }

    // Il ne peut pas y avoir moins de 0 joueurs qui combattent !
    if($this->current_player_fighting_count < 0)
    {
        $this->current_player_fighting_count = 0;
    }

  }
  
  public function chooseFoeToFight()
  {
         // Si 1ere boucle du combat, il n'y a pas de pos en mémoire, dont on prend le 1er ennemi supérieur au nombre d'alliés dont les pv sont plus grand que 0.
    // Sinon, garder le même ennemi.
    if($this->$current_foe_pos_in_string != 0)
    {
        $this->$current_foe_pos_in_string = $this->current_player_fighting_count;
        $foeHp = $this->current_foe_array_hp[ $this->current_player_fighting_count ]; //1er foe dispo (indice 4 si 4 joueurs combattent)
        if($this->current_foe_hp == '0')
        {
            while($this->current_foe_hp == '0' && isset($this->current_foe_array_hp[ $this->current_foe_pos_in_string ]))
            { //isset avoid unimited loop
                $this->$current_foe_pos_in_string += 1;
                $this->current_foe_hp = $this->current_foe_array_hp[ $this->current_foe_pos_in_string ]; //1er foe dispo (indice 4 si 4 joueurs combattent)
            }
        }
    }
    else
    {
        $this->current_foe_hp = $this->array_foe_hp[ $this->current_foe_pos_in_string ];
    }
   
    // convert the HP value
    if ($this->current_foe_hp == 'A')
    {
        $this->current_foe_hp = 10;
    }
    else
    {
        $this->current_foe_hp = (int)$this->current_foe_hp;
    }
  }
  
  public function computeFoeStrength()
  {
          //Calcul de la force du monstre
    // à l'extérieur, la force du monstre est la distance à la ville (0,0)
    if($this->player_area=='outside')
    {
        $this->current_foe_atk = $this->current_foe_def = floor( sqrt( pow($this->player_pos_x,2)+ pow($this->player_pos_y,2) ) );
    }

    // Dans un donjon, tous les monstres ont la même force, car les joueurs sont sensés mettre 1-2 jours pour terminer un étage de 20*20 cases.
    // Ils ont le niveau de la case de carte sur laquelle est l'entrée du donjon.
    else if($this->player_area=='dj')
    {
        // selectionne les coordonnées de l'entrée du donjon sur la carte
        $query="SELECT m.x, m.y 
            FROM map m 
               INNER JOIN map_dj md 
                   ON m.id = md.id_area
            WHERE id_dj=".$this->id_dj;
        $result=[];
        try {
            $res = $this->database->query($query);
            if($res)
            {
                while($data=$res->fetch(PDO::FETCH_ASSOC))
                {
                    $result['x'] = $data['x'];
                    $result['y'] = $data['y'];
                }
            }
        }
        catch (PDOException $e)
        {
           
        }
        
        $this->current_foe_atk = $this->current_foe_def = floor( sqrt( pow($_SESSION['x']-result['x'],2) + pow($_SESSION['y']-result['y'], 2) ) );
    }
    
    // Dans une abysse, tous les monstres ont la même force, car les joueurs sont sensés mettre 1-2 jours pour terminer un étage de 20*20 cases.
    // Ils ont le niveau du maximum de la stat d'attaque en cours quand l'abysse s'ouvre. Le niveau augmente de 1 par unité de profondeur. Soit 1*18 = 18lvl à faire en 4 jours.
    else if($this->player_area == 'abyss')
    {
        // détermine la valeur max possible de la statistique d'attaque des joueurs à l'ouverture de l'abysse (4 jours avant la fin)
        $maxStat = 10 + ($maxCityDay - 4) * 5;

        $this->current_foe_atk = $this->current_foe_def = $maxStat;
    }
    // En enfer, les monstres ont le niveau du plus profond de l'abysse, soit le niveau max de la stat d'attaque atteignable par un joueur à l'ouverture de l'abysse, +18lvl.
    // Les bonus bâtiments ne sont pas pris en compte (craft +10)
    else if($this->player_area == 'hell')
    {
        $maxStat = 10 + ($maxCityDay - 4) * 5;

        $this->current_foe_atk = $this->current_foe_def = $maxStat + $abyssDeepness;
    }
 
  }
  
  public function definePlayerGearDurability()
  {
    //Définit la durabilité de l'équipement du joueur avant la bataille
    $shieldDur = $upperDur = $lowerDur = $maskDur = $helmetDur = $spearDur = 0;
    $shieldLvl = $upperLvl = $lowerLvl = $maskLvl = $helmetLvl = $spearLvl = 0;

    // Si le joueur est équipé des différents équipements
    //sessions définies dans ctrl_life_bars.php
    if (isset($_SESSION['player_shield_dur']))
    {
        $shieldDur = $_SESSION['player_shield_dur'];
        $shieldLvl = $_SESSION['player_shield_lvl'];
    }
    if (isset($_SESSION['player_upper_dur']))
    {
        $upperDur = $_SESSION['player_upper_dur'];
        $upperLvl = $_SESSION['player_upper_lvl'];
    }
    if (isset($_SESSION['player_lower_dur']))
    {
        $lowerDur = $_SESSION['player_lower_dur'];
        $lowerLvl = $_SESSION['player_lower_lvl'];
    }
    if (isset($_SESSION['player_helmet_dur']))
    {
        $helmetDur = $_SESSION['player_helmet_dur'];
        $helmetLvl = $_SESSION['player_helmet_lvl'];
    }
    if (isset($_SESSION['player_mask_dur']))
    {
        $maskDur = $_SESSION['player_mask_dur'];
        $maskLvl = $_SESSION['player_mask_lvl'];
    }
    if (isset($_SESSION['player_spear_dur']))
    {
        $spearDur = $_SESSION['player_spear_dur'];
        $spearLvl = $_SESSION['player_spear_lvl'];
    }
  }
  
  public function getPlayerCombatStats()
  {
    //aller chercher les stats du joueur
    //définis dans ctrl_login.php
    // XP is provided on foe kill, so there is no lvl up between two attacks.
    $this->playerStatAtk = $_SESSION['atk_stat']['player_stat_lvl'];
    $this->playerStatDef = $_SESSION['def_stat']['player_stat_lvl'];
  }
  
  private function foeAttacksPlayer()
  {
    $partToAtk = $this->chooseGearPartToAttack();
    
    // Le joueur a-t-il succombé à l'attaque ?
    if ($playerIsDead == 1)
    {
      $this->killPlayerFromFoeAttack();
    }
    
    $this->foeAttackPlayerGearPart($partToAtk);
  }
  
  private function chooseGearPartToAttack()
  {
    $partToAtk = '';
        
    //Choix de la pièce à attaquer, si aucun bouclier. Sinon, attaquer le bouclier
    if($this->current_shield_dur != 0)
    {
        $partToAtk = 'shield';
    }
    else
    {
        $n = rand(0,3);
        switch($n)
        {
            case 0:
                $partToAtk='upper';
                if ($this->current_upper_dur == 0)
                {
                    $this->playerIsDead = true;
                }
                break;
            case 1:
                $partToAtk='lower';
                if ($this->current_lower_dur == 0)
                {
                    $this->playerIsDead = true;
                }
                break;
            case 2:
                $partToAtk='helmet';
                if ($this->current_helmet_dur == 0)
                {
                    $this->playerIsDead = true;
                }
                break;
            case 3:
                $partToAtk='mask';
                if ($this->current_mask_dur == 0)
                {
                    $this->playerIsDead = true;
                }
                break;
        }
      
    }
    
    return $partToAtk;
  }
  
  //TODO Add transaction
  private function killPlayerFromFoeAttack()
  {
    $food = 0;
    $morale = 0;
    
        $_SESSION['is_fighting'] = 0;

        // Le joueur ne combat plus
        $query="UPDATE player SET is_fighting=0
            WHERE id_player=".$this->id_player;
        try
        {
            $this->database->query($query);
        }
        catch (PDOException $e)
        {
          
        }
        
        // Le joueur est renvoyé en ville
        $query="UPDATE player SET player_pos_x=0, 
                                  player_pos_y=0, 
                                  player_area='inside', 
                                  id_area=0, 
                                  id_abyss=0, 
                                  id_dj=0, 
                                  prev_dir=''
                WHERE id_player =".$this->id_player;
        try
        {
            $res = $this->database->query($query);
        }
        catch (PDOException $e)
        {
           
        }
        
        // Le joueur perd de la faim et du moral
        $food = $_SESSION['player-food-bar'];
        $food > 20 ? ($food=20) : ($food=5);
        $morale = $_SESSION['player-morale-bar'];
        $morale > 20 ? ($morale=20) : ($morale=5);

        $query="UPDATE player_life_bar SET player_food=".$food.", player_morale=".$morale."
            WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $res = $this->database->query($query);
        }
        catch (PDOException $e)
        {
          
        }
        
        $query="UPDATE city_item SET is_alive=0
            WHERE id_player =".$_SESSION['id_player'];
        try
        {
            $res = $this->database->query($query);
        }
        catch (PDOException $e)
        {
           
        }
        
        // Finally returns "die" to ajax request and end the script.
        return "die";
        //response[0] = 'die';
        //echo response;
        //die();

  }
  
  //TODO retirer le code récurrent du switch
  private function foeAttackPlayerGearPart($partToAtk)
  {
    $playerDef = 0;
    $playerAtk = 0;
    $lostDur = 0;

    #region adds damage reduction based on building
    $armorBonusBld = 0;

    // Si seul le 1er niveau de collecte est construit (par défault)
    if(in_array(19, $_SESSION['id_bld']) && !in_array(20, $_SESSION['id_bld']) && !in_array(21, $_SESSION['id_bld'])){
        $armorBonusBld = 0;
    }
    // Si le 2nd niveau de collect est construit
    else if(in_array(20, $_SESSION['id_bld']) && !in_array(21, $_SESSION['id_bld'])){
        $armorBonusBld = 1;
    }
    // Si le 3e niveau est construit
    else if(in_array(21, $_SESSION['id_bld'])){
        $armorBonusBld = 3;
    }

    #endregion
    
    //Si le joueur n'est pas mort, le monstre attaque la partie de l'équipement choisie
    switch ($partToAtk)
    {
        case 'shield':
            $playerDef = floor(($playerStatAtk + $this->current_shield_lvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat')
            {
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0)
            {
                $lostDur = ($this->curren_foe_atk - $playerDef);
            // Si le joueur est plus fort que le monstre, il son équipement perd quand même de la durabilité.
            }
            else
            {
                $lostDur = 1;
            }

            // Apply the armor bonus
            $lostDur -= $armorBonusBld;

            // Never less than 1 HP lost
            if ($lostDur < 1)
            {
                $lostDur = 1;
            }

            $this->current_shield_dur -= $lostDur;
            $_SESSION['player_shield_dur'] = $this->current_shield_dur;
        
            $this->action->postAction($this->database, 'def-shield', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            //$response[7] = $shieldDur;
            // Si l'équipement attaqué est détruit par l'attaque, renseigner la BDD
            if ($this->current_shield_dur <= 0)
            {
                $_SESSION['player_shield_lvl'] = 0;
                $_SESSION['player_shield_dur'] = 0;
                
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$this->id_player." 
                      AND id_city=".$this->id_city." 
                      AND item_pos='shield' 
                      AND is_alive=1";
            }
            // Sinon, update seulement la durabilité
            else
            {
                $query="UPDATE city_item SET item_dur=".$shieldDur."
                WHERE id_player=".$_SESSION['id_player']." 
                  AND item_pos='shield' 
                  AND is_alive=1";
            }
        break;
        case 'upper':
            $playerDef = floor(($playerStatAtk + $upperLvl) / 2); //+10%leader
            
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat')
            {
                $playerDef += floor($playerDef*20/100);
            }
            
            if(($foeAtk - $playerDef) > 0)
            {
                $lostDur = ($foeAtk - $playerDef);
            }
            else
            {
                $lostDur = 1;
            }

            // Apply the armor bonus
            $lostDur -= $armorBonusBld;

            // Never less than 1 HP lost
            if ($lostDur < 1)
            {
                $lostDur = 1;
            }

            $upperDur -= $lostDur;
            $_SESSION['player_upper_dur'] = $upperDur;
            $this->action->postAction($this->database, 'def-upper', 0, 0, 0, 0, 0, 0, 0, $lostDur);
        //    $response[7] = $upperDur;

            if ($upperDur <= 0)
            {
                $_SESSION['player_upper_lvl'] = 0;
                $_SESSION['player_upper_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$_SESSION['id_player']." AND id_city=".$_SESSION['id_city']." AND item_pos='upper' AND is_alive=1";
            }
            else
            {
                $query="UPDATE city_item SET item_dur=".$upperDur."
                    WHERE id_player=".$_SESSION['id_player']." AND item_pos='upper' AND is_alive=1";
            }
        break;
        case 'lower':
            $playerDef = floor(($playerStatAtk + $lowerLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat')
            {
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0)
            {
                $lostDur = ($foeAtk - $playerDef);
            }
            else
            {
                $lostDur = 1;
            }

            // Apply the armor bonus
            $lostDur -= $armorBonusBld;

            // Never less than 1 HP lost
            if ($lostDur < 1)
            {
                $lostDur = 1;
            }

            $lowerDur -= $lostDur;
            $_SESSION['player_lower_dur'] = $lowerDur;
            $this->action->postAction($this->database, 'def-lower', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $lowerDur;

            if ($lowerDur <= 0)
            {
                $_SESSION['player_lower_lvl'] = 0;
                $_SESSION['player_lower_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$this->id_player." 
                      AND id_city=".$this->id_city." 
                      AND item_pos='lower' 
                      AND is_alive=1";
            }
            else
            {
                $query="UPDATE city_item SET item_dur=".$lowerDur."
                WHERE id_player=".$this->id_player." 
                  AND item_pos='lower' 
                  AND is_alive=1";
            }
        break;
        case 'helmet':
            $playerDef = floor(($playerStatAtk + $helmetLvl) / 2); //+10%leader
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat')
            {
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0)
            {
                $lostDur = ($foeAtk - $playerDef);
            }
            else
            {
                $lostDur = 1;
            }

            // Apply the armor bonus
            $lostDur -= $armorBonusBld;

            // Never less than 1 HP lost
            if ($lostDur < 1)
            {
                $lostDur = 1;
            }

            $helmetDur -= $lostDur;
            $_SESSION['player_helmet_dur'] = $helmetDur;
            postAction($mysql, 'def-helmet', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $helmetDur;
            
            if ($helmetDur <= 0)
            {
                $_SESSION['player_helmet_lvl'] = 0;
                $_SESSION['player_helmet_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$this->id_player."
                      AND id_city=".$this->id_city." 
                      AND item_pos='helmet' 
                      AND is_alive=1";
            }
            else
            {
                $query="UPDATE city_item SET item_dur=".$helmetDur."
                WHERE id_player=".$this->id_player." 
                  AND item_pos='helmet' 
                  AND is_alive=1";
            }

        break;
        case 'mask':
            $playerDef = floor(($playerStatAtk + $maskLvl) / 2); //+10%leader
            
            // Add the +20% Master bonus
            if($isMaster == 1 && $masterStat == 'atk_stat')
            {
                $playerDef += floor($playerDef*20/100);
            }
            if(($foeAtk - $playerDef) > 0)
            {
                $lostDur = ($foeAtk - $playerDef);
            }
            else
            {
                $lostDur = 1;
            }

            // Apply the armor bonus
            $lostDur -= $armorBonusBld;

            // Never less than 1 HP lost
            if ($lostDur < 1)
            {
                $lostDur = 1;
            }
            
            $maskDur -= $lostDur;
            $_SESSION['player_mask_dur'] = $maskDur;
            postAction($mysql, 'def-mask', 0, 0, 0, 0, 0, 0, 0, $lostDur);
            $response[7] = $maskDur;

            if ($maskDur <= 0)
            {
                $_SESSION['player_mask_lvl'] = 0;
                $_SESSION['player_mask_dur'] = 0;
                $query="UPDATE city_item SET item_pos='', is_alive=0, item_dur=0
                    WHERE id_player=".$this->id_player." 
                      AND id_city=".$this->id_city." 
                      AND item_pos='mask' 
                      AND is_alive=1";
            }
            else
            {
                $query="UPDATE city_item SET item_dur=".$maskDur."
                WHERE id_player=".$this->id_player." 
                  AND item_pos='mask' 
                  AND is_alive=1";
            }
        break;
    }
    try
    {
        $this->database->query($query);
    }
    catch (PDOException $e)
    {
        
    }
  }
  
  private function playerAttacksFoe()
  {
    $lostHp = $this->computeDamagesPlayerInflictsToFoe();
    
    // "Vous infligez X dégâts à l'Ombre"
    $this->action->postAction($this->database, 'atk', 0,0,0,0,0,0,0, $lostHp);
    
        // Renvoyer les nouveaux PV du monstre pour actualiser la barre de vie.
    //echo $foeHp;
    $response[1] = $foeHp;
    
    // Réduit la durabilité de la lance équipée si elle existe.
    if($spearDur > 0)
    {
      $this->damageSpear();
    }
    
    $this->updateFoeHp();
    
    if($foeHp <= 0)
    {
      $this->killFoe();
      $this->lootMetal();
    }

    if($this->battleHasEnded)
    {
      $this->endBattle();
    }
  }
  
// TODO
private function computeDamagesPlayerInflictsToFoe()
{
    $dmgInflicted = 0;

    // TODO : compute dmg


    #region adds damage reduction based on building
    $DmgBonusBld = 0;

    // Si seul le 1er niveau de collecte est construit (par défault)
    if(in_array(19, $_SESSION['id_bld']) && !in_array(20, $_SESSION['id_bld']) && !in_array(21, $_SESSION['id_bld'])){
        $DmgBonusBld = 0;
    }
    // Si le 2nd niveau de collect est construit
    else if(in_array(20, $_SESSION['id_bld']) && !in_array(21, $_SESSION['id_bld'])){
        $DmgBonusBld = 1;
    }
    // Si le 3e niveau est construit
    else if(in_array(21, $_SESSION['id_bld'])){
        $DmgBonusBld = 3;
    }

    #endregion

    // Apply the dmg bonus
    $dmgInflicted += $DmgBonusBld;

    // Never less than 1 dmg given
    if ($dmgInflicted < 1)
    {
        $dmgInflicted = 1;
    }

    return $dmgInflicted;

}
  
private function damageSpear()
{
    $strengthDiff = 0;
    $spearDur = 0;
    $lostDur = 0;

    #region Reduce the loss of dirability of the spear based on the building built

    $dmgBonusReductionBld = 0;

    // Si seul le 1er niveau de combat est construit (par défault)
    if(in_array(19, $_SESSION['id_bld']) && !in_array(20, $_SESSION['id_bld']) && !in_array(21, $_SESSION['id_bld'])){
        $dmgBonusReductionBld = 0;
    }
    // Si le 2nd niveau de combat est construit
    else if(in_array(20, $_SESSION['id_bld']) && !in_array(21, $_SESSION['id_bld'])){
        $dmgBonusReductionBld = 1;
    }
    // Si le 3e niveau est construit
    else if(in_array(21, $_SESSION['id_bld'])){
        $dmgBonusReductionBld = 3;
    }

    #endregion

    // Réduit la durabilité de la lance équipée si elle existe.
    $strengthDiff = $this->current_foe_def - $this->current_player_atk;
    if($strengthDiff > 0)
    {
        $lostDur = $strengthDiff;
    // Si le joueur est plus fort que le monstre, il son équipement perd quand même de la durabilité.
    }
    else
    {
        $lostDur = 1;
    }

    // Apply the bonus dmg to equipment
    $lostDur -= $dmgBonusReductionBld;

    // Never less than 1 HP lost
    if ($lostDur < 1)
    {
        $lostDur = 1;
    }

    $spearDur -= $lostDur;
    $this->action->postAction($this->database, 'atk-spear', 0, 0, 0, 0, 0, 0, 0, $lostDur);
    // $response[8] = $spearDur;

    // Si la lance est brisée
    if ($spearDur <= 0)
    {
        $_SESSION['player_spear_lvl'] = 0;
        $_SESSION['player_spear_dur'] = 0;
        $query="UPDATE city_item SET item_pos='', is_alive=0
            WHERE id_player=".$this->id_player." 
              AND id_city=".$this->id_city." 
              AND item_pos='spear' 
              AND is_alive=1";
        //TOBEDONE postaction : votre lance est brisée
    }
    else
    {
        //update la vie de la lance
        $_SESSION['player_spear_dur'] = $spearDur;
        $query="UPDATE city_item SET item_dur=".$spearDur."
            WHERE id_player=".$this->id_player." 
              AND item_pos='spear' 
              AND is_alive=1
            LIMIT 1";
    }
    try 
    {
        $res = $this->database->query($query);
    }
    catch (PDOException $e)
    {
     
    }
}
  
  private function updateFoeHp()
  {
        //update foe_hp
    //mettre le char qui correspond aux PV du monstre à 0 (ne pas supprimer le char, car si un autre joueur modifie la chaine avant nous, la pos du char dans la string sera modifiée)
    //en plus ça donne le nombre de monstres initial pour le respawn !

    // Convert to hexa
    if($this->current_foe_hp == 10)
    {
        $this->current_foe_hp='A';
    }
    else if($this->current_foe_hp < 0)
    {
        $this->current_foe_hp='0';
    }

    // Crée la nouvelle chaine foeHP pour la case avec la nouvelle valeur des PV du monstre affronté.
    $newFoeHpList = substr_replace($result['foe_hp'], $foeHp, $posInString, 1); //fct can do many things https://www.php.net/manual/en/function.substr-replace.php
    
        switch($this->player_area)
        {
        case 'outside':
            $query="UPDATE map SET foe_hp='".$newFoeHpList."'
                WHERE id_city=".$this->id_city." 
                  AND x=".$this->player_pos_x." 
                  AND y=".$this->player_pos_y;
        break;
        case 'dj':
            $query="UPDATE dj SET dj_foe_hp='".$newFoeHpList."'
                 WHERE id_dj=".$this->id_city." 
                   AND dj_x=".$this->player_pos_x." 
                   AND dj_y=".$this->player_pos_y;
            break;
        case 'abyss':
            $query="UPDATE abyss SET abyss_foe_hp='".$newFoeHpList."'
                WHERE id_abyss=".$this->id_city." 
                  AND abyss_x=".$this->player_pos_x." 
                  AND abyss_y=".$this->player_pos_y;
            break;
        case 'dj2':
            $query="UPDATE dj2 SET dj2_foe_hp='".$newFoeHpList."'
                WHERE id_dj2=".$this->id_city." 
                  AND dj2_x=".$this->player_pos_x." 
                  AND dj2_y=".$this->player_pos_y;
            break;
         case 'hell':
            $query="UPDATE hell SET hell_foe_hp='".$newFoeHpList."'
                WHERE id_hell=".$this->id_city." 
                  AND hell_x=".$this->player_pos_x." 
                  AND hell_y=".$this->player_pos_y;
            break;
        default:
            echo 'player area non reconnue'; //??
            return '...'; //??
    }
    try
    {
        $this->database->query($query);
        //echo $newFoeHpList;
    }
    catch (PDOException $e)
    {
     
    }

  }
  
  private function killFoe()
  {
    
  }
  
  private function lootMetal()
  {
        // Le joueur loot-il du métal ? (1% de chances, 100% si DJ2)
        $lootMetal = false;
        if($this->player_area == 'dj2')
        {
            $lootMetal = true;
        }
        else
        {
            $m = rand(1,100);
            if($m == 100)
            {
                $lootMetal = true;
            }
        }

        // Add the looted metal to the inventory
        if($lootMetal)
        {
            // If sypply is master, add 20%
            if($_SESSION['is_master'] == 1 && $_SESSION['master_stat'] == 'supply_stat')
            {
                $maxInvent = $_SESSION['max-invent'] + floor($_SESSION['max-invent']*20/100);
            }
            else
            {
                $maxInvent = $_SESSION['max-invent'];
            }

            //vérifier la place restante dans l'inventaire
            if($_SESSION['player-invent-size'] >= $maxInvent)
            {
                echo "Inventaire plein.";
                die();
            }

            $query="INSERT INTO city_item(id_city, id_player, id_item, item_lvl, item_pos) 
                    VALUE(".$this->id_city.", ".$this->id_player.", 20, 999, 'invent')";
            try
            {
                $this->database->query($query);
            }
            catch (PDOException $e)
            {
                
            }
            
            $this->statistic->updatePlayerMetalLootStatistic($this->database, 1);
            $this->action->postAction($this->database, 'loot-metal');
        }

  }
  
  private function endBattle()
  {
        $this->action->postAction($this->database, 'foedead');
        updatePlayerAtkStat($this->database, 20);
        updatePlayerDefStat($this->database, 20);
        return 'endfight'; //??
  }
}