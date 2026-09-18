<?php 

// Non utilisé, juste pour tests.

require_once dirname(__FILE__)."/core/tools/tools.php";


//map (image) storage in DB
if(isset ($_REQUEST['r'])) { //x,y,r,g,b,a
    
    $x = $_REQUEST['x'];
    $y = $_REQUEST['y'];
    $r = $_REQUEST['r'];
    $g = $_REQUEST['g'];
    $b = $_REQUEST['b'];
    $a = $_REQUEST['a'];
    
    $x = explode(",", $x);
    $y = explode(",", $y);
    $r = explode(",", $r);
    $g = explode(",", $g);
    $b = explode(",", $b);
    $a = explode(",", $a);
    
   // $n = array_slice($x, -1)[0]; //49
    //$m = array_slice($y, -1)[0];
    //echo $n;
    $n = sizeof($x)-1;
    $foo=49;
    $m = 49;

    //convert r,g,b,a from string to int
    for ($i=0; $i<$n; $i++){
        $x[$i]=(int)$x[$i];
        $y[$i]=(int)$y[$i];
        $r[$i]=(int)$r[$i];
        $g[$i]=(int)$g[$i];
        $b[$i]=(int)$b[$i];
        $a[$i]=(int)$a[$i];
    }

    // Décalage d'indices pour avoir (0,0) au centre de la carte

    /*
    Before : 
        (0,0)   ...   (25,0)  ... (49,0) 
         ...
        (0,25)  ...   (25,25) ... (49,25) 
         ...
        (0,49)  ...   (25,49) ... (49,49) 


    After :
        (-25,25)   ...   (0,25)  ... (25,25) 
          ...             
        (-25,0)    ...   (0,0)   ... (25,0) 
          ...
        (-25,-25)  ...   (0,-25) ... (25,-25) 
    */


    $maxCityX = 99; //to have -24 -> 25
    $maxCityY = 100; //to have 25 -> -24
    $newY = floor($maxCityY / 2);
    $compteurPosY = 0;
    // Pour tout x : x - floor(49/2) = x - 24.
    for($i=0;$i<$n;$i++){
        $x[$i] -= floor($maxCityX/2);
    }
    // Pour tout y : toutes les suites de 49 cases prennent le même y. Diminue de 1 à la 49e case.
    for($i=0;$i<$n;$i++){
        $y[$i] = $newY;
        if($compteurPosY == $maxCityY){
            $newY -=1;
            $compteurPosY=0;
        }
        $compteurPosY++; // when it reaches 49, the next Y will have an offset $newY reduced by 1.
    }

    

    // Set area type and gate as a function of area color
    $area_type = [];

    for ($i=0; $i<$n; $i++){
        if ($r[$i]=="182" && $g[$i]=="255" && $b[$i]=="0"){
            $area_type[$i]="field"; //OR $area_type[]="field";
        }
        else if ($r[$i]=="76" && $g[$i]=="255" && $b[$i]=="0"){
            $area_type[$i]="forest_1";
        }
        else if ($r[$i]=="38" && $g[$i]=="127" && $b[$i]=="0"){
            $area_type[$i]="forest_2";
        }
        // For now, the DJ is inside a forest center. Maybe later create different red nuances for DJ in other biomes ?
        else if ($r[$i]=="255" && $g[$i]=="0" && $b[$i]=="0"){
            $area_type[$i]="forest_2";
        }
    }
    
    

    //create random content selon la zone
    for ($i=0; $i<$n; $i++){
        $content[$i]='';
        $contentString = '';
        $is_discovered[$i] = 0;

        /// Plaine arbres 5-20%, rock : 0-5% orange 0-3% honey 0-3%, rare tree 1/10000 or 1 every 100 areas
        if($area_type[$i]=="field"){
            for($j=0; $j<100; $j++){
                $m = rand(1,1000);
                if ($m>=50 && $m<200){
                    $contentString .= 't';
                }
                else if ($m >= 200 && $m<250){
                    $contentString .= 'r';
                }
                else if ($m >= 250 && $m <280){
                    $contentString .= 'o';
                }
                else if ($m >= 280 && $m <310){
                    $contentString .= 'h';
                }
                else if ($m == 999){ //rare tree
                    $contentString .= 'u';
                }
                else {
                    $contentString .= 'n';
                }
            }
        }

            /// Foret arbres 50-70 rock 0-5 orange 0-3 honey 0-3 rare tree 1/1000 or 1/10 areas.
        else if($area_type[$i]=="forest_1"){
            for($j=0; $j<100; $j++){
                $m = rand(1,1000);
                if ($m>=10 && $m<700){
                    $contentString .= 't';
                }
                else if ($m >= 700 && $m <750){
                    $contentString .= 'r';
                }
                else if ($m >= 750 && $m <780){
                    $contentString .= 'o';
                }
                else if ($m >= 780 && $m <810){
                    $contentString .= 'h';
                }
                else if ($m >= 999){ //rare tree
                    $contentString .= 'u';
                }
                else {
                    $contentString .= 'n';
                }
            }
        }

            /// Foret 2 arbres 25-40 arbres rares 40% rock 0-2 orange 0 honey 0
        else if($area_type[$i]=="forest_2"){
            for($j=0; $j<100; $j++){
                $m = rand(1,1000);
                if ($m>=10 && $m<400){
                    $contentString . 't';
                }
                else if ($m>=400 && $m<900){
                    $contentString . 'u';
                }
                else if ($m >= 900 && $m <920){
                    $contentString . 'r';
                }
                else {
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

        $content[$i] = $contentString;
    }
    // populate foes on map
    $foe_hp = [];
    $max_foe = [];
    
    for ($i=0; $i<$n; $i++){

        $foe[$i]=0;
        $foe_hp[$i]='';
        $max_foe[$i]=0;

        //40% empty, 30% 1 foe, 20% 2 foes, 10% 3 foes.
        $m = rand(1,100);
        switch($m){
            case ($m <= 40):
                break;
            case ($m > 40 && $m <= 70):
                $foe[$i] = 1;
                break;
            case ($m > 70 && $m <= 90):
                $foe[$i] = 2;
                break;
            default:
                $foe[$i] = 3;
        }

        $foeNbr = $foe[$i];
        for($j=0; $j<$foeNbr; $j++){
            $foe_hp[$i].='A';
        }
        
        $max_foe[$i]=$foe[$i];
    }


    // Populate Hordes and Titans

    /*
    // Do not add foe on (0,0) (city) and Set (0,0) to discovered
    for ($i=0; $i<$n; $i++){
        if($x[$i]==0 && $y[$i]==0){
            //x and y have the same index as the area is a square
            $foe[$i] = 0;
            $max_foe[$i] = 0;
            $foe_hp[$i] = '';
            $is_discovered[$i] = 1;
        }
    }

    


    //define id_city for the new player
        //get last id_city
    $query='SELECT id_city FROM map ORDER BY id_city DESC LIMIT 1';
    $res = $database->mysql->prepare($query);
    $res->execute();
    $row = $res->fetchAll(PDO::FETCH_OBJ);
    $lastIdCity = $row[0]->id_city;

    // check if the last city is full
    $query='SELECT COUNT(*) as playerNbr FROM map WHERE id_city = '.$lastIdCity;
    $res = $database->mysql->prepare($query);
    $res->execute();
    $row = $res->fetchAll(PDO::FETCH_OBJ);
    $nbrPlayer = $row[0]->playerNbr;

    //if the last city is full, define a new id
    if($nbrPlayer == MAX_CITY_PLAYERS){
        $newIdCity = $lastIdCity + 1;
        createNewCity($newIdCity, $x, $y, $r, $g, $b, $content, $foe, $max_foe, $foe_hp, $area_type, $is_discovered);
    } else if ($nbrPlayer < MAX_CITY_PLAYERS){
        addPlayerToCity($lastIdCity, $x, $y, $r, $g, $b, $content, $foe, $max_foe, $foe_hp, $area_type, $is_discovered);
    } else {
        $_SESSION['error_msg'] = "Erreur à la création d'une ville. Si l'erreur persiste, merci de contacter un administrateur.";
        header("Location: index.php?page=home");
        die();
    }
    */

    $query="
    drop table if exists map;
    create table map
    (
    id int unsigned not null auto_increment primary key,
    id_city int unsigned not null default 1,
    x smallint not null default 0,
    y smallint not null default 0,
    r smallint unsigned not null default 0,
    g smallint unsigned not null default 0,
    b smallint unsigned not null default 0,
    content varchar(101) not null default '',
    ally smallint(5) unsigned not null default 0,
    foe mediumint unsigned not null default 0,
    max_foe mediumint unsigned not null default 0,
    foe_hp varchar(64) not null default '',
    area_type varchar(10) not null default '0',
    is_discovered tinyint(1) not null default 0
    )
    engine=innodb;
    
    insert into map (x, y, r, g, b, content, foe, foe_hp, max_foe, area_type, is_discovered) values ";
    for ($i=0; $i<$n; $i++){
       $query.="(".$x[$i].", ".$y[$i].", ".$r[$i].", ".$g[$i].", ".$b[$i].", '".$content[$i]."', ".$foe[$i].", '".$foe_hp[$i]."', ".$max_foe[$i].", '".$area_type[$i]."', ".$is_discovered[$i]."),";
    }

    //kill the last coma
    $query = substr($query, 0, -1);
    //echo ($query);
    try {
        $res=$database->mysql->query($query);
            if ($res) {
                //echo $query;
                echo "Map database created successfully";
                }
            
    } catch (PDOException $e){
        //echo $e->getMessage();
        echo "Erreur dans la requête ajax_admin.php " . $e->getMessage();
    }


    /*********************** */
    /* Adds stuff to the map */
    /*********************** */

    /*
    // Remove the foes on city entry (0,0)
    $query="UPDATE map SET foe=0, max_foe=0, foe_hp='' 
            WHERE x=0 AND y=0 AND id_city=1";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin : Remove foes on city entry " . $e->getMessage();
    }

    // Select the id_area where the color is RED
    $query="SELECT id FROM map 
            WHERE id_city=1 AND r=255 AND g=0 AND b=0
            LIMIT 1";
    try {
        $idRed = 0;
        $res=$database->mysql->query($query);
        if($res) {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) {
                $idRed = $data['id'];
            }
        }
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin select RED area " . $e->getMessage();
    }

    // Add a DJ entry where the area is RED
    $query="INSERT INTO map_dj(id_city, id_dj, id_dj_area, id_area, dj_x, dj_y) VALUE (1, 1, 1, ".$idRed.", 1, 1)";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin set DJ entry " . $e->getMessage();
    }

    // Create walls on map limits by setting the content value of limit areas to '0'.
    $limitLeftX = -49;
    $limitRightX = 50;
    $limitUpY = 50;
    $limitDownY = -49;
    $query="UPDATE map SET content='' 
            WHERE x=".$limitLeftX." OR x=".$limitRightX." OR y=".$limitUpY." OR y=".$limitDownY;
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin limit map " . $e->getMessage();
    }
    */
/*
         //create new map
         $query="insert into map (id_city, x, y, r, g, b, content, foe, foe_hp, max_foe, area_type, is_discovered) values ";
         for ($i=0; $i<$n; $i++){
         $query.="(".$newIdCity.", ".$x[$i].", ".$y[$i].", ".$r[$i].", ".$g[$i].", ".$b[$i].", '".$content[$i]."', ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."', '".$area_type[$i]."', ".$is_discovered[$i]."),";
         }
         $query = substr($query, 0, -1);
         try {
             $database->mysql->query($query);
             echo "New map added successfully";
         } catch (PDOException $e){
             echo $e->getMessage();
         }
         */
        
    
}



function CreateNewMapTable(){

    $query="
    drop table if exists map;
    create table map
    (
    id int unsigned not null auto_increment primary key,
    id_city int unsigned not null default 1,
    x smallint not null default 0,
    y double not null default 0,
    r smallint unsigned not null default 0,
    g smallint unsigned not null default 0,
    b smallint unsigned not null default 0,
    a smallint unsigned not null default 0,
    content varchar(101) not null default '',
    ally smallint(5) unsigned not null default 0,
    foe mediumint unsigned not null default 0,
    max_foe mediumint unsigned not null default 0,
    foe_hp varchar(64) not null default '',
    area_type varchar(10) not null default '0',
    is_discovered tinyint(1) not null default 0
    )
    engine=innodb;
    
    insert into map (x, y, r, g, b, a, content, foe, foe_hp, max_foe, area_type, is_discovered) values ";
    for ($i=0; $i<$n; $i++){
       $query.="(".$x[$i].", ".$y[$i].", ".$r[$i].", ".$g[$i].", ".$b[$i].", ".$a[$i].", '".$content[$i]."', ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."', '".$area_type[$i]."', ".$is_discovered[$i]."),";
    }

    //kill the last coma
    $query = substr($query, 0, -1);
    //echo ($query);
    try {
        $res=$database->mysql->query($query);
            if ($res) {
                echo "Map database created successfully";
                }
            
    } catch (PDOException $e){
        //echo $e->getMessage();
        echo "Erreur dans la requête ajax_admin.php";
    }
}

//maze storage in DB
if(isset ($_REQUEST['up'])) {
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";

    $mysql = new PDO("mysql:host=$servername;dbname=demo", $username, $password);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $x = $_REQUEST['x'];
    $y = $_REQUEST['y'];
    $up = $_REQUEST['up'];
    $right = $_REQUEST['right'];
    $down = $_REQUEST['down'];
    $left = $_REQUEST['left'];

    echo $x . ' //// ' . $y . ' ///// ' . $up .' /// ' . $left . ' /// ' .$down . ' /// ' . $right;
    
    $x = explode(",", $x);
    $y = explode(",", $y);
    $up = explode(",", $up);
    $right = explode(",", $right);
    $down = explode(",", $down);
    $left = explode(",", $left);
    
    $n = sizeof($x)-1;

    echo ' /// ';
    var_dump($right);
    

    /*
    //add common walls for adjacent areas
    //$x et $y commencent à l'indice 0,0 (en bas à gauche) et se finissent à (19,19)
    for($i=0; $i<$n; $i++){ //pour chaque case
        if($up[$i]==1){ //si mur en haut
            if ($y[$i]+1 <= 19){ //si case en haut existe
                $down[$i-20]=1; //mettre un mur en bas de la case en haut
            }
        }
        if($down[$i]==1){
            if ($y[$i]-1 >= 0){
                $up[$i+20]=1;
            }
        }
        if($left[$i]==1){
            if ($x[$i]-1 >= 0){
                $right[$i-1]=1;
            }
        }
        if($right[$i]==1){
            if ($x[$i]+1 <= 19){
                $left[$i+1]=1;
            }
        }        
    }*/

    /*
    for($i=0; $i<$n; $i++){ //pour chaque case
        if($up[$i]==1){ //si mur en haut
            if (isset($down[$i - 20])){ //si case en haut existe
                $down[$i-20]=1; //mettre un mur en bas de la case en haut
            } else {
                //ajouter une case ?
            }
        }
        if($down[$i]==1){
            if (isset($up[$i + 20])){
                $up[$i+20]=1;
            }
        }
        if($left[$i]==1){
            if (isset($right[$i-1])){
                $right[$i-1]=1;
            }
        }
        if($right[$i]==1){
            if (isset($left[$i+1])){
                $left[$i+1]=1;
            }
        }        
    }
*/

    // !!!
    //adds empty areas around the maze with walls to deal w/ the map limit problem. So the real size is 21*21
    // -> Plus compliqué, non nécessaire. Pour l'instant, ne pas charger les 9 cases si elles n'existent pas. De toutes façons on ne mettra pas les monstres sur
    // les side map.

    // Move the center from top-right to middle
    /*
    Before : 
        (0,0)   ...   (25,0)  ... (49,0) 
         ...
        (0,25)  ...   (25,25) ... (49,25) 
         ...
        (0,49)  ...   (25,49) ... (49,49) 


    After :
        (-25,25)   ...   (0,25)  ... (25,25) 
          ...             
        (-25,0)    ...   (0,0)   ... (25,0) 
          ...
        (-25,-25)  ...   (0,-25) ... (25,-25) 
    */

    $maxMazeX = 19; //to have -19 -> 20
    $maxMazeY = 20; //to have 20 -> -19
    $newY = floor($maxMazeY / 2);
    $compteurPosY = 0;
    // Pour tout x : x - floor(20/2) = x - 10.
    for($i=0;$i<$n;$i++){
        $x[$i] -= floor($maxMazeX/2);
    }
    // Pour tout y : toutes les suites de 20 cases prennent le même y. Diminue de 1 à la 20e case.
    for($i=0;$i<$n;$i++){
        $y[$i] = $newY;
        if($compteurPosY == $maxMazeY-1){ // the -1 is because $maxmazeY is 20 and Y goes from 0 to 19.
            $newY -=1;
            $compteurPosY=0;
        }
        $compteurPosY++; // when it reaches 20, the next Y will have an offset $newY reduced by 1.
    }

    // populate foes in dj, 10 foes everywhere
    $foe_hp = [];
    $max_foe = [];
    
    for ($i=0; $i<$n; $i++){

        $foe[$i]=0;
        $foe_hp[$i]='';
        $max_foe[$i]=0;

        //10 foes everywhere
        $foe[$i] = 10;
        
        $foeNbr = $foe[$i];
        for($j=0; $j<$foeNbr; $j++){
            $foe_hp[$i].='A';
        }
        
        $max_foe[$i]=$foe[$i];
    }

    
    //create dj in table
    $query="
    drop table if exists dj;
    create table dj
    (
    id_dj_area int(10) unsigned not null auto_increment primary key,
    id_city int(10) unsigned not null default 1,
    id_dj int(10) unsigned not null default 1,
    dj_x smallint(5) not null default 0,
    dj_y smallint(5) not null default 0,
    dj_u boolean not null default 0,
    dj_r boolean not null default 0,
    dj_d boolean not null default 0,
    dj_l boolean not null default 0,
    dj_content varchar(101) not null default '',
    dj_ally smallint(5) unsigned not null default 0,
    dj_foe mediumint(8) unsigned not null default 1,
    dj_max_foe mediumint(8) unsigned not null default 1,
    dj_foe_hp varchar(30) not null default '',
    is_discovered tinyint unsigned not null default 1
    )
    engine=innodb;
    
    insert into dj(dj_x, dj_y, dj_u, dj_r, dj_d, dj_l, dj_foe, dj_max_foe, dj_foe_hp) values ";
    for ($i=0; $i<$n; $i++){
        $query.="(".$x[$i].", ".$y[$i].", ".$up[$i].", ".$right[$i].",".$down[$i].", ".$left[$i].", ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."'),";
    }
    //kill the last coma
    $query = substr($query, 0, -1);
    
    try {
        $res=$database->mysql->query($query);
            if ($res) {
                //echo $query;
                }
            
    } catch (PDOException $e){
        echo $e->getMessage();
    }

    // Define the DJ exit and DJ2 entry.
    $abyssEntryX = 1;
    $abyssEntryY = 1;
    for ($i=0; $i<$n; $i++){
        if ($r[$i]=="255" && $g[$i]=="0" && $b[$i]=="0"){
            $hellEntryX = $x[$i];
            $hellEntryY = $y[$i];
        }
    }
    // TODO :
    $newIdDj = $newIdCity = 2; // 1 dj par ville pour l'instant. Après il faudra une table avec id_dj en clé primaire pour avoir lastInsertId.
    // set the dj entry.
    $query="UPDATE map_dj SET id_dj=".$newIdDj.", dj_x=".$djEntryX.", dj_y=".$djEntryY." 
            WHERE id_city=1 
            LIMIT 1";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin : Remove foes on city entry " . $e->getMessage();
    }
    // set the dj exit to dj2
    $query="INSERT INTO dj_dj2(id_dj, id_dj2, dj2_x, dj2_y) VALUE (".$newIdDj.", ".newIdDj.", ".$djEntryDj2X.", ".$djEntryDj2Y.")";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin set DJ entry " . $e->getMessage();
    }

    // Remove the foes on dj entry
    $query="UPDATE map SET foe=0, max_foe=0, foe_hp='' 
            WHERE x=0 AND y=0 AND id_city=1";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin : Remove foes on city entry " . $e->getMessage();
    }

    /* UNUSED YET
    // Create walls on map limits by setting the content value of limit areas to ''.
    $limitLeftX = -20;
    $limitRightX = 21;
    $limitUpY = 21;
    $limitDownY = -20;
    $query="UPDATE dj SET content='0' 
            WHERE id_dj=".$newIdDj." AND (dj_x=".$limitLeftX." OR dj_x=".$limitRightX." OR dj_y=".$limitUpY." OR dj_y=".$limitDownY.")";
    try {
        $res=$database->mysql->query($query);
    } catch (PDOException $e){
        echo "Erreur dans la requête ajax_admin limit map " . $e->getMessage();
    }
    */

}
  


