<?php

/**
 * 
 * Handles the statistics
 * 
 */

class Statistic
{
    // Sessions
    private $idPlayer = 0;

    // Objects
    private $database;
    private $log;
    private $news;

    public function __construct($database, $log, $news)
    {
        // Objects

        // If the class is called from ajax files, the class files won't be included
        //require_once dirname(__FILE__)."/class.database.php";
        $this->database = $database;

        //require_once dirname(__FILE__)."/class.log.php";
        $this->log = $log;

        $this->news = $news;

        //Variables
        $this->idPlayer = $_SESSION['id_player'];

        // En attendant d'avoir un objet pour les news à envoyer en paramètre
        //require_once dirname(dirname(__FILE__))."/tools/tools.php";
    }

    /**
     * 
     * Updates a player's statistic when he/she performs a particular action.
     * @param string $statistic The name of the statistic (as stored in table 'statistic')
     * @param int $amount The amount to add to the existing statistic.
     * 
     */
    public function updatePlayerStatistic(string $statistic, int $amount)
    {
        // TODO Remplacer id_statistic par le nom de la stat
        $err = '';
        $query = 'UPDATE statistic_local_player slp INNER JOIN statistic s ON slp.id_statistic = s.id_statistic 
                    SET slp.amount = slp.amount + '.$amount.' 
                    WHERE slp.id_player='.$this->idPlayer.' AND s.statistic_name = "'.$statistic.'"';
        try 
        {
            $this->database->mysql->query($query);
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

        // (Caching) Update sessions to match DB
        $_SESSION['player_local_statistics'][$statistic]['amount'] += $amount;

        // Adds a city new when a statistic's threshold is reached.
        if($statistic != 'stealth') // Pas sûr pour stealth.
        {
            $i = $_SESSION['player_local_statistics'][$statistic]['amount'];

            if($i==10 || $i==20 || $i==50){
                $this->news->addNew($statistic, $i, 0);
            }

            // Multiples of 100.
            for($j = 1; $j<=100; $j++){
                if($i == 100 * $j){
                    $this->news->addNew($statistic, $i, 0);
                }
            }
        }

    }

}