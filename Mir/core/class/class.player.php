<?php
class Player {
    public $player_name;
    public $player_title;
    public $player_best_stat;
    public $player_is_master;
    public $player_thumbs;
    
    //arrays
    public $player_inventory;
    public $player_awards;
    public $player_stats;
    public $player_titles;
    
    function __construct($player_ID) {
        getAllPlayerData($player_ID);
        
        getPlayerName();
        getPlayerTitle();
        getPlayerBestStat();
        getPlayerIsMaster();
        getPlayerThumbs();
        
        //arrays
        getPlayerInventory();
        getPlayerAwards();
        getPlayerStats();
        getPlayerTitles();
    }
    
    //general
    function getAllPlayerData($player_ID){
        //myPDOconnection();
        //$query = 
    }
    
    //precise
    function getPlayerName(){
        myPDOconnection();
        $query = 'SELECT player_name from player WHERE player_id = '.$player_ID.'';
        
       // return
    }
    function getPlayerTitle(){
        
    }
    function getPlayerBestStat(){
        
    }
    function getPlayerIsMaster(){
        
    }
    function getPlayerThumbs(){
        
    }
        
    //arrays
    function getPlayerInventory(){
        
    }
    function getPlayerAwards(){
        
    }
    function getPlayerStats(){
        
    }
    function getPlayerTitles(){
        
    }
    
}