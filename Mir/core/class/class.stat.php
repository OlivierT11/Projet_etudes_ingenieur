<?php

/**
 * Handles the player stats (xp, lvl)
 */

 // TODO : retirer les sessions?

class Stat
{
    // Sessions
    private $idPlayer = 0;
    private $maxPossibleLvl;
    private $xpGainedPercent;

    // Returned values
    public $newLvl = 0;
    public $newXp = 0;

    public function __construct($database, $log, $action)
    {
        // Objects
        $this->database = $database;
        $this->log = $log;
        $this->action = $action;

        //Variables
        $this->idPlayer = $_SESSION['id_player'];
    }

    /**
     * 
     * Get the list of player stats.
     * Used on login if the player is inside a city, or when the player joins a city.
     * 
     * @return array $playerStats The array of stats
     * 
     */
    public function getPlayerStats()
    {
        $playerStats = [];
        $statName = '';
        $err = '';
        
        $query='SELECT ps.id_stat, ps.player_stat_lvl, ps.player_stat_xp, s.stat_name
                    FROM player_stat ps INNER JOIN stat s ON s.id_stat = ps.id_stat
                    WHERE ps.id_player='.$this->idPlayer.'
                    ORDER BY ps.player_stat_lvl DESC';
        try 
        {
            $res=$this->database->mysql->query($query);
            if ($res)
            { 
                while($data=$res->fetch(PDO::FETCH_ASSOC))
                { 
                    $statName = $data['stat_name'];
                    $playerStats[$statName]['id_stat'] = $data['id_stat']; 
                    $playerStats[$statName]['player_stat_lvl'] = $data['player_stat_lvl'];
                    $playerStats[$statName]['player_stat_xp'] = $data['player_stat_xp'];
                    $playerStats[$statName]['stat_name'] = $statName;
                }
            }
        } 
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();
            
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            $err = 'Erreur dans la requête SQL getPlayerStats : ' . $e->getMessage() . ' /// ' . $query;

            // (Logging)
            $log->addLog($err, 'error', $this->idPlayer);

            throw new Exception('GetPlayerStatsError');
        }

        return $playerStats;

    }

    /**
     * 
     * Define max invent size base on ravi stat. 30 places de base. +1 place par lvl.
     * @param array $playerStats The array of stats
     * 
     */
    public function defineMaxInventSize(array $playerStats)
    {
        return 30 + $playerStats['supply_stat']['player_stat_lvl']; 
    }

    /**
     * 
     * Define the master stat from the stat list
     * @param array $playerStats The array of stats
     * 
     * @return int &$isMaster True if the player has a master stat
     * @return string &$masterStat The name of the master stat, if exists.
     */
    public function defineMasterStat(array $playerStats, int &$isMaster, string &$masterStat)
    {
        $i=1;
        $bestStatLvl = 0;
        $secondBestStatLvl = 0;
        $bestStatName = '';
        $secondBestStatName = '';

        // Get the 2 best stats. The 2 stats are compared so the previous value must be kept in memory w/ a pseudo-foreach
        foreach ($playerStats as $stat)
        {
            if ($i == 1)
            {
                $bestStatLvl = $stat['player_stat_lvl'];
                $bestStatName = $stat['stat_name'];
            }
            else if ($i == 2)
            {
                $secondBestStatLvl = $stat['player_stat_lvl'];
                $secondBestStatName = $stat['stat_name'];
            }
            else
            {
                if ($bestStatLvl >= 2* $secondBestStatLvl)
                {
                    $isMaster = 1;
                    $masterStat = $bestStatName;
                } 
                else
                {
                    $isMaster = 0;
                    $masterStat = '';
                }

                // Ends the loop after 2 iterations
                break;
            }
            $i++;
        }
    }

    /**
     * 
     * Updates a player's stat when he/she performs an action that gives XP.
     * (Caching) The DB AND the session are updated.
     * @param string $stat The name of the stat (as stored in table 'stat')
     * @param int $xp The amount of experience to add to the stat.
     * 
     */
    public function updatePlayerStat($stat, $xp)
    {
        $this->maxPossibleLvl = $_SESSION['max_possible_lvl'];
        $this->xpGainedPercent = $_SESSION['xpGainPercent'];

        // Check if the daily maximum is reached
        if($_SESSION['player_stat'][$stat]['player_stat_lvl'] >= $this->maxPossibleLvl)
        {
            $this->action->postAction('lvlmax');
            return false;
        }
    
        // Define the XP to add if the max lvl is very high compared to the current lvl.
        if(isset($this->xpGainPercent)){
            $xp += floor($xp*($this->xpGainPercent['Combat']/100));
        }
    
        // Define the stat lvl and xp to add to the DB
        $statXp =  $_SESSION['player_stat'][$stat]['player_stat_xp'];
        $statLvl = $_SESSION['player_stat'][$stat]['player_stat_lvl'];
        $statXp += $xp;
        if ($statXp >= 100){
            $statXp -= 100;
            $statLvl += 1;
            $this->action->postAction('lvlup', null, 'Combat');
        }

        // Update the stat's lvl and xp
        $err = '';
        $query = 'UPDATE player_stat ps INNER JOIN stat s ON ps.id_stat = s.id_stat
                    SET ps.player_stat_xp = '.$statXp.', ps.player_stat_lvl = '.$statLvl.'  
                    WHERE ps.id_player='.$this->idPlayer.' AND s.stat_name = "'.$stat.'"';
        try
        {
            $this->database->mysql->query($query);
        }
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            $err = 'Erreur dans la requête SQL de la fonction updatePlayerStat : '.$e->getMessage();
        }

        // (Logging) Log the error in the 'error' log file (can't be done in catch, or many lines will be logged)
        if($err != '')
            $this->log->addLog($err, 'error', $this->idPlayer);

        // (Caching) Update sessions to match DB
        $_SESSION['player_stat'][$stat]['player_stat_xp']  = $statXp;
        $_SESSION['player_stat'][$stat]['player_stat_lvl'] = $statLvl;
    }
}