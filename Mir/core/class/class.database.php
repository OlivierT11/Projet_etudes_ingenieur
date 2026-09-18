<?php

/**
 * 
 * Class to handle the database calls.
 * 
 */

class Database
{
    private $userName = '';
    private $serverName = '';
    private $password = '';
    private $dbName = '';

    public $mysql; 

    //public $err = 'test';
    
    public function __construct()
    {
        // Aliment properties with constants from tools/config.php
        // $this->serverName = MYSQL_SERVER_MIR;
        // $this->userName = MYSQL_USERNAME_MIR;
        // $this->password = MYSQL_PASSWORD_MIR;
        // $this->dbName = MYSQL_DATABASE_MIR;

        // Connection on test environement
        $this->serverName = MYSQL_SERVER_MIR_GCLOUD;
        $this->userName = MYSQL_USERNAME_MIR_GCLOUD;
        $this->password = MYSQL_PASSWORD_MIR_GCLOUD;
        $this->dbName = MYSQL_DATABASE_MIR_GCLOUD;

        $this->mysql = $this->getMyMysqlConnection();
    }
    // Connects to the SQL database for the main game
    public function getMyMysqlConnection()
    {
        try 
        {
            $PDOstring = 'mysql:host='.$this->serverName.';dbname='.$this->dbName.';charset=utf8';

            $this->mysql = new PDO($PDOstring, $this->userName, $this->password);

            // set the PDO error mode to exception
            $this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            echo 'Erreur dans la requête SQL de ajax_general.php : '. $e->getMessage();
            die();
        }
        return $this->mysql;
    }

    // Connects to the SQL database for the Elkarte forum
    public function getMyElkarteConnection()
    {
        $servername = "127.0.0.1";
        $username = "root";
        $password = "";
    
        try 
        {
            $mysql = new PDO("mysql:host=$servername;dbname=elkarte;charset=utf8", $username, $password);
            // set the PDO error mode to exception
            $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $mysql;
        }
        catch(PDOException $e)
        {
            echo "Connection failed: " . $e->getMessage();
        }
    }

}