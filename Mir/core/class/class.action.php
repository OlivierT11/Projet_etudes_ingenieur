<?php

/**
 * 
 * Class to handle the user actions (adding and displaying)
 * 
 */

class Action
{
    private $userName = '';
    private $serverName = '';
    private $password = '';
    private $dbName = '';

    private $database;
    private $log;

    private $idCity = 0;
    
    public function __construct($database, $log)
    {
        // Objects
        $this->database = $database;
        $this->log = $log;

        // Variables
        $this->idCity = $_SESSION['id_city'];
        $this->idPlayer = $_SESSION['id_player'];
    }

    // Get the first 50 player actions.
    public function getAllActions()
    {
        $actionsList = [];

        // (Caching) Use the session if exists
        /*
        if(isset($_SESSION['player_actions']) && sizeof($_SESSION['player_actions']) != 0)
        {
            $actionsList = $_SESSION['player_actions'];
            //var_dump($actionsList);
        }
        else
        {*/
            $actionsLimit = 50;
            $err = '';
            $query="SELECT p.player_name, 
                        cn.action_date, cn.action_content
                        FROM city_action cn INNER JOIN player p ON cn.id_player = p.id_player 
                        WHERE cn.id_city=".$this->idCity." ORDER BY cn.id_city_action DESC LIMIT ".$actionsLimit;
            try {
                $res=$this->database->mysql->query($query);
                if($res){
                    while($data=$res->fetch(PDO::FETCH_ASSOC)){
                       /* array_push($actionsList, array('action_date' => $data['action_date'], 
                                                       'action_content' => $data['action_content'], 
                                                       'player_name' => $data['player_name']));*/

                        $actionsList['action_date'][] = $data['action_date'];
                        $actionsList['action_content'][] = $data['action_content'];
                        $actionsList['player_name'][] = $data['player_name'];
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
    

            // Keep the actions in session to use as cache
            $_SESSION['player_actions'] = $actionsList;
       // }

        // Format the list of actions to disply it as HTML.
        $actionsListFormatted = $this->formatActions($actionsList);

        return $actionsListFormatted;
    }

    // Format the list of actions into HTML to display on page.
    private function formatActions($actionsList)
    {
        if(isset($actionsList['player_name']))
        {
            $n = sizeof($actionsList['player_name']);
        }
        else
        {
            // Si aucune action
            $n = 0;
        }

        $actionsListFormatted = '';
        $counter=1;

        $actionsListFormatted='<li><p style="text-align:center;"><strong>Historique des actions</strong></p></li>';
        for ($i=0; $i<$n; $i++){

            $actionsListFormatted .= '<li><p>';
            $actionsListFormatted .= $actionsList['action_date'][$i].' | '.$actionsList['player_name'][$i].' | '.$actionsList['action_content'][$i];
            $actionsListFormatted .= '</p></li>';
            
            $counter++;
        }

        if($counter == 50){
            $actionsListFormatted .= '<li><p><strong>(Limité à 50 items)</strong></p></li>';
        } else {
            $actionsListFormatted .= '</p></li>';
        }
        return $actionsListFormatted;
    }

    /**
     * 
     * Formats and posts a new action to the database
     * @param $action type d'action
     * 
     */
    public function postAction($action, $qty=null, $item=null, $lvl1=null, $lvl2=null, $lvl3=null, $lvl4=null, $lvl5=null, $dmg=null){

        $actionDate = date("H:i");
        $actionContent='';
        $qty=0;

        // get the value of stat lvl and xp to display XP/LVL gain after the Action text
        // A utiliser si on veut afficher le lvl et l'XP dans le texte de l'Action.
        // Pour l'instant ils sont affichés dans la stat box.
        // Ces variables sont à initializer individuellement dans le switch($action) correspondant.
        /*
        $woodLVL = $_SESSION['player_stat']['woodcutter_stat']['player_stat_lvl'];
        $woodXP = $_SESSION['player_stat']['woodcutter_stat']['player_stat_xp'];
        $atkLVL = $_SESSION['player_stat']['atk_stat']['player_stat_lvl'];
        $atkXP = $_SESSION['player_stat']['atk_stat']['player_stat_xp'];
        $defLVL = $_SESSION['player_stat']['def_stat']['player_stat_lvl'];
        $defXP = $_SESSION['player_stat']['def_stat']['player_stat_xp'];
        $exploLVL = $_SESSION['player_stat']['explo_stat']['player_stat_lvl'];
        $exploXP = $_SESSION['player_stat']['explo_stat']['player_stat_xp'];
        $supplyLVL = $_SESSION['player_stat']['supply_stat']['player_stat_lvl'];
        $supplyXP = $_SESSION['player_stat']['supply_stat']['player_stat_xp'];
        $farmLVL = $_SESSION['player_stat']['farm_stat']['player_stat_lvl'];
        $farmXP = $_SESSION['player_stat']['farm_stat']['player_stat_xp'];
        $pikeLVL = $_SESSION['player_stat']['forge_pike_stat']['player_stat_lvl'];
        $pikeXP = $_SESSION['player_stat']['forge_pike_stat']['player_stat_xp'];
        $upLVL = $_SESSION['player_stat']['forge_up_stat']['player_stat_lvl'];
        $upXP = $_SESSION['player_stat']['forge_up_stat']['player_stat_xp'];
        $headLVL = $_SESSION['player_stat']['forge_head_stat']['player_stat_lvl'];
        $headXP = $_SESSION['player_stat']['forge_head_stat']['player_stat_xp'];
        $orangeLVL = $_SESSION['player_stat']['cook_orange_stat']['player_stat_lvl'];
        $orangeXP = $_SESSION['player_stat']['cook_orange_stat']['player_stat_xp'];
        $honeyLVL = $_SESSION['player_stat']['cook_honey_stat']['player_stat_lvl'];
        $honeyXP = $_SESSION['player_stat']['cook_honey_stat']['player_stat_xp'];
        $shieldLVL = $_SESSION['player_stat']['forge_shield_stat']['player_stat_lvl'];
        $shieldXP = $_SESSION['player_stat']['forge_shield_stat']['player_stat_xp'];
        */

        //if multiple items are looted
        $lvlArray = [];

        if($lvl1 != null){
            $lvl1 = floor($lvl1);
            $lvlArray[] = $lvl1;
        }
        if($lvl2 != null){
            $lvl2 = floor($lvl2);
            $lvlArray[] = $lvl2;
        }
        if($lvl3 != null){
            $lvl3 = floor($lvl3);
            $lvlArray[] = $lvl3;
        }
        if($lvl4 != null){
            $lvl4 = floor($lvl4);
            $lvlArray[] = $lvl4;
        }
        if($lvl5 != null){
            $lvl5 = floor($lvl5);
            $lvlArray[] = $lvl5;
        }
        $qty = sizeof($lvlArray);

        // Toutes les actions qui possèdent des balises style avec guillemets doivent être entourées de double quotes pour aller dans la requête sql (cf la 1ere).
        // La requête SQL doit être entourée de guillemets simples, avec guillemets doubles pour les strings !
        switch($action){
            //ferme et récolte
            case "farm":
                $actionContent .= "Le sol est riche et fertile. Vous avez produit ".$qty." <span style='font-weight:bold;'>".$item."</span> de niveaux (";
                for($i=0; $i<$qty; $i++){
                    $actionContent .= $lvlArray[$i];
                }
                $actionContent .= ').';
            break;
            case "farm-red":
                $actionContent .= "Le sol est rouge et pauvre. Vous avez produit ".$qty." <span style='font-weight:bold;'>".$item."</span> de niveaux (";
                for($i=0; $i<$qty; $i++){
                    $actionContent .= $lvlArray[$i];
                }
                $actionContent .= ').';
            break;
            case "farm-dark":
                $actionContent .= "Le sol est noir. Tout est mort. Vous avez produit 1 <span style='font-weight:bold;'>".$item."</span> de niveaux (";
                for($i=0; $i<$qty; $i++){
                    $actionContent .= $lvlArray[$i];
                }
                $actionContent .= ").";
            break;
            case "harvest-tree":
                $actionContent .= "Vous avez récolté 1 <span style='font-weight:bold; color:goldenrod;'>Bois</span> de niveau ".$lvl1.". (XP Récolte + 20%)";
            break;
            case "harvest-tree1":
                $actionContent .= "Vous avez récolté 1 <span style='font-weight:bold; color:#ddbc02;'>Bois Rare</span> de niveau ".$lvl1.". (XP Récolte + 20%)";
            break;
            case "harvest-orange":
                $actionContent .= "Vous avez trouvé 1 <span style='font-weight:bold; color:orange;'>Orange</span> de niveau ".$lvl1.". (XP Récolte + 20%)";
            break;
            case "harvest-honey":
                $actionContent .= "Vous avez trouvé 1 <span style='font-weight:bold; color:yellow;'>Miel</span> de niveau ".$lvl1.". (XP Récolte + 20%)";
            break;
            case "plant-orange":
                $actionContent .= "Vous avez planté 1 <span style='font-weight:bold; color:orange;'>Orange</span> de niveau ".$lvl1.". (XP Récolte + 20%)";
            break;
            case "plant-honey":
                $actionContent .= "Vous avez planté 1 <span style='font-weight:bold; color:yellow;'>Miel</span> de niveau ".$lvl1.". (XP Récolte + 20%)";
            break;
            case "watering":
                $actionContent .= "Vous avez arrosé vos cultures et nourri vos abeilles.";
            break;
            //fight
            case "atk":
                $actionContent .= "Vous infligez <span style='font-weight:bold; color:green;'>".$dmg."</span> dégâts à l'Ombre.";
            break;
            case "def-shield":
                $actionContent .= "L'Ombre inflige <span style='font-weight:bold; color:red;'>".$dmg."</span> dégâts au <strong>Bouclier</strong>.";
            break;
            case "def-upper":
                $actionContent .= "L'Ombre inflige <span style='font-weight:bold; color:red;''>".$dmg."</span> dégâts à l'<strong>Armure haute</strong>.";
            break;
            case "def-lower":
                $actionContent .= "L'Ombre inflige <span style='font-weight:bold; color:red;'>".$dmg."</span> dégâts à l\'<strong>Armure basse</strong>.";
            break;
            case "def-helmet":
                $actionContent .= "L'Ombre inflige <span style='font-weight:bold; color:red;'>".$dmg."</span> dégâts au <strong>Casque</strong>.";
            break;
            case "def-mask":
                $actionContent .= "L'Ombre inflige <span style='font-weight:bold; color:red;'>".$dmg."</span> dégâts au <strong>Masque</strong>.";
            break;
            case "atk-spear":
                $actionContent .= "Votre <strong>Lance</strong> subit <span style='font-weight:bold; color:red;'>".$dmg."</span> dégâts.";
            break;
            case "foedead":
                $actionContent .= "<strong>Vous avez vaincu une Ombre.</strong> (XP Combat + 20%)";
            break;
            case "dead":
                $actionContent .= "Vous avez péri.";
            break;
            //forge
            case "forge":
                $actionContent .= "Vous avez fabriqué 1 ".$item." de niveau ".$lvl1.". (XP Forgeron + 20%)";
            break;
            //cook
            case "cook":
                $actionContent .= "Vous avez cuisiné 1 ".$item." de niveau ".$lvl1.". (XP Cuisiner + 20%)";
            break;
            //lvl up
            case "lvlup":
                $actionContent .= "<strong>Vous avez gagné 1 niveau (".$item.").</strong>";
            break;
            case "lvlmax":
                $actionContent .= "<span style='font-weight:bold;color:red;'>Vous avez atteint le niveau maximum dans une stat. Ce niveau augmente de 5 chaque jour.</span>";
            break;
            //déplacement et stealth
            case "blocked":
                $actionContent .= "Les Ombres sont trop puissantes ici. Vous êtes <span style='font-weight:bold;color:red;'>Bloqué(e)</span>. Vous pouvez revenir en arrière.";
            break;
            case "stealth":
                $actionContent .= "Les Ombres sont trop puissantes ici, mais vous êtes suffisament discret(e) pour vous <span style='font-weight:bold;color:olive;'>Camoufler</span>.";
            break;
            case "stealth-ok":
                $actionContent .= 'Vous contournez prudemment les Ombres. (XP Explorateur + 10%)';
            break;
            case "explo":
                $actionContent .= 'Vous découvrez une région inexplorée. (XP Explorateur + 20%)';
            break;
            // transport
            case "carry":
                $actionContent .= 'Vous transportez beaucoup de matériel. (XP Ravitailleur + 20%)';
            break;
            // build TODO ?
            case "vote-bld":
                $actionContent .= 'Vous avez voté pour une construction.';
            break;
            case "camp":
                $actionContent .= 'Vous avez construit un camp.';
            break;
            //changement de zone
            //TODO ??
            case "go-outside-city":
                $actionContent .= 'Vous passez les murailles. Vous êtes dehors.';
            break;
            case "go-inside-city":
                $actionContent .= 'Vous passez les murailles. Vous êtes en sécurité à l\'intérieur. Votre <strong>moral</strong> est à son maximum !';
            break;
            case "go-inside-dj":
                $actionContent .= 'Vous entrez dans une caverne sombre.';
            break;
            case "go-outside-dj":
                $actionContent .= 'Vous atteignez la surface.';
            break;
            case "go-inside-abyss":
                $actionContent .= 'Vous entrez dans la crevasse.';
            break;
            case "go-ouside-abyss":
                $actionContent .= 'Vous atteignez la surface.';
            break;
            case "go-inside-hell":
                $actionContent .= 'Vous entrez en Enfer.';
            break;
            case "go-ouside-hell":
                $actionContent .= 'Vous grimpez hors des Enfers.';
            break;
            case "go-inside-dj2":
                $actionContent .= 'Vous atteignez le fond de la caverne.';
            break;
            case "go-ouside-dj2":
                $actionContent .= 'Vous quittez le fond de la caverne.';
            break;
            case "go-inside-camp":
                $actionContent .= 'Vous entrez dans un camp fortifié.';
            break;
            case "go-ouside-camp":
                $actionContent .= 'Vous quittez la sécurité du camp.';
            break;
            //inventaire
            case "equip": //?
                $actionContent .= "Vous équipez un <span style='color:grey;'>".$item."</span> de niveau ".$lvl1;
            break;
            case "give":
                $actionContent .= "Vous donnez toutes vos ressources à la ville, Merci!";
            break;
            case "eat-orange":
                $actionContent .= "Vous mangez une <span style='font-weight:bold;color:orange;'>Orange</span>.";
            break;
            case "eat-honey":
                $actionContent .= "Vous mangez du <span style='font-weight:bold;color:gold;'>Miel</span>.";
            break;
            case "eat-baked-orange":
                $actionContent .= "Vous mangez une <span style='font-weight:bold;color:DarkOrange;'>Tarte aux fruits</span>.";
            break;
            case "eat-baked-honey":
                $actionContent .= "Vous mangez du <span style='font-weight:bold;color:chocolate;'>Miel raffiné</span>.";
            break;
            case "eat-cake":
                $actionContent .= "Vous mangez un <span style='font-weight:bold;color:sienna;'>Gâteau</span>.";
            break;
            //Loot
            case "loot-metal":
                $actionContent .= "Vous avez trouvé un <span style='font-weight:bold;color:silver;'>Métal rare</span> sur l'Ombre vaincue.";
            break;
            // Wave
            case "hitByWave":
                $actionContent .= "Vous êtes resté(e) dehors pendant le passage de la Vague <span style='font-weight:bold;color:red;'>Vous subissez de lourd dégâts</span>.";
            break;

        }

        // Adds data into database
        $err = '';
        $query='INSERT INTO city_action(id_city, id_player, action_date, action_content)
                    VALUES ('.$this->idCity.', '.$this->idPlayer.', "'.$actionDate.'", "'.$actionContent.'")';
        try {
            $this->database->mysql->query($query);
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


        #region (Caching)


        /* (Caching) BUG
        if(isset($_SESSION['player_actions']))
        {
            //$this->log->addLog(sizeof($_SESSION['player_actions']['player_name']), 'database');

            // Reverse arrays values while keeping the keys
            $k = array_keys($_SESSION['player_actions']['action_date']);
            $v = array_values($_SESSION['player_actions']['action_date']);
            
            // Adds one empty key
            $k[] = end($k) + 1;
            
            // Reverse the 3 arrays, push the new value, then reverse back and combine key and array values.
            $rv = array_reverse($v);
            var_dump($rv);
            $rv = array_push($rv, $actionDate);
            $v = array_reverse($rv); // pb $v is int ???
            $_SESSION['player_actions']['action_date'] = array_combine($k, $v);

            $k = array_keys($_SESSION['player_actions']['action_content']);
            $v = array_values($_SESSION['player_actions']['action_content']);
            $k[] = end($k) + 1;
            $rv = array_reverse($v);
            $rv = array_push($rv, $actionDate);
            $v = array_reverse($rv);
            $_SESSION['player_actions']['action_content'] = array_combine($k, $v);

            $k = array_keys($_SESSION['player_actions']['player_name']);
            $v = array_values($_SESSION['player_actions']['player_name']);
            $k[] = end($k) + 1;
            $rv = array_reverse($v);
            $rv = array_push($rv, $actionDate);
            $v = array_reverse($rv);
            $_SESSION['player_actions']['player_name'] = array_combine($k, $v);
            
            //$this->log->addLog(sizeof($_SESSION['player_actions']['player_name']), 'database');
            
            // si plus de 50 lignes : supprimer la dernière ligne
            if(sizeof($_SESSION['player_actions']['action_date']) > 50)
            {
                array_pop($_SESSION['player_actions']['action_date']); //PB : on pop l'indice 49, l'indice 50 se met au début > voir https://stackoverflow.com/questions/14370551/reverse-array-values-while-keeping-keys
                array_pop($_SESSION['player_actions']['action_content']);
                array_pop($_SESSION['player_actions']['player_name']);
            }
            $this->log->addLog(sizeof($_SESSION['player_actions']['player_name']), 'database');
        }
        else
        {
            $_SESSION['player_actions'] = [];
           // $_SESSION['player_actions'] = $newAction;
            $_SESSION['player_actions']['player_name'] = $newAction['player_name'];
            $_SESSION['player_actions']['action_content'] =  $actionContent;
            $_SESSION['player_actions']['action_date'] = $actionDate;
        }
        */
        

        #endregion

    }

}


