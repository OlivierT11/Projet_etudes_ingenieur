<?php

class Searchbar
{
    // Classes
    private $database;
    private $log;
    
    // Variables
    private $idCity;
    private $idPlayer;

    public function __construct($database, $log)
    {
        $this->database = $database;
        $this->log = $log;

        $this->idCity = $_SESSION['id_city'];
        $this->idPlayer = $_SESSION['id_player'];
    }

    
}