//abyss (image) storage in DB
// Difference with map : no position switch. The cordinates are not shown to the player in the abysses.
if(isset ($_REQUEST['rAbyss'])) {
    $servername = "127.0.0.1";
    $username = "root";
    $password = "";
    
    $mysql = new PDO("mysql:host=$servername;dbname=demo", $username, $password);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $x = $_REQUEST['xAbyss'];
    $y = $_REQUEST['yAbyss'];
    $r = $_REQUEST['rAbyss'];
    $g = $_REQUEST['gAbyss'];
    $b = $_REQUEST['bAbyss'];
    //$a = $_REQUEST['aAbyss'];
    
    $x = explode(",", $x);
    $y = explode(",", $y);
    $r = explode(",", $r);
    $g = explode(",", $g);
    $b = explode(",", $b);
    //$a = explode(",", $a);
    
    $n = sizeof($x)-1;

    // Change indexes
    $maxAbyssX = 19; //to have -19 -> 20
    $newY = floor($maxAbyssX / 2);
    $compteurPosY = 0;
    // Pour tout x : x - floor(20/2) = x - 10.
    for($i=0;$i<$n;$i++){
        $x[$i] -= floor($maxAbyssX/2);
    }

    // populate foes in abyss, 20 foes everywhere
    $foe_hp = [];
    $max_foe = [];
    
    for ($i=0; $i<$n; $i++){

        $foe[$i]=0;
        $foe_hp[$i]='';
        $max_foe[$i]=0;

        //10 foes everywhere
        $foe[$i] = 20;
        
        $foeNbr = $foe[$i];
        for($j=0; $j<$foeNbr; $j++){
            $foe_hp[$i].='A';
        }
        
        $max_foe[$i]=$foe[$i];
    }



    $query="
    drop table if exists abyss;
    create table abyss
    (
    id_abyss_area int unsigned not null auto_increment primary key,
    id_abyss int unsigned not null default 1,
    abyss_x smallint not null default 0,
    abyss_y smallint not null default 0,
    abyss_r smallint unsigned not null default 0,
    abyss_g smallint unsigned not null default 0,
    abyss_b smallint unsigned not null default 0,
    abyss_content varchar(1) default '',
    abyss_ally smallint(5) unsigned not null default 0,
    abyss_foe mediumint unsigned not null default 20,
    abyss_max_foe mediumint unsigned not null default 20,
    abyss_foe_hp text,
    is_discovered tinyint unsigned not null default 1
    )
    engine=innodb;
    insert into abyss(abyss_x, abyss_y, abyss_r, abyss_g, abyss_b, abyss_foe, abyss_max_foe, abyss_foe_hp) values ";
    for ($i=0; $i<$n; $i++){
        $query.="(".$x[$i].", ".$y[$i].", ".$r[$i].", ".$g[$i].", ".$b[$i].", ".$foe[$i].", ".$max_foe[$i].", '".$foe_hp[$i]."'),";
    }
    //kill the last coma
    $query = substr($query, 0, -1);
    try {
        $res=$database->mysql->query($query);
            if ($res) {
                echo "Abyss database created successfully";
                }
            
    } catch (PDOException $e){
        echo $e->getMessage() . "Erreur dans la requête abyss insertion";
    }

    /*

    //Définir l'entrée de l'abysse.
    // TODO amel : case de couleur spéciale pour l'entrée.
    $newIdCity = 1;
    $abyss_x = 1;
    $abyss_y=1;

    $query="INSERT INTO abyss_city(id_city,id_abyss,abyss_x,abyss_y) VALUE(".$newIdCity.", ".$newIdCity.", ".$abyss_x.", ".$abyss_y.")";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();

    //Définir l'entrée de l'enfer. C'est la premiere case rouge.
    $query="SELECT abyss_x, abyss_y 
                FROM abyss 
                WHERE id_abyss=".$newIdCity." AND abyss_r = 255 AND abyss_g = 0 AND abyss_b = 0
                ORDER BY abyss_y DESC, abyss_x ASC
                LIMIT 1";
    $sth = $$database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();
    $row = $sth->fetchAll(PDO::FETCH_OBJ);
    $hell_x = $row[0]->abyss_x;
    $hell_y = $row[0]->abyss_y;

    $query="INSERT INTO abyss_hell(id_abyss,id_hell,abyss_x,abyss_y) VALUE(".$newIdCity.", ".$newIdCity.", ".$hell_x.", ".$hell_y.")";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $sth->execute();

    */

}