<?php

/**
 * Inventory class
 * Contains methods linked to items, like harvesting, crafting, or inventory display
 * 
 * Factorisation : most methods in this class are widely used.
 * Caching : This class uses the $_SESSION['items'] session array for the player's items,
 *  And the "items=" cache Key for the city inventory and shared items.
 */
class Invent
{
    // Gives a unique ID to every item element on the page
    private $indexItems = 0;

    // Index de l'id BDD dans la $listeID pour comparaison
    private $indexList=0; 

    // Liste des ID d'items en table pour comparaison. Public because it will be put in session
    public $inventsList=[]; 

    // Size of the player's inventory. Public because it will be put in session
    public $playerInventSize = 0;

    // Inventories
    public $inventPlayer;
    public $inventCity;
    public $inventCitySize = 0;
    public $shieldEquiped;
    public $upperEquiped;
    public $lowerEquiped;
    public $helmetEquiped;
    public $maskEquiped;
    public $spearEquiped;

    // Sessions
    private $idPlayer = 0;
    private $isMaster = 0;
    private $masterStat = "";
    private $maxInvent = 0;
    private $playerArea = "";
    private $idCity = 0;
    private $idCamp = 0;

    private $itemsId = [];
    private $itemsName = [];
    private $itemsClass = [];
    //private $items_sprite_path = [];
    private $itemsLvl = [];
    private $itemsDur = [];
    private $itemsCrafter = [];
    private $itemsHarvester = [];

    // Classes
    private $database;
    //private $I18N;
    //private $Cache;
    

    public function __construct() {
        //$this->getItemsStats($inventType, $id_player, $id_city, $id_camp);

        // Necessairy ?
        //$this->mysql=$this->getMy //super class inherited by this class

        // Classes used
        $this->database = new Database();
        //$this->i18N = new I18n();
        //$this->cache = new Cache();
        // new Database() ; new I18N() ; new Cache();

        // Keep sessions as properties
        // it means that the object must be instanciated on each page.
        if(isset($_SESSION['id_player']))
            $this->idPlayer = $_SESSION['id_player'];

        if(isset($_SESSION['is_master']))
            $this->isMaster = $_SESSION['is_master'];

        if(isset($_SESSION['master_stat']))
            $this->masterStat = $_SESSION['master_stat'];

        if(isset($_SESSION['max-invent']))
            $this->maxInvent = $_SESSION['max-invent'];

        if(isset($_SESSION['player_area']))
            $this->playerArea = $_SESSION['player_area'];

        if(isset($_SESSION['id_city']))
            $this->idCity = $_SESSION['id_city'];

        if(isset($_SESSION['id_camp']))
            $this->idCamp = $_SESSION['id_camp'];
    }

    #region Inventories

    /**
     * 
     * Gets all items available on the page, in multiple lists (playerInvent, cityInvent etc)
     * A Super-List keeps all the items loaded. It is used to ...
     * 
     */
    public function getItemsList(bool $loadInventPlayer, bool $loadInventCity, bool $loadEquipedItems)
    {
        $inv = [];
        $n = 0;

        // Get and format the player's inventory
        if($loadInventPlayer === true)
        {
            $inv = $this->getPlayerItemsList();
            $this->inventPlayer = $this->FormatInvent($inv, "player");

            // Keep the player's inventory current size. ?????
            //if (isset($inv['id_city_item'])){ 
            //    $n = sizeof($inv['id_city_item']);
            //} 
            //$this->playerInventSize = $n;

            // Store the current invent size, for harvesting limit
            $this->playerInventSize = sizeof($inv);
        }

        //Get and format the city's inventory
        if($loadInventCity === true)
        {
            $inv = $this->getCityItemsList();
            
            $this->inventCity = $this->FormatInvent($inv, "city");

            // Compute city invent size
            if (isset($inv['id_city_item']))
            {
                $this->inventCitySize = sizeof($inv['id_city_item']);
            }
            else
            {
                $this->inventCitySize = 0;
            }
        }

        //Get and format the equipped items
        if($loadEquipedItems === true)
        {
            $inv = $this->getEquipedItemsList("shield");
            $this->shieldEquiped = $this->FormatInvent($inv, "shield");

            $inv = $this->getEquipedItemsList("upper");
            $this->upperEquiped = $this->FormatInvent($inv, "upper");

            $inv = $this->getEquipedItemsList("lower");
            $this->lowerEquiped = $this->FormatInvent($inv, "lower");

            $inv = $this->getEquipedItemsList("helmet");
            $this->helmetEquiped = $this->FormatInvent($inv, "helmet");

            $inv = $this->getEquipedItemsList("mask");
            $this->maskEquiped = $this->FormatInvent($inv, "mask");

            $inv = $this->getEquipedItemsList("spear");
            $this->spearEquiped = $this->FormatInvent($inv, "spear");
        }

    }

