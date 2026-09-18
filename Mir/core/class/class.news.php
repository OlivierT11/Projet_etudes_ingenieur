<?php

/**
 * Handle the news
 */

class News
{
    private $idPlayer = 0;
    private $idCity = 0;

    public function __construct($database, $log)
    {
        // Objects
        $this->database = $database;
        $this->log = $log;

        // Variables
        if(isset($_SESSION['id_city']) && $_SESSION['id_city'] != 0)
        {
            $this->idPlayer = $_SESSION['id_player'];
            $this->idCity = $_SESSION['id_city'];
        }
    }

    /**
     * Get the list of news for buidlings only. To use on the "Architecture" screen
     */
    public function getCityNewsBuilding()
    {
        $newsListHTML = '';
        
        $query="SELECT new_date, new_content
                FROM city_new
                WHERE id_city=".$this->idCity." AND new_category='build'
                ORDER BY id_city_new DESC
                LIMIT 20";
	    $newsList=[];
	    try
	    {
	    	$res = $this->database->mysql->query($query);
	    	if($res)
	    	{
	    		while($data=$res->fetch(PDO::FETCH_ASSOC))
	    		{
                    $newsList[]=$data;
	    		}
	    	}
	    }
	    catch (PDOException $e)
	    {
	    	$_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";        
	    	$err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
	    	$this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
	    	die();
	    }

        $newsListHTML = $this->formatNewsList($newsList);

	    return $newsListHTML;
    }

    public function formatNewsList($newsList)
    {
        $newsListHTML='';

        // Build the news table
        foreach ($newsList as $new) 
        {
            $newsListHTML .= '<p>'.$new['new_date'].' : '.$new['new_content'];
        }

        return $newsListHTML;
    }

    /**
     * 
     * Add a new to display inside the city or a camp
     * @param string $type Type of the new, to select its content
     * @param int $n Quantity to display in the new's message, if needed
     * @param bool $is_major Select whether the new appears in the major or minor screen.
     * @param int $idCity Send the id city as parameter in the rare case where session(id_city) does not exists on object initialization (city creation)
     * @param int $idPlayer Send the id player as parameter in the rare case where session(id_player) does not exists on object initialization (city creation)
     * 
     */
    public function addNew(string $type, $n, $is_major, $idCity=null, $idPlayer=null)
    {
	
        $newDate = date("H:i");
        $newContent='';
        $category = '';

        if($idCity != null)
            $this->idCity = $idCity;

        if($idPlayer != null)
            $this->idPlayer = $idPlayer;
    
        // Toutes les actions qui possèdent des balises style avec guillemets doivent être entourées de double quotes pour aller dans la requête sql (cf la 1ere).
        // La requête SQL doit être entourée de guillemets simples, avec guillemets doubles pour les strings !
        switch ($type){
            case 'wood_harvesting':
                $newContent .= "a récolté en tout <span style='font-weight:bold;'>".$n." bois</span>.";
                break;
            case 'wood1_harvesting':
                $newContent .= "a récolté en tout <span style='font-weight:bold;'>".$n." bois rares</span>.";
                break;
            case 'orange_harvesting':
                $newContent .= "a récolté en tout <span style='font-weight:bold;'>".$n." oranges</span> dans la nature.";
                break;
            case 'honey_harvesting':
                $newContent .= "a récolté en tout <span style='font-weight:bold;'>".$n." ruches à miel</span> dans la nature.";
                break;
            case 'metal_looted':
                $newContent .= "a récupéré en tout <span style='font-weight:bold;'>".$n." métaux</span> en tuant des Ombres.";
                break;
            case 'orange_farming':
                $newContent .= "a cultivé en tout <span style='font-weight:bold;'>".$n." oranges</span>.";
                break;
            case 'honey_farming':
                $newContent .= "a produit en tout <span style='font-weight:bold;'>".$n." ruches à miel</span>.";
                break;
            case 'area_discovered':
                $newContent .= "a découvert <span style='font-weight:bold;'>".$n." régions inexplorées</span>.";
                break;
            case 'item_given_to_city':
                $newContent .= "a donné en tout <span style='font-weight:bold;'>".$n." objets à la ville</span>.";
                break;
            case 'foe_killed':
                $newContent .= "a éliminé en tout <span style='font-weight:bold;'>".$n." Ombres</span>.";
                break;
            case 'upper_crafted':
                $newContent .= "a fabriqué en tout <span style='font-weight:bold;'>".$n." armures hautes</span>.";
                break;
            case 'lower_crafted':
                $newContent .= "a fabriqué en tout <span style='font-weight:bold;'>".$n." armures basses</span>.";
                break;
            case 'pike_crafted':
                $newContent .= "a fabriqué en tout <span style='font-weight:bold;'>".$n." lances</span>.";
                break;
            case 'shield_crafted':
                $newContent .= "a fabriqué en tout <span style='font-weight:bold;'>".$n." boucliers</span>.";
                break;
            case 'mask_crafted':
                $newContent .= "a fabriqué en tout <span style='font-weight:bold;'>".$n." masques</span>.";
                break;
            case 'helmet_crafted':
                $newContent .= "a fabriqué en tout <span style='font-weight:bold;'>".$n." casques</span>.";
                break;
            case 'cake_cooked':
                 $newContent .= "a cuisiné en tout <span style='font-weight:bold;'>".$n." gâteaux</span>.";
                break;
             case 'honey_cooked':
                $newContent .= "a cuisiné en tout <span style='font-weight:bold;'>".$n." miels</span>.";
                break;
            case 'orange_cooked':
                $newContent .= "a cuisiné en tout <span style='font-weight:bold;'>".$n." oranges</span>.";
                break;

            // On build
            case 'build':
                $newContent .= "Le bâtiment <span style='font-weight:bold;'>".$n."</span> a été construit.";
                $category = "build";
                break;
            case 'build-not-enough-material':
                $newContent .= "Par manque de ressources rares, aucun bâtiment n'a été construit hier.";
                $category = "build";
                $is_major = 2; // Do not display on main page
                break;
    
            // On city creation
            case 'newCity':
                $newContent .= "Une nouvelle ville a été fondée dans les ruines d'une Citadelle. Bienvenue dans votre nouvelle ville.";
                break;
            case 'newPlayer':
                $newContent .= $n . " tombe du ciel dans un nuage de feu.";
                break;
    
            // On city creation + 1 day
             case 'city-begin':
                $newContent .= "Il n'y a pas suffisament de joueurs au premier jour, les évènements de la ville commenceront demain.";
                break;
    
            // On city leave
            case 'playerLeave':
                $newContent .= $n . " est introuvable, il/elle a visiblement abandonné la Citadelle. Son équipement a été rendu.";
                break;
                
        }
        
        $err = '';
        $query='INSERT INTO city_new(id_city, id_player, new_date, new_content, new_category, is_major)
                    VALUES ('.$this->idCity.', '.$this->idPlayer.', "'.$newDate.'", "'.$newContent.'", "'.$category.'", '.$is_major.')';
        try 
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Une équipe
            a été prévenue. Si le problème persiste, veuillez contacter un administrateur.";
        
            $err = 'Erreur dans la requête SQL de la fonction addNew : '.$e->getMessage() . ' : '. $query;
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);
            
        // (Caching) Add the new to the cache file

    }
}