    // Factorisation de la tooltip si possible
    private function itemTooltip()
    {

    }

    private function getPlayerItemsList()
    {
        $err = '';
        $query="SELECT ci.id_city_item, ci.item_lvl, ci.item_dur, 
                i.item_name
            FROM city_item ci JOIN item i ON i.id_item = ci.id_item
            WHERE ci.id_player=".$this->idPlayer." AND ci.id_city=".$this->idCity." AND ci.item_pos='invent' AND ci.is_alive=1 AND item_category <> 'farm' 
            ORDER BY FIELD(item_category, 'food', 'resource'), ci.id_city_item DESC";
        try {

            $res=$this->database->mysql->query($query);
            $result=[];
            if($res) {
               while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $result['id_city_item'][] = $data['id_city_item']; // toutes les lignes, accessibles avec $result['id_item'][$i]
                    $result['item_name'][]= $data['item_name'];
                    $result['item_lvl'][]= $data['item_lvl'];
                    $result['item_dur'][]= $data['item_dur'];
               }
           }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction updatePlayerStatistic : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);


        return $result;

        /*
        //get harvester and crafter name (for tooltip)
        $query="SELECT p.player_name AS harvester_name
            FROM city_item ci JOIN player p ON ci.id_harvester = p.id_player
            WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND ci.item_pos='invent' AND ci.is_alive=1"; 
        try {
            $res=$database->mysql->query($query);
            if($res) {
               while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $result['harvester_name'][]= $data['harvester_name'];
               }
           }
        } catch (PDOException $e){
            echo $e->getMessage();
        }
        $query="SELECT p.player_name AS crafter_name
            FROM city_item ci JOIN player p ON ci.id_crafter = p.id_player
            WHERE ci.id_player=".$_SESSION['id_player']." AND ci.id_city=".$_SESSION['id_city']." AND ci.item_pos='invent' AND ci.is_alive=1"; 
        try {
            $res=$database->mysql->query($query);
            if($res) {
               while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    if($data['crafter_name']){
                        $result['crafter_name'][]= $data['crafter_name'];
                    } else {
                        $result['crafter_name'][] = 0;
                    }
               }
            }

        } catch (PDOException $e){
            echo $e->getMessage();
        }*/

        /*inventPlayer = tableau de comparaison entre id table et id div
           [id_item]
               0 => 3442
               1 => 3443
               2 => 3444 ...
    */
    }

    private function getCityItemsList()
    {
        $err = '';
        if($this->playerArea == 'inside')
        {
            $query="SELECT ci.id_city_item, i.item_name, ci.item_dur, ci.item_lvl
                FROM city_item ci JOIN item i ON i.id_item = ci.id_item
                WHERE ci.id_city=".$this->idCity." AND ci.item_pos='bank' AND ci.is_alive=1 AND item_category <> 'farm' 
                ORDER BY FIELD(item_category, 'food', 'resource'), ci.id_city_item DESC"; //AND item_category <> 'rare'
        }
        else if ($this->playerArea == 'camp')
        {
            $query="SELECT ci.id_city_item, i.item_name, ci.item_lvl, ci.item_dur
                FROM city_item ci JOIN item i ON i.id_item = ci.id_item
                WHERE ci.id_city=".$this->idCity." AND ci.item_pos='bank' AND ci.is_alive=1 AND id_camp=".$this->idCamp." AND item_category<>'farm'
                ORDER BY FIELD(item_category, 'food', 'resource'), ci.id_city_item DESC";
        }
        
         try {
             $res=$this->database->mysql->query($query);
             $result=[];
             if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $result['id_city_item'][] = $data['id_city_item'];
                    $result['item_name'][] = $data['item_name'];
                    $result['item_lvl'][]= $data['item_lvl'];
                    $result['item_dur'][]= $data['item_dur'];
                }
            }
         } 
         catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction updatePlayerStatistic : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);


         return $result;

    }

    private function getEquipedItemsList($part)
    {
        $err = '';
        $query="SELECT ci.id_city_item, i.item_name, ci.item_dur, ci.item_lvl
            FROM city_item ci JOIN item i ON i.id_item = ci.id_item
            WHERE ci.id_player=".$this->idPlayer." AND ci.id_city=".$this->idCity." AND ci.item_pos='".$part."' AND ci.is_alive=1"; // ATTENTION AUX GUILLEMETS AUTOUR DES STRINGS
        try {
            $res=$this->database->mysql->query($query);
            $result=[];
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                    $result['id_city_item'][] = $data['id_city_item']; //tjr 1 seul item equipé par position
                    $result['item_name'][] = $data['item_name'];
                    $result['item_lvl'][]= $data['item_lvl'];
                    $result['item_dur'][]= $data['item_dur'];
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction updatePlayerStatistic : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);


        return $result;
    }

    /**
     *  Create the HTML code for the list of items.
     *  It assumes that every item has the same format, regardless of its position (invent, city, camp, equip).
     *  Differences between items can be added with conditions (if)
     * 
     *  @param $invent : the list of items to format into an inventory.
     */
    public function FormatInvent($invent, $type)
    {
        $inventFormatted='';

        // Get the size of the inventory
        if(isset($invent['id_city_item'])){ 
            $n = sizeof($invent['id_city_item']);
        } else {$n=0;}

        // Create a key/value array to link the html ID to the DB ID (for client sanitization)
        if(isset($invent['id_city_item'])){
            foreach ($invent['id_city_item'] as $tableIdItemKey)
            {
                $this->inventsList[$this->indexList] = $tableIdItemKey;
                $this->indexList++;
            }
        }

        for($i=0;$i<$n;$i++) {
            //define the color of the durability div
            $d = $invent['item_dur'][$i];
            switch($d){
                case ($d <= 10 && $d > 5):
                    $color = '#84DE02';
                    break;
                case ($d <= 5 && $d >= 3):
                    $color = 'orange';
                    break;
                case ($d < 3):
                    $color = 'amaranth';
                    break;
                default:
            }
            //drag & drop
            $inventFormatted.="<div id=".$this->indexItems." class='item item-".$invent['item_name'][$i]."' draggable='true' ondragstart='drag(event)'>";
            //lvl and life displayed on item
            $inventFormatted.="<div class='item-life-box' style='width:".($invent['item_dur'][$i]*10)."%; background-color:".$color.";'></div>";
            $inventFormatted.="<div class='item-lvl-box'><strong>".$invent['item_lvl'][$i]."</strong></div>";
            //tooltip info
            $inventFormatted.='<span class="tooltiptext"><strong>'.L($invent['item_name'][$i]).
                        '</strong><br/>
                        Niveau : '.$invent['item_lvl'][$i].'
                        <br/>
                        Vie : <span class="item-dur-label">'.$invent['item_dur'][$i].'</span>/10';
                        //<hr>';
                        /*if($inventPlayer['crafter_name'][$i] && $inventPlayer['crafter_name'][$i] != 0){
                            $inventPlayerFormatted.='Créé par : <span class="item-crafter-label">'.$inventPlayer['crafter_name'][$i].'</span><br/>';
                        }
                        $inventPlayerFormatted.='Récolté par : <span class="item-harvester-label">'.$inventPlayer['harvester_name'][$i].'</span> 
                        <br/>'; */
                    
                        //Boutons d'action dans la tooltip
                        if ($invent['item_name'][$i] == ('orange')){
                            //$inventPlayerFormatted.='<hr><button class="eat-orange">Manger</button><br/>'; 
                            $inventFormatted.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                        }
                        else if ($invent['item_name'][$i] == ('honey')){
                            //$inventPlayerFormatted.='<hr><button class="eat-honey">Manger</button><br/>'; 
                            $inventFormatted.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                        }
                        else if ($invent['item_name'][$i] == ('baked_orange')){
                            //$inventPlayerFormatted.='<hr><button class="eat-baked_orange">Manger</button><br/>'; 
                            $inventFormatted.='<br/><br/>Clic droit : <strong>manger</strong>.'; 
                        }
                        else if ($invent['item_name'][$i] == ('baked_honey')){
                            //$inventPlayerFormatted.='<hr><button class="eat-baked_honey">Manger</button><br/>'; 
                            $inventFormatted.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                        }
                        else if ($invent['item_name'][$i] == ('cake')){
                            //$inventPlayerFormatted.='<hr><button class="eat-cake">Manger</button><br/>'; 
                            $inventFormatted.='<br/><br/>Clic droit : <strong>manger</strong>'; 
                        }
            $inventFormatted.='</span></div>';
                    
            $this->indexItems++;

        }

        // Adds empty items at the end of the player's invent
        if($type == "player")
            $inventFormatted = $this->addVoidItems($inventFormatted, $n);
        
        

        return $inventFormatted;
    }

    /**
     * 
     * Adds empty items at the end of the player's invent to reach max-invent.
     * If the supply stat is master, add 20%.
     * 
     */
    private function addVoidItems($inventFormatted, $inventSize)
    {
        $maxInvent = 0;
        $emptyItemsToAdd = 0;
        if($this->isMaster == 1 && $this->masterStat == 'supply_stat'){
            $maxInvent = $this->maxInvent + floor($this->maxInvent*20/100);
            $emptyItemsToAdd = $this->maxInvent - $inventSize;
        } else {
            $emptyItemsToAdd = $this->maxInvent - $inventSize;
        }

        for($i=0; $i<$emptyItemsToAdd; $i++){
            $inventFormatted .= "<div class ='item item-void'></div>";
        }

        return $inventFormatted;
    }

    #endregion

    #region Item History

    /**
     * Items history
     * 
     * Get the list of every item linked to the player.
     * Linked means that the player either crafted the item, harvested it, or uses it currently.
     * 
     * Displays who made the item, who owns it now, and if it is still alive.
     * It will most likely be displayed in a page module, and the query to display it will be made
     *  using ajax, on click on the closed module to open it.
     * Part of it may be displayed in the item's tooltip (a statuer)
     * 
     * The cache may be implemented to store history data of items (in the "items" array used by the city
     * inventory), but is complex as many users have to write their item changes into the cache.
     */

    // Get the usage history of items
    public function getItemsHistory()
    {
        
        //TOTO : transformer itemsList en liste d'objets items.

        $itemsList = [];
        $itemsListFormatted = [];

        //Position actuelle

        //Inventaire d'un joueur
        $query = 'SELECT i.item_name, ci.item_lvl
                    FROM city_item ci INNER JOIN item i ON i.id_item = ci.id_item
                    WHERE (id_crafter='.$this->idPlayer.' OR id_harvester ='.$this->idPlayer.') 
                        AND item_pos = :item_pos 
                        AND is_alive=1
                    LIMIT 100';
        $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        /**
         * Execute multiple requests with the same DB connection.
         * Allows adding a new item zone outside the query.
        */ 

        // Get the items linked to the player, currently in an inventory.
        $sth->execute(array(':item_pos' => "invent"));
        while($data=$sth->fetch(PDO::FETCH_ASSOC)) {
            if($data){
                $itemsList['item_name'][] = $data['item_name'];
                $itemsList['item_lvl'][] = $data['item_lvl'];
                $itemsList['item_pos'][] = 'invent';
                $itemsList['is_item_crafted'][] = 0;
                $itemsList['death_reason'][] = 0;
            }
        }

        // Get the items linked to the player, currently equiped.
        //TODO


        // Get the items linked to the player, currently in the city inventory.
        $sth->execute(array(':item_pos' => "city"));
        while($data=$sth->fetch(PDO::FETCH_ASSOC)) {
            if($data){
                $itemsList['item_name'][] = $data['item_name'];
                $itemsList['item_lvl'][] = $data['item_lvl'];
                $itemsList['item_pos'][] = 'city';
                $itemsList['is_item_crafted'][] = 0;
                $itemsList['death_reason'][] = 0;
            }
        }

        // Get the items linked to the player, currently in a camp inventory
        $sth->execute(array(':item_pos' => "camp"));
        while($data=$sth->fetch(PDO::FETCH_ASSOC)) {
            if($data){
                $itemsList['item_name'][] = $data['item_name'];
                $itemsList['item_lvl'][] = $data['item_lvl'];
                $itemsList['item_pos'][] = 'camp';
                $itemsList['is_item_crafted'][] = 0;
                $itemsList['death_reason'][] = 0;
            }
        }

        //Item used for :

        //Craft 
        // PB ? overwrite certains objets.
        $query = 'SELECT i.item_name, ci.item_lvl
                    FROM city_item ci INNER JOIN item i ON i.id_item = ci.id_item
                    WHERE (id_crafter='.$this->idPlayer.' OR id_harvester ='.$this->idPlayer.') AND id_item_crafted <> 0';
        try {
            $res=$this->database->mysql->query($query);
            if($res) {
                while($data=$sth->fetch(PDO::FETCH_ASSOC)) { 
                    $itemsList['item_name'][] = $data['item_name'];
                    $itemsList['item_lvl'][] = $data['item_lvl'];
                    $itemsList['item_pos'][] = 0;
                    $itemsList['is_item_crafted'][] = 1;
                    $itemsList['death_reason'][] = 0;
                }
            }
        } catch (PDOException $e){
            $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
            return false;
        }


        //Eat
        $query = 'SELECT i.item_name, ci.item_lvl
                    FROM city_item ci INNER JOIN item i ON i.id_item = ci.id_item
                    WHERE (id_crafter='.$this->idPlayer.' OR id_harvester ='.$this->idPlayer.') AND ci.death_reason = "eaten"';
        try {
            $res=$this->database->mysql->query($query);
            if($res) {
                while($data=$sth->fetch(PDO::FETCH_ASSOC)) {
                    $itemsList['item_name'][] = $data['item_name'];
                    $itemsList['item_lvl'][] = $data['item_lvl'];
                    $itemsList['item_pos'][] = 0;
                    $itemsList['is_item_crafted'][] = 0;
                    $itemsList['death_reason'][] = 'eaten';
                }
            }
        } catch (PDOException $e){
            $e->getMessage();
            //$_SESSION['yoda_debug']=$e->getMessage()."\n";
            return false;
        }

        /*
        // death reason
        $query = 'SELECT death_reason
                    FROM city_item
                    WHERE id_player ='.$_SESSION['id_player'].' AND death_reason != 0
                    LIMIT 50';
        try {
            $res = $database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)){ // LIMIT 1

                }
            }
        } catch (PDOException $e){
            $e->getMessage();
                //$_SESSION['yoda_debug']=$e->getMessage()."\n";
            return false;
        }*/

        return $itemsList;
    }

    // Formats the data from getItemsHistory
    public function formatItemsHistory($itemsList)
    {
        // Initialize $n even if no result returned
        if(!isset($itemsList['item_name']))
        $n=0;
        else
        $n = sizeof($itemsList['item_name']);

        $itemsHistory = '';
        $itemsHistory .= '<li><p style="text-align:center;"><strong>Historique des objets</strong></p></li>';
        for ($i=0; $i<$n; $i++){
        
            $itemsHistory .= '<li><p>Votre '.L($itemsList['item_name'][$i]).' de niveau '.$itemsList['item_lvl'][$i];
        
            //item current position
            //if(isset($itemsList[$i]['item_area']) && $itemsList[$i]['death_reason'] == 0){
                if ($itemsList['item_pos'][$i] == 'invent'){
                    $itemsHistory .= " est dans l'inventaire d'un joueur.";
                } 
                else if ($itemsList['item_pos'][$i] == 'upper'
                            || $itemsList['item_pos'][$i] == 'lower'
                            || $itemsList['item_pos'][$i] == 'helmet'
                            || $itemsList['item_pos'][$i]== 'mask'
                            || $itemsList['item_pos'][$i] == 'shield'
                            || $itemsList['item_pos'][$i] == 'spear'){
                    $itemsHistory .= ' est équipé par un joueur.';
                } 
                else if ($itemsList['item_pos'][$i] == 'camp'){
                    $itemsHistory .= ' est dans le stock d\un camp.'; // ('.$itemsList[$i]['camp_pos_x'].','.$itemsList[$i]['camp_pos_y'].').';
                }
                else if ($itemsList['item_pos'][$i] == 'city'){
                    $itemsHistory .= ' est dans le stock de la ville.';
                }
                else { //item_pos = 0
                    if($itemsList['is_item_crafted'][$i] == 1){
                        $itemsHistory .= ' a servi à la création d\un objet.';
                    }
                    else if ($itemsList['death_reason'][$i] == 'eaten'){
                        $itemsHistory .= ' a été mangé par un joueur.';
                    }
                }
           // }
            
            //item death reason
           /* if(isset($itemsList[$i]['death_reason']) && $itemsList[$i]['death_reason'] != 0){
                if ($itemsList[$i]['death_reason'] == 'dur'){
                    $itemsHistory .= ' a été détruit par le passage du temps.';
                } else if ($itemsList[$i]['death_reason'] == 'foe'){
                    $itemsHistory .= ' a été détruit par une Ombre.';
                } else if ($itemsList[$i]['death_reason'] == 'wave'){
                    $itemsHistory .= ' a été détruit par une Vague.';
                } else if ($itemsList[$i]['death_reason'] == 'craft'){
                    $itemsHistory .= ' a servi pour fabriquer '.$itemsList[$i]['item_crafted_name'].' de niveau '.$itemsList[$i]['item_crafted_lvl'];
                }
            }*/
            $itemsHistory .= '</p></li>';
        }
        $itemsHistory .= '<li><p><strong>(Limité à 50 items)</strong></p></li>';

        return $itemsHistory;
    }
    
    #endregion

    // Unused
    // Transformer les listes en obj item
    public function getItemsStats($inventType, $id_player, $id_city, $id_camp)
    { //??? $id_player nécessaire ??? essayer SESSION direct
        $err = '';
        $query = 'SELECT ci.id_item, i.item_name, ci.item_lvl, ci.item_dur, ci.id_harvester, ci.id_crafter
        FROM city_item ci INNER JOIN item i ON ci.id_item = i.id_item
        WHERE id_city='.$id_city.' AND pos="'.$inventType.'" AND is_alive=1 AND id_player='.$id_player;
        
        if ($id_camp !== 0){
            $query.=" AND id_camp=".$id_camp;
        }
        switch($inventType){
            case "invent-forge":
                $query.=" AND 'category'=('resource' OR 'weapon')";
                break;
            case "bank-forge":
                $query.=" AND 'category'=('resource' OR 'weapon')";
                break;
            case "invent-armory":
                $query.=" AND 'category'=('resource' OR 'armor')";
                break;
            case "bank-armory":
                $query.=" AND 'category'=('resource' OR 'armor')";
                break;
            case "invent-kitchen":
                $query.=" AND 'category'='food'";
                break;
            case "bank-kitchen":
                $query.=" AND 'category'='food'";
                break;
            default:
                break;
        }
        
                
        try {
            $res=$this->database->mysql->query($query);
            if($res) {
                while($data=$res->fetch(PDO::FETCH_ASSOC)) {}
                    $this->itemsId[] = $data['id_item'];
                    $this->itemsName[] = $data['item_name'];
                    $this->itemsLvl[] = $data['item_lvl'];
                    $this->itemsDur[] = $data['item_dur'];
                    $this->itemsCrafter[] = $data['id_crafter'];
                    $this->itemsHarvester[] = $data['id_harvester'];

                    $this->itemsClass[] = floor($data['item_lvl'] / 100) + 1;
                }
            
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
                a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction updatePlayerStatistic : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);


    }

    /*
    function loadFollowList($database, $area){

        /*
        Dehors : item de catégorie "resource" uniquement
        "Votre item (nom) de niveau (lvl) a été transformé en (nom) par (nom) et est porté par (nom)"
        Si l'id_harvester = id_player : on affiche pas l'item

        Zone de craft (forge, armurerie ou cuisine) peu importe l'endroit (ville ou camp)
        "Votre item (nom) de noiveau (lvl) est porté par (nom)"
        Si l'id_crafter = id_player : on affiche pas l'item

        Porteur : rester aux infos dans les tooltips pour l'instant

        Si l'objet est utilisé dans un bâtiment
        "Votre item (nom) de niveau (lvl) a été utilisé par la ville dans la construction du bâtiment (nom)"

        En fin de chaîne : 
        "Il est maintenant DETRUIT"
        
        $id_player = $_SESSION['id_player'];
        $id_city = $_SESSION['id_city'];
        if(!empty($_SESSION['id_camp'])) {
            $id_camp = $_SESSION['id_camp'];
        } else { $id_camp = 0;}
        
        $itemFollowListObj = new ItemFollowList($inventType, $id_player, $id_city, $id_camp);
        $size = sizeof($inventObj->itemsId);

        echo '<ul>';
        for ($i=0; $i<$size; $i++) {
            echo '<li id="'.$inventObj->itemsId[i].'> 
                Votre objet '.$itemFollowListObj->itemsName[i].' de niveau '.$itemFollowListObj->itemsLvL[i];

                if ($area == 'outside') {
                    echo ' a été transformé par '.$itemFollowListObj->itemsCrafter[i].' en '.$itemFollowListObj->itemsCraftedItemName[i].''
                }
                
            echo '</li>';
        }
        echo '</ul>';
    }


    */
    
}
    