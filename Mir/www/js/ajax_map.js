/*

AJAX code relative to the game map (minimap, big map etc).
No DJ or Abyss (maybe?)

*/

if (document.getElementById("map")){

    $(document).ready(function() {
       
        loadMap("map");
        
        //checkMapLimitSides(); -> a big enough map prevents ppl from reaching the sides
        //checkMapLimitCorners();
        
        //loadMiniMap(); -> now AJAX
        //loadBigMap();
        
        getPosX();
        getPosY();

        // Add direction functions to arrows (moveUp() etc). Added after the map loading to prevent clicking quickly while the map is unloaded and bypassing walls.
        addDirectionChangeFunctionsOnArrows();
        
    });
} 
else if(document.getElementById("dj")){

    $(document).ready(function() {
        
        loadMap("dj");
        
        //checkMapLimitSides(); -> walls
        //checkMapLimitCorners();
        
        getPosX();
        getPosY();

        // Add direction functions to arrows (moveUp() etc). Added now to prevent clicking quickly while the map is unloaded and bypassing walls.
        addDirectionChangeFunctionsOnArrows();
        
    });
} 
else if(document.getElementById("abyss")){ //ne s'exécute que si on est dehors. Pb : le script est tjr importé et lu. Amèl : modifier vi_end.php.

    $(document).ready(function() {
        
        loadMap("abyss");
        
        //checkMapLimitSides(); -> no need because we can't go on white areas.
        //checkMapLimitCorners();
        
        getPosX();
        getPosY();

        // Add direction functions to arrows (moveUp() etc). Added now to prevent clicking quickly while the map is unloaded and bypassing walls.
        addDirectionChangeFunctionsOnArrows();
        
    });
}
else if(document.getElementById("hell")){ //ne s'exécute que si on est dehors. Pb : le script est tjr importé et lu. Amèl : modifier vi_end.php.

    $(document).ready(function() {
        
        loadMap("hell");
        
        //checkMapLimitSides(); -> now merged w/ area.class
        //checkMapLimitCorners();
        
        getPosX();
        getPosY();

        // Add direction functions to arrows (moveUp() etc). Added now to prevent clicking quickly while the map is unloaded and bypassing walls.
        addDirectionChangeFunctionsOnArrows();
        
    });
}
else if(document.getElementById("dj2")){ //ne s'exécute que si on est dehors. Pb : le script est tjr importé et lu. Amèl : modifier vi_end.php.

    $(document).ready(function() {
        
        loadMap("dj2");
        
        getPosX();
        getPosY();

        // Add direction functions to arrows (moveUp() etc). Added now to prevent clicking quickly while the map is unloaded and bypassing walls.
        addDirectionChangeFunctionsOnArrows();
        
    });
}
else {
    //Charge une autre page
}

// Map area functions (biome, dungeon, abyss)
function loadMap($area)
{
    // Select the map to load
    if ($area == "map"){
        var str = "loadMap";
        var canvas = document.getElementById("map");
     }
     else if ($area == "dj"){
        var str = "loadDj";
        var canvas = document.getElementById("dj");
     }
     else if ($area == "abyss"){
        var str = "loadAbyss";
        var canvas = document.getElementById("abyss");
     } 
     else if ($area == "hell"){
        var str = "loadHell";
        var canvas = document.getElementById("hell");
     }
     else if ($area == "dj2"){
        var str = "loadDj2";
        var canvas = document.getElementById("dj2");
     }
     else {alert("error");}

     var ctx = canvas.getContext("2d");
     
     var tree = new Image();
     tree.src = 'www/img/tree.png';
     var citadel = new Image();
     citadel.src = 'www/img/citadel.png';
     var cave = new Image();
     cave.src = 'www/img/cave.png';
     var camp = new Image();
     camp.src = 'www/img/camp.png';
     var cavecamp = new Image();
     cavecamp.src = 'www/img/cave-camp.png';
     var raretree = new Image();
     raretree.src = 'www/img/tree-rare.png';
     var souche = new Image();
     souche.src = 'www/img/souche.png';
     var foe = new Image();
     foe.src = 'www/img/foe-spider.png';
     var ally = new Image();
     ally.src = 'www/img/ally.png';
     var honey = new Image();
     honey.src = 'www/img/hive.png';
     var orange = new Image();
     orange.src = 'www/img/orange-tree.png';
     var stone = new Image();
     stone.src = 'www/img/stone.png';

     stone.onload = function () { //Il faut attendre que l'image soit chargée pour la mettre dans le canvas
     
            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_outside.php",
                data: {param: str}, 
                success: function (response) {
                    
                    if(response == "error")
                    {
                        alert("Une erreur est survenue, merci de recharger la page.");
                        location.reload();
                    }
            
                    //alert(contentArea);
                    var contentAreasJSON = JSON.parse(response);
                    
                    //display current area
                    var contentAreas = contentAreasJSON.content;
                    var contentAreasFoe = contentAreasJSON.nbr_foe;
                    var contentAreasAlly = contentAreasJSON.nbr_ally;
                    var contentAreasX = contentAreasJSON.x;
                    var contentAreasY = contentAreasJSON.y;

                    // If is_discovered = 0, add an Action message (for real time, else the user must reload the page to get the action)
                    var is_discovered = contentAreasJSON.is_discovered[4];
                    if(is_discovered == 0){
                        var newAction = document.createElement('LI');
                        newAction.innerHTML = '<p>Vous découvrez une région inexplorée. (XP Explorateur + 20%)</p>';
                        var newWrapper = document.getElementById('actions-wrapper-for-map').children[0]; //ul
                        var firstNew = document.getElementById('actions-wrapper-for-map').children[0].children[1]; //div > ul > li. Skip title node [0].
                        newWrapper.insertBefore(newAction, firstNew);
                    }


                    // check if the area is a cave entry (dj, dj2, hell) If yes, display the cave image.
                    //var is_cave_entry = contentAreasJSON.is_cave_entry[4];

                    // check if the area is a cave exit (dj, dj2, abyss). If yes, display the cave image.
                    //var is_cave_exit = contentAreasJSON.is_cave_exit[4];

                    // check if the area is a camp (map, dj, dj2, abyss, hell). If yes, display the camp image.
                    //var is_camp = false;
                    //if(contentAreasJSON.id_camp[4]){
                    //    is_camp=true;
                    //}

                    // if the area is both an entry or an exit and a camp, display the combined images
                    /*var is_cave_camp = false;
                    if(is_camp && (is_cave_entry || is_cave_exit)){
                        is_camp = false;
                        is_cave_entry = false;
                        is_cave_exit = false;
                        is_cave_camp = true;
                    }*/

                    /*
                    if (contentAreasJSON.id_map_exit){
                        //var id_map_exit = contentAreasJSON.id_map_exit; //1 seule valeur renvoyée
                    }
                    //if the current area is a dungeon entrance / exit
                    else {
                        if(document.getElementById('btn-go-outside-dj')){
                            document.getElementById('btn-go-outside-dj').parentNode.style.display='none';
                        }
                    }
                    */
                   
                    if(contentAreasFoe.length == 9){ //normal case, 8 areas around the player
                        var contentArea = contentAreas[4];
                        var contentAreaFoe = contentAreasFoe[4];
                        var contentAreaAlly = contentAreasAlly[4];
                    } else { //Used only in DJ. Amel : add 1 area around DJ
                        //Si on est en bordure de map, area.class ne renvoie qu'un array à 1 dimension (la case en cours)
                        var contentArea = contentAreas[0];
                        var contentAreaFoe = contentAreasFoe[0];
                        var contentAreaAlly = contentAreasAlly[0];
                    }

                     //Display allies number on top of the map
                     if(document.getElementById("displayAllyNbr")){
                        document.getElementById("displayAllyNbr").innerHTML = contentAreaAlly;
                    }
                    //Display foes number on top of the map
                    if(document.getElementById("displayShadowNbr")){
                        document.getElementById("displayShadowNbr").innerHTML = contentAreaFoe;
                    }
                    
                    if ($area == "map"){
                        document.getElementById("map").innerHTML = contentArea;


                        // check if the area is the city entry. If yes, display the city image.
                        var is_city = false;
                        if(contentAreasJSON.is_city == true){
                            is_city=true;
                        }

                        //check if the area is a DJ entry
                        var is_cave_entry = false;
                        if(contentAreasJSON.is_cave_entry == true){
                            is_cave_entry=true;
                        }

                        //check if the area has a camp
                        var is_camp = false;
                        if(contentAreasJSON.is_camp == true){
                            is_camp=true;
                        }

                        //get foe stats
                        //var distanceFromCity = Math.floor( Math.sqrt( Math.pow(contentAreaY,2)+ Math.pow(contentAreaX, 2) ) );
                        //var foeStr = distanceFromCity;
                        //var foeDef = distanceFromCity;

                        //get biome type
                        if(contentAreasFoe.length == 9){ //normal case, 8 areas around the player
                            var r = contentAreasJSON.r[4];
                            var g = contentAreasJSON.g[4];
                            var b = contentAreasJSON.b[4];
                        } else {
                            //Si on est en bordure de map, area.class ne renvoie qu'un array à 1 dimension (la case en cours)
                            var r = contentAreasJSON.r[0];
                            var g = contentAreasJSON.g[0];
                            var b = contentAreasJSON.b[0];
                        }

                        biomeType = "";
                        if( (r==182) && (g=255) && (b=0) ){
                            biomeType = "P"; //plains
                        } else if ((r==76) && (g=255) && (b=0) ){
                            biomeType = "F1";
                        } else if ((r==0) && (g=0) && (b=0) ){
                            biomeType = "F3";
                        }

                        //display side-map buttons

                        // the data for 8 areas are received (0-7). The current area is 4.

                        /* ORDER OF APPEARANCE

                        x   y
                        -1  1
                        0   1
                        1   1

                        -1  0
                        0   0
                        1   0

                        -1  -1
                        0   -1
                        1   -1

                        The ids in the json array of the 9 positions around the current area :
                        0   1   2
                        3   4   5
                        6   7   8

                        */

                        // display the foe numbers on side arrows
                        var contentAreaNorthFoe = contentAreasFoe[1];
                        document.getElementById("foe-nbr-up").innerHTML = contentAreaNorthFoe;  

                        var contentAreaEastFoe = contentAreasFoe[5];
                        document.getElementById("foe-nbr-right").innerHTML = contentAreaEastFoe;  

                        var contentAreaSouthFoe = contentAreasFoe[7];
                        document.getElementById("foe-nbr-down").innerHTML = contentAreaSouthFoe;  

                        var contentAreaWestFoe = contentAreasFoe[3];
                        document.getElementById("foe-nbr-left").innerHTML = contentAreaWestFoe;  

                        var contentAreaNorthWestFoe = contentAreasFoe[0];
                        document.getElementById("foe-nbr-up-left").innerHTML = contentAreaNorthWestFoe;  

                        var contentAreaNorthEastFoe = contentAreasFoe[2];
                        document.getElementById("foe-nbr-up-right").innerHTML = contentAreaNorthEastFoe;  

                        var contentAreaSouthEastFoe = contentAreasFoe[8];
                        document.getElementById("foe-nbr-down-right").innerHTML = contentAreaSouthEastFoe;  

                        var contentAreaSouthWestFoe = contentAreasFoe[6];
                        document.getElementById("foe-nbr-down-left").innerHTML = contentAreaSouthWestFoe; 

                        //displays the ally number on side arrows
                        var contentAreaNorthAlly = contentAreasAlly[1];
                        document.getElementById("ally-nbr-up").innerHTML = contentAreaNorthAlly;  

                        var contentAreaEastAlly = contentAreasAlly[5];
                        document.getElementById("ally-nbr-right").innerHTML = contentAreaEastAlly;  

                        var contentAreaSouthAlly = contentAreasAlly[7];
                        document.getElementById("ally-nbr-down").innerHTML = contentAreaSouthAlly;  

                        var contentAreaWestAlly = contentAreasAlly[3];
                        document.getElementById("ally-nbr-left").innerHTML = contentAreaWestAlly;  

                        var contentAreaNorthWestAlly = contentAreasAlly[0];
                        document.getElementById("ally-nbr-up-left").innerHTML = contentAreaNorthWestAlly;  

                        var contentAreaNorthEastAlly = contentAreasAlly[2];
                        document.getElementById("ally-nbr-up-right").innerHTML = contentAreaNorthEastAlly;  

                        var contentAreaSouthEastAlly = contentAreasAlly[8];
                        document.getElementById("ally-nbr-down-right").innerHTML = contentAreaSouthEastAlly;  

                        var contentAreaSouthWestAlly = contentAreasAlly[6];
                        document.getElementById("ally-nbr-down-left").innerHTML = contentAreaSouthWestAlly; 

                        // display side buttons (arrows, foe Nbr, ally number). Display walls if map limit
                        //north
                        if(contentAreas[1] == '0'){ //if out of the map
                            document.getElementById("up-arrow").style.display="none";
                            document.getElementById("up-wall").style.display="block";
                        } 

                        //east
                        if(contentAreas[5] == '0'){
                            document.getElementById("right-arrow").style.display="none";
                            document.getElementById("right-wall").style.display="block";
                        } 

                        //south
                        if(contentAreas[7] == '0'){
                            document.getElementById("down-arrow").style.display="none";
                            document.getElementById("down-wall").style.display="block";
                        } 

                        //west
                        if(contentAreas[3] === '0'){
                            document.getElementById("left-arrow").style.display="none";
                            document.getElementById("left-wall").style.display="block";
                        } 
                        
                        //north west
                        if(contentAreas[0] == '0'){
                            document.getElementById("up-left-arrow").style.display="none";
                            document.getElementById("up-left-wall").style.display="block";
                        } 

                        //north east
                        if(contentAreas[2] == '0'){
                            document.getElementById("up-right-arrow").style.display="none";
                            document.getElementById("up-right-wall").style.display="block";
                        } 

                        //south east
                        if(contentAreas[8] == '0'){
                            document.getElementById("down-right-arrow").style.display="none";
                            document.getElementById("down-right-wall").style.display="block";
                        } 

                        //soutn west
                        if(contentAreas[6] == '0'){
                            document.getElementById("down-left-arrow").style.display="none";
                            document.getElementById("down-left-wall").style.display="block";
                        } 

                        checkIfPlayerBlocked(contentAreasJSON.nbr_foe[4]);

                    }

                    else if ($area == "dj"){
                        document.getElementById("dj").innerHTML = contentArea;

                        //get foe stats
                        //var distanceFromCity = Math.floor( Math.sqrt( Math.pow(contentAreaY,2)+ Math.pow(contentAreaX, 2) ) );
                        //var foeStr = distanceFromCity;
                        //var foeDef = distanceFromCity;

                        //check if the area is the DJ exit
                        var is_cave_exit = false;
                        if(contentAreasJSON.is_cave_exit == true){
                            is_cave_exit=true;
                        }
  
                        //check if the area is the dj2 entry
                        var is_dj2_entry = false;
                        if(contentAreasJSON.is_dj2_entry == true){
                            is_dj2_entry=true;
                        }

                        //get biome type
                        if(contentAreasFoe.length == 9){ //normal case, 8 areas around the player
                            var u = contentAreasJSON.up[4];
                            var r = contentAreasJSON.right[4];
                            var d = contentAreasJSON.down[4];
                            var l = contentAreasJSON.left[4];
                        } else {
                            //Si on est en bordure de map, area.class ne renvoie qu'un array à 1 dimension (la case en cours) (buggé)
                            var u = contentAreasJSON.up[0];
                            var r = contentAreasJSON.right[0];
                            var d = contentAreasJSON.down[0];
                            var l = contentAreasJSON.left[0];
                        }
                        

                        // display side map and div content (arrows, walls, foe Nbr, ally number)
                        if(contentAreasFoe.length == 9){
                            if(u == 0){ //no wall north
                                var contentAreaNorthFoe = contentAreasFoe[1];
                                document.getElementById("up-arrow").innerHTML = contentAreaNorthFoe; 
                            } else {
                                document.getElementById("up-arrow").style.display="none";
                                document.getElementById("up-wall").style.display="block";
                            }
                            if(r == 0){
                                var contentAreaEastFoe = contentAreasFoe[5];
                                document.getElementById("right-arrow").innerHTML = contentAreaEastFoe; 
                            } else {
                                document.getElementById("right-arrow").style.display="none";
                                document.getElementById("right-wall").style.display="block"; 
                            }
                            if(d == 0){
                                var contentAreaSouthFoe = contentAreasFoe[7];
                                document.getElementById("down-arrow").innerHTML = contentAreaSouthFoe;  
                            } else {
                                document.getElementById("down-arrow").style.display="none";
                                document.getElementById("down-wall").style.display="block";
                            }    
                            if(l == 0){
                                var contentAreaWestFoe = contentAreasFoe[3];
                                document.getElementById("left-arrow").innerHTML = contentAreaWestFoe;
                            } else {
                                document.getElementById("left-arrow").style.display="none";
                                document.getElementById("left-wall").style.display="block";
                            }
                            checkIfPlayerBlocked(contentAreasJSON.nbr_foe[4]);   

                        } else { //dj border or corner

                            if(u == 0){ //no wall north
                                //var contentAreaNorthFoe = contentAreasFoe[1];
                                //document.getElementById("up-arrow").innerHTML = contentAreaNorthFoe; 
                            } else {
                                document.getElementById("up-arrow").style.display="none";
                                document.getElementById("up-wall").style.display="block";
                            }
                            if(r == 0){
                                //var contentAreaEastFoe = contentAreasFoe[5];
                                //document.getElementById("right-arrow").innerHTML = contentAreaEastFoe; 
                            } else {
                                document.getElementById("right-arrow").style.display="none";
                                document.getElementById("right-wall").style.display="block"; 
                            }
                            if(d == 0){
                                //var contentAreaSouthFoe = contentAreasFoe[7];
                                //document.getElementById("down-arrow").innerHTML = contentAreaSouthFoe;  
                            } else {
                                document.getElementById("down-arrow").style.display="none";
                                document.getElementById("down-wall").style.display="block";
                            }    
                            if(l == 0){
                                //var contentAreaWestFoe = contentAreasFoe[3];
                                //document.getElementById("left-arrow").innerHTML = contentAreaWestFoe;
                            } else {
                                document.getElementById("left-arrow").style.display="none";
                                document.getElementById("left-wall").style.display="block";
                            }
                            checkIfPlayerBlocked(contentAreasJSON.nbr_foe[0]);   
                        } 
                        
                        //hide other direction buttons
                        document.getElementById("up-left-arrow").style.display="none"; 
                        document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";

                        

                    }

                    else if ($area == "abyss"){
                        document.getElementById("abyss").innerHTML = contentArea;

                        //get foe stats
                        //var distanceFromCity = Math.floor( Math.sqrt( Math.pow(contentAreaY,2)+ Math.pow(contentAreaX, 2) ) );
                        //var foeStr = distanceFromCity;
                        //var foeDef = distanceFromCity;

                        //get biome type
                        var r = contentAreasJSON.r;
                        var g = contentAreasJSON.g;
                        var b = contentAreasJSON.b;
                        //var a = contentAreasJSON.a;
                        
                        console.log(r);
                        console.log(g);
                        console.log(b);

                        //check if the area is the abyss exit
                        var is_abyss_exit = false;
                        if(contentAreasJSON.is_abyss_exit == true){
                            is_abyss_exit=true;
                        }
                        
                        //check if the area is the hell entry
                        var is_hell_entry = false;
                        if(contentAreasJSON.is_hell_entry == true){
                            is_hell_entry=true;
                        }

                        /*
                        biomeType = "";
                        if( (r[4]==182) && (g=255) && (b=0) ){
                            biomeType = "P"; //plains
                        } else if ((r==76) && (g=255) && (b=0) ){
                            biomeType = "F1";
                        } else if ((r==0) && (g=0) && (b=0) ){
                            biomeType = "F3";
                        }*/

                        // display side buttons (arrows, foe Nbr, ally number). 
                        //north
                        var contentAreaNorthFoe = contentAreasFoe[1];
                        document.getElementById("up-arrow").innerHTML = contentAreaNorthFoe; 

                        //east
                        var contentAreaEastFoe = contentAreasFoe[5];
                        document.getElementById("right-arrow").innerHTML = contentAreaEastFoe;  

                        //south
                        var contentAreaSouthFoe = contentAreasFoe[7];
                        document.getElementById("down-arrow").innerHTML = contentAreaSouthFoe;  

                        //west
                        var contentAreaWestFoe = contentAreasFoe[3];
                        document.getElementById("left-arrow").innerHTML = contentAreaWestFoe;   
                        
                        //north west
                        var contentAreaNorthWestFoe = contentAreasFoe[0];
                        document.getElementById("up-left-arrow").innerHTML = contentAreaNorthWestFoe;  

                        //north east
                        var contentAreaNorthEastFoe = contentAreasFoe[2];
                        document.getElementById("up-right-arrow").innerHTML = contentAreaNorthEastFoe; 

                        //south east
                        var contentAreaSouthEastFoe = contentAreasFoe[8];
                        document.getElementById("down-right-arrow").innerHTML = contentAreaSouthEastFoe;

                        //soutn west
                        var contentAreaSouthWestFoe = contentAreasFoe[6];
                        document.getElementById("down-left-arrow").innerHTML = contentAreaSouthWestFoe;  

                        checkIfPlayerBlocked(contentAreasJSON.nbr_foe[4]);
                        checkAbyssBorders(r,g,b);
                    }

                    else if ($area == "hell"){
                        document.getElementById("hell").innerHTML = contentArea;

                        //check if the area is the hell exit
                        var is_hell_exit = false;
                        if(contentAreasJSON.is_hell_exit == true){
                            is_hell_exit=true;
                        }
                        //TODO same for dj2
                        
                        //get foe stats
                        //var distanceFromCity = Math.floor( Math.sqrt( Math.pow(contentAreaY,2)+ Math.pow(contentAreaX, 2) ) );
                        //var foeStr = distanceFromCity;
                        //var foeDef = distanceFromCity;
                        /*
                        //get biome type
                        if(contentAreasFoe.length == 9){ //normal case, 8 areas around the player
                            var r = contentAreasJSON.r[4];
                            var g = contentAreasJSON.g[4];
                            var b = contentAreasJSON.b[4];
                            var a = contentAreasJSON.a[4];
                        } else { //TOTEST TODO
                            //Si on est en bordure de map, area.class ne renvoie qu'un array à 1 dimension (la case en cours)
                            var r = contentAreasJSON.r[0];
                            var g = contentAreasJSON.g[0];
                            var b = contentAreasJSON.b[0];
                            var a = contentAreasJSON.a[0];
                        }

                        biomeType = "";
                        if( (r==182) && (g=255) && (b=0) ){
                            biomeType = "P"; //plains
                        } else if ((r==76) && (g=255) && (b=0) ){
                            biomeType = "F1";
                        } else if ((r==0) && (g=0) && (b=0) ){
                            biomeType = "F3";
                        }*/

                        //display side-map buttons

                        // the data for 8 areas are received (0-7). The current area is 4.

                        /* ORDER OF APPEARANCE

                        x   y
                        -1  1
                        0   1
                        1   1

                        -1  0
                        0   0
                        1   0

                        -1  -1
                        0   -1
                        1   -1

                        The ids in the json array of the 9 positions around the current area :
                        0   1   2
                        3   4   5
                        6   7   8

                        */

                        // display the foe numbers on side arrows
                        var contentAreaNorthFoe = contentAreasFoe[1];
                        document.getElementById("foe-nbr-up").innerHTML = contentAreaNorthFoe;  

                        var contentAreaEastFoe = contentAreasFoe[5];
                        document.getElementById("foe-nbr-right").innerHTML = contentAreaEastFoe;  

                        var contentAreaSouthFoe = contentAreasFoe[7];
                        document.getElementById("foe-nbr-down").innerHTML = contentAreaSouthFoe;  

                        var contentAreaWestFoe = contentAreasFoe[3];
                        document.getElementById("foe-nbr-left").innerHTML = contentAreaWestFoe;  

                        var contentAreaNorthWestFoe = contentAreasFoe[0];
                        document.getElementById("foe-nbr-up-left").innerHTML = contentAreaNorthWestFoe;  

                        var contentAreaNorthEastFoe = contentAreasFoe[2];
                        document.getElementById("foe-nbr-up-right").innerHTML = contentAreaNorthEastFoe;  

                        var contentAreaSouthEastFoe = contentAreasFoe[8];
                        document.getElementById("foe-nbr-down-right").innerHTML = contentAreaSouthEastFoe;  

                        var contentAreaSouthWestFoe = contentAreasFoe[6];
                        document.getElementById("foe-nbr-down-left").innerHTML = contentAreaSouthWestFoe; 

                        //displays the ally number on side arrows
                        var contentAreaNorthAlly = contentAreasAlly[1];
                        document.getElementById("ally-nbr-up").innerHTML = contentAreaNorthAlly;  

                        var contentAreaEastAlly = contentAreasAlly[5];
                        document.getElementById("ally-nbr-right").innerHTML = contentAreaEastAlly;  

                        var contentAreaSouthAlly = contentAreasAlly[7];
                        document.getElementById("ally-nbr-down").innerHTML = contentAreaSouthAlly;  

                        var contentAreaWestAlly = contentAreasAlly[3];
                        document.getElementById("ally-nbr-left").innerHTML = contentAreaWestAlly;  

                        var contentAreaNorthWestAlly = contentAreasAlly[0];
                        document.getElementById("ally-nbr-up-left").innerHTML = contentAreaNorthWestAlly;  

                        var contentAreaNorthEastAlly = contentAreasAlly[2];
                        document.getElementById("ally-nbr-up-right").innerHTML = contentAreaNorthEastAlly;  

                        var contentAreaSouthEastAlly = contentAreasAlly[8];
                        document.getElementById("ally-nbr-down-right").innerHTML = contentAreaSouthEastAlly;  

                        var contentAreaSouthWestAlly = contentAreasAlly[6];
                        document.getElementById("ally-nbr-down-left").innerHTML = contentAreaSouthWestAlly; 

                        // display side buttons (arrows, foe Nbr, ally number). Do not display if map limit
                        //north
                        if(contentAreas[1] == '0'){ //if out of the map
                            document.getElementById("up-arrow").style.display="none";
                            document.getElementById("up-wall").style.display="block";
                        }

                        //east
                        if(contentAreas[5] == '0'){
                            document.getElementById("right-arrow").style.display="none";
                            document.getElementById("right-wall").style.display="block";
                        }

                        //south
                        if(contentAreas[7] == '0'){
                            document.getElementById("down-arrow").style.display="none";
                            document.getElementById("down-wall").style.display="block";
                        }

                        //west
                        if(contentAreas[3] === '0'){
                            document.getElementById("left-arrow").style.display="none";
                            document.getElementById("left-wall").style.display="block";
                        }
                        
                        //north west
                        if(contentAreas[0] == '0'){
                            document.getElementById("up-left-arrow").style.display="none";
                            document.getElementById("up-left-wall").style.display="block";
                        }

                        //north east
                        if(contentAreas[2] == '0'){
                            document.getElementById("up-right-arrow").style.display="none";
                            document.getElementById("up-right-wall").style.display="block";
                        }

                        //south east
                        if(contentAreas[8] == '0'){
                            document.getElementById("down-right-arrow").style.display="none";
                            document.getElementById("down-right-wall").style.display="block";
                        }

                        //soutn west
                        if(contentAreas[6] == '0'){
                            document.getElementById("down-left-arrow").style.display="none";
                            document.getElementById("down-left-wall").style.display="block";
                        }

                        checkIfPlayerBlocked(contentAreasJSON.nbr_foe[4]);

                    }

                    else if ($area == "dj2"){
                        document.getElementById("dj2").innerHTML = contentArea;

                        /*
                        The ids in the json array of the 9 positions around the current area :
                        0   1   2
                        3   4   5
                        6   7   8
                        */

                        // display the foe numbers on side arrows
                        var contentAreaNorthFoe = contentAreasFoe[1];
                        document.getElementById("foe-nbr-up").innerHTML = contentAreaNorthFoe;  

                        var contentAreaEastFoe = contentAreasFoe[5];
                        document.getElementById("foe-nbr-right").innerHTML = contentAreaEastFoe;  

                        var contentAreaSouthFoe = contentAreasFoe[7];
                        document.getElementById("foe-nbr-down").innerHTML = contentAreaSouthFoe;  

                        var contentAreaWestFoe = contentAreasFoe[3];
                        document.getElementById("foe-nbr-left").innerHTML = contentAreaWestFoe;  

                        var contentAreaNorthWestFoe = contentAreasFoe[0];
                        document.getElementById("foe-nbr-up-left").innerHTML = contentAreaNorthWestFoe;  

                        var contentAreaNorthEastFoe = contentAreasFoe[2];
                        document.getElementById("foe-nbr-up-right").innerHTML = contentAreaNorthEastFoe;  

                        var contentAreaSouthEastFoe = contentAreasFoe[8];
                        document.getElementById("foe-nbr-down-right").innerHTML = contentAreaSouthEastFoe;  

                        var contentAreaSouthWestFoe = contentAreasFoe[6];
                        document.getElementById("foe-nbr-down-left").innerHTML = contentAreaSouthWestFoe; 

                        //displays the ally number on side arrows
                        var contentAreaNorthAlly = contentAreasAlly[1];
                        document.getElementById("ally-nbr-up").innerHTML = contentAreaNorthAlly;  

                        var contentAreaEastAlly = contentAreasAlly[5];
                        document.getElementById("ally-nbr-right").innerHTML = contentAreaEastAlly;  

                        var contentAreaSouthAlly = contentAreasAlly[7];
                        document.getElementById("ally-nbr-down").innerHTML = contentAreaSouthAlly;  

                        var contentAreaWestAlly = contentAreasAlly[3];
                        document.getElementById("ally-nbr-left").innerHTML = contentAreaWestAlly;  

                        var contentAreaNorthWestAlly = contentAreasAlly[0];
                        document.getElementById("ally-nbr-up-left").innerHTML = contentAreaNorthWestAlly;  

                        var contentAreaNorthEastAlly = contentAreasAlly[2];
                        document.getElementById("ally-nbr-up-right").innerHTML = contentAreaNorthEastAlly;  

                        var contentAreaSouthEastAlly = contentAreasAlly[8];
                        document.getElementById("ally-nbr-down-right").innerHTML = contentAreaSouthEastAlly;  

                        var contentAreaSouthWestAlly = contentAreasAlly[6];
                        document.getElementById("ally-nbr-down-left").innerHTML = contentAreaSouthWestAlly; 


                        // display side map (arrows, foe Nbr, ally number). Do not display if map limit
                        //north
                        if(contentAreas[1] == '0'){ //if out of the map
                            document.getElementById("up-arrow").style.display="none";
                            document.getElementById("up-wall").style.display="block";
                        } 

                        //east
                        if(contentAreas[5] == '0'){
                            document.getElementById("right-arrow").style.display="none";
                            document.getElementById("right-wall").style.display="block";
                        }

                        //south
                        if(contentAreas[7] == '0'){
                            document.getElementById("down-arrow").style.display="none";
                            document.getElementById("down-wall").style.display="block";
                        }

                        //west
                        if(contentAreas[3] === '0'){
                            document.getElementById("left-arrow").style.display="none";
                            document.getElementById("left-wall").style.display="block";
                        }
                        
                        //north west
                        if(contentAreas[0] == '0'){
                            document.getElementById("up-left-arrow").style.display="none";
                            document.getElementById("up-left-wall").style.display="block";
                        }

                        //north east
                        if(contentAreas[2] == '0'){
                            document.getElementById("up-right-arrow").style.display="none";
                            document.getElementById("up-right-wall").style.display="block";
                        }

                        //south east
                        if(contentAreas[8] == '0'){
                            document.getElementById("down-right-arrow").style.display="none";
                            document.getElementById("down-right-wall").style.display="block";
                        }

                        //soutn west
                        if(contentAreas[6] == '0'){
                            document.getElementById("down-left-arrow").style.display="none";
                            document.getElementById("down-left-wall").style.display="block";
                        }
                    
                        checkIfPlayerBlocked(contentAreasJSON.nbr_foe[4]);
                
                    }

                    //display current area in canvas
                    displayContent(contentArea, contentAreaFoe, citadel, cave, camp, cavecamp, tree, raretree, souche, stone, orange, honey, ally, contentAreaAlly, foe, canvas, ctx, 
                        is_city, is_cave_entry, is_camp, is_dj2_entry, is_cave_exit, is_abyss_exit, is_hell_entry, is_hell_exit); 

                }
            });

            
        
    } //stone.onload()  
}

// If a side area is completely white (255,255,255), the players can not move to it. It allows the complete range of pixel colors.
function checkAbyssBorders(r,g,b)
{
    //function reusable for any modded map with walls
    //if(r[0]!="0" || g[0]!="0" || b[0]!="0"){
    //    document.getElementById("up-left-arrow").style.display="none";
    //    document.getElementById("up-left-wall").style.display="block";
    //}
    if(r[1]=="255" && g[1]=="255" && b[1]=="255"){
        document.getElementById("up-arrow").style.display="none";
        document.getElementById("up-wall").style.display="block";
    }
    //if(r[2]!="0" || g[2]!="0" || b[2]!="0"){
    //    document.getElementById("up-right-arrow").style.display="block";
    //    document.getElementById("up-right-wall").style.display="none";
    //}
    if(r[3]=="255" && g[3]=="255" && b[3]=="255"){
        document.getElementById("left-arrow").style.display="none";
        document.getElementById("left-wall").style.display="block";
    }
    if(r[5]=="255" && g[5]=="255" && b[5]=="255"){
        document.getElementById("right-arrow").style.display="none";
        document.getElementById("right-wall").style.display="block";
    }
    //if(r[6]!="0" || g[6]!="0" || b[6]!="0"){
    //    document.getElementById("down-left-arrow").style.display="block";
    //    document.getElementById("down-left-wall").style.display="none";
    //}
    if(r[7]=="255" && g[7]=="255" && b[7]=="255"){
        document.getElementById("down-arrow").style.display="none";
        document.getElementById("down-wall").style.display="block";
    }
    //if(r[8]!="0" || g[8]!="0" || b[8]!="0"){
    //    document.getElementById("down-right-arrow").style.display="block";
    //    document.getElementById("down-right-wall").style.display="none";
    //}

    //always set the corners buttons as "block"
    document.getElementById("up-left-arrow").style.display="none";
    document.getElementById("up-left-wall").style.display="block";
    document.getElementById("up-right-arrow").style.display="none";
    document.getElementById("up-right-wall").style.display="block";
    document.getElementById("down-left-arrow").style.display="none";
    document.getElementById("down-left-wall").style.display="block";
    document.getElementById("down-right-arrow").style.display="none";
    document.getElementById("down-right-wall").style.display="block";

}

function checkIfPlayerBlocked(foe){
    
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {action : "checkIfPlayerBlocked", foeNbr : foe},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                location.reload();
            }
            var response = JSON.parse(response);
            
            var dir = response[0];
            var name = response[1];
            var date = response[2];
            var action = response[3];
            
            // Ajoute une Action disant que le joueur est bloqué, si une action existe
            if(action != ''){
              var newAction = document.createElement('LI');
              newAction.innerHTML = '<p>' + date + ' | ' + name + ' | ' + action + '</p>';
              var newWrapper = document.getElementById('actions-wrapper-for-map').children[0]; //ul
              var firstNew = document.getElementById('actions-wrapper-for-map').children[0].children[1]; //div > ul > li. Skip title node [0].
              newWrapper.insertBefore(newAction, firstNew);

            }

            if(dir != '0'){ //a direction is returned, so the player is indeed blocked by a monster.

              // Display the correct walls based on the previous direction
              switch(dir){
                case 'n': //the player comes from North, so block every other direction.
                    //document.getElementById("up-arrow").style.display="none";
                    document.getElementById("down-arrow").style.display="none";
                    document.getElementById("left-arrow").style.display="none";
                    document.getElementById("right-arrow").style.display="none";
                    document.getElementById("up-left-arrow").style.display="none";
                    document.getElementById("up-right-arrow").style.display="none";
                    document.getElementById("down-left-arrow").style.display="none";
                    document.getElementById("down-right-arrow").style.display="none";

                    //displays red walls
                    //document.getElementById("up-wall").style.backgroundColor="red";
                    //document.getElementById("up-wall").style.display="block";
                    document.getElementById("down-wall").style.backgroundColor="#e00000";
                    document.getElementById("down-wall").style.display="block";
                    document.getElementById("left-wall").style.backgroundColor="#e00000";
                    document.getElementById("left-wall").style.display="block";
                    document.getElementById("right-wall").style.backgroundColor="#e00000";
                    document.getElementById("right-wall").style.display="block";
                    
                break;
                case 's':
                        document.getElementById("up-arrow").style.display="none";
                        //document.getElementById("down-arrow").style.display="none";
                        document.getElementById("left-arrow").style.display="none";
                        document.getElementById("right-arrow").style.display="none";
                        document.getElementById("up-left-arrow").style.display="none";
                        document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("left-wall").style.backgroundColor="#e00000";
                        document.getElementById("left-wall").style.display="block";
                        document.getElementById("right-wall").style.backgroundColor="#e00000";
                        document.getElementById("right-wall").style.display="block";

                        break;
                case 'e':
                        document.getElementById("up-arrow").style.display="none";
                        document.getElementById("down-arrow").style.display="none";
                        document.getElementById("left-arrow").style.display="none";
                        //document.getElementById("right-arrow").style.display="none";
                        document.getElementById("up-left-arrow").style.display="none";
                        document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("down-wall").style.backgroundColor="#e00000";
                        document.getElementById("down-wall").style.display="block";
                        document.getElementById("left-wall").style.backgroundColor="#e00000";
                        document.getElementById("left-wall").style.display="block";

                        break;
                case 'w':
                        document.getElementById("up-arrow").style.display="none";
                        document.getElementById("down-arrow").style.display="none";
                        //document.getElementById("left-arrow").style.display="none";
                        document.getElementById("right-arrow").style.display="none";
                        document.getElementById("up-left-arrow").style.display="none";
                        document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("down-wall").style.backgroundColor="#e00000";
                        document.getElementById("down-wall").style.display="block";
                        //document.getElementById("left-wall").style.backgroundColor="red";
                       // document.getElementById("left-wall").style.display="block";
                        document.getElementById("right-wall").style.backgroundColor="#e00000";
                        document.getElementById("right-wall").style.display="block";

                        break;
                case 'nw':
                        document.getElementById("up-arrow").style.display="none";
                        document.getElementById("down-arrow").style.display="none";
                        document.getElementById("left-arrow").style.display="none";
                        document.getElementById("right-arrow").style.display="none";
                        //document.getElementById("up-left-arrow").style.display="none";
                        document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("down-wall").style.backgroundColor="#e00000";
                        document.getElementById("down-wall").style.display="block";
                        document.getElementById("left-wall").style.backgroundColor="#e00000";
                        document.getElementById("left-wall").style.display="block";
                        document.getElementById("right-wall").style.backgroundColor="#e00000";
                        document.getElementById("right-wall").style.display="block";

                        break;
                case 'ne':
                        document.getElementById("up-arrow").style.display="none";
                        document.getElementById("down-arrow").style.display="none";
                        document.getElementById("left-arrow").style.display="none";
                        document.getElementById("right-arrow").style.display="none";
                        document.getElementById("up-left-arrow").style.display="none";
                        //document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("down-wall").style.backgroundColor="#e00000";
                        document.getElementById("down-wall").style.display="block";
                        document.getElementById("left-wall").style.backgroundColor="#e00000";
                        document.getElementById("left-wall").style.display="block";
                        document.getElementById("right-wall").style.backgroundColor="#e00000";
                        document.getElementById("right-wall").style.display="block";
                        document.getElementById("up-left-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-left-wall").style.display="block";

                        break;
                case 'se':
                        document.getElementById("up-arrow").style.display="none";
                        document.getElementById("down-arrow").style.display="none";
                        document.getElementById("left-arrow").style.display="none";
                        document.getElementById("right-arrow").style.display="none";
                        document.getElementById("up-left-arrow").style.display="none";
                        document.getElementById("up-right-arrow").style.display="none";
                        document.getElementById("down-left-arrow").style.display="none";
                        //document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("down-wall").style.backgroundColor="#e00000";
                        document.getElementById("down-wall").style.display="block";
                        document.getElementById("left-wall").style.backgroundColor="#e00000";
                        document.getElementById("left-wall").style.display="block";
                        document.getElementById("right-wall").style.backgroundColor="#e00000";
                        document.getElementById("right-wall").style.display="block";
                        
                        break;
                case 'sw':
                        document.getElementById("up-arrow").style.display="none";
                        document.getElementById("down-arrow").style.display="none";
                        document.getElementById("left-arrow").style.display="none";
                        document.getElementById("right-arrow").style.display="none";
                        document.getElementById("up-left-arrow").style.display="none";
                        document.getElementById("up-right-arrow").style.display="none";
                        //document.getElementById("down-left-arrow").style.display="none";
                        document.getElementById("down-right-arrow").style.display="none";
    
                        //displays red walls
                        document.getElementById("up-wall").style.backgroundColor="#e00000";
                        document.getElementById("up-wall").style.display="block";
                        document.getElementById("down-wall").style.backgroundColor="#e00000";
                        document.getElementById("down-wall").style.display="block";
                        document.getElementById("left-wall").style.backgroundColor="#e00000";
                        document.getElementById("left-wall").style.display="block";
                        document.getElementById("right-wall").style.backgroundColor="#e00000";
                        document.getElementById("right-wall").style.display="block";
                        
                        break;
                default:
                        //joueur non bloqué

              }

              // Change size, position and color of diagonal walls. Disabled in dungeons
              if(document.getElementById("up-left-wall") !== null)
              {
                document.getElementById("up-left-wall").style.backgroundColor="#e00000";
                document.getElementById("up-left-wall").style.display="block";
                document.getElementById("up-left-wall").style.width="30px";
                document.getElementById("up-left-wall").style.height="30px";

                document.getElementById("up-right-wall").style.backgroundColor="#e00000";
                document.getElementById("up-right-wall").style.display="block";
                document.getElementById("up-right-wall").style.width="30px";
                document.getElementById("up-right-wall").style.height="30px";
                document.getElementById("up-right-wall").style.left="472px";

                document.getElementById("down-left-wall").style.backgroundColor="#e00000";
                document.getElementById("down-left-wall").style.display="block";
                document.getElementById("down-left-wall").style.width="30px";
                document.getElementById("down-left-wall").style.height="30px";
                document.getElementById("down-left-wall").style.top="472px";

                document.getElementById("down-right-wall").style.backgroundColor="#e00000";
                document.getElementById("down-right-wall").style.display="block";
                document.getElementById("down-right-wall").style.width="30px";
                document.getElementById("down-right-wall").style.height="30px";
                document.getElementById("down-right-wall").style.left="472px";
                document.getElementById("down-right-wall").style.top="472px";
              }

            }
        }
    });
}

function addDirectionChangeFunctionsOnArrows()
{
    document.getElementById("up-arrow").onclick = function() {moveUp();}
    document.getElementById("down-arrow").onclick = function() {moveDown()};
    document.getElementById("left-arrow").onclick = function() {moveLeft()};
    document.getElementById("right-arrow").onclick =  function() {moveRight()};

    document.getElementById("up-left-arrow").onclick = function() {moveUpLeft()};
    document.getElementById("up-right-arrow").onclick = function() {moveUpRight()};
    document.getElementById("down-left-arrow").onclick = function() {moveDownLeft()};
    document.getElementById("down-right-arrow").onclick = function() {moveDownRight()};
}

function displayContent(contentArea, contentAreaFoe, citadel, cave, camp, cavecamp, tree, raretree, souche, stone, orange, honey, ally, contentAreaAlly, foe, canvas, ctx, 
        is_city=false, is_cave_entry=false, is_camp=false, is_dj2_entry = false, is_cave_exit = false, is_abyss_exit=false, is_hell_entry=false, is_hell_exit=false) {
     var maxX = 10; // 10 emplacement sur X
     var maxY = 10;
     var decorsList = []; 
     var k = 1;
     var object='';
     
     var isTree=0;
     var isTree1=0;
     //var isRock=0;
     var isHoney=0;
     var isOrange=0;
     var isHoney=0;
     
     //display monster(s) on area
     if (contentAreaFoe > 0){
         for(var i=0; i<contentAreaFoe; i++){
            var randomPosFoeX = Math.floor(Math.random() * 500) - 10; 
            var randomPosFoeY = Math.floor(Math.random() * 500) - 10; 
            ctx.drawImage(foe, randomPosFoeX , randomPosFoeY , 50, 50); 
         }
     }
     //Display Content
     for (var i=1; i<=maxX; i++) {
         for (var j=1; j<=maxY; j++) {
            
            var object = contentArea.substr(k,1);
            
            //put randomness in database !
            var randomNumber = Math.floor(Math.random() * 20) - 10; //add a little randomness to the placement of the trees and stone.
     
            decorsList.push(object);

            switch (decorsList[k-1]) {
                case 't':
                    //alert((canvas.width/11)*j - 10 +randomNumber);
                    ctx.drawImage(tree, (canvas.width/11)*j - 10 +randomNumber , (canvas.width/11)*i -10 +randomNumber , 40, 40); //-10 to adapt to the screen
                    isTree=1;
                    break;
                case 'u':
                    //alert((canvas.width/11)*j - 10 +randomNumber);
                    ctx.drawImage(raretree, (canvas.width/11)*j - 10 +randomNumber , (canvas.width/11)*i -10 +randomNumber , 40, 40); //-10 to adapt to the screen
                    isTree1=1;
                    break;
                case 'n':
                    //alert("Il n'y a rien");
                    break;
                case 'r':
                    ctx.drawImage(stone, (canvas.width/11)*j -10 +randomNumber, (canvas.width/11)*i -10 +randomNumber, 20, 20);  
                    isRock=1;
                    //ctx.drawImage(stone, 100, 50, 50 ,100);  
                    //ctx.drawImage(souche, (canvas.width/10)*i), 50);
                    break;
                case 'o':
                    ctx.drawImage(orange, (canvas.width/11)*j -10 +randomNumber, (canvas.width/11)*i -10 +randomNumber, 40, 40);  
                    isOrange=1;
                    //ctx.drawImage(stone, 100, 50, 50 ,100);  
                    //ctx.drawImage(souche, (canvas.width/10)*i), 50);
                    break;
                case 'h':
                    ctx.drawImage(honey, (canvas.width/11)*j -10 +randomNumber, (canvas.width/11)*i -10 +randomNumber, 20, 20); 
                    isHoney=1; 
                    //ctx.drawImage(stone, 100, 50, 50 ,100);  
                    //ctx.drawImage(souche, (canvas.width/10)*i), 50);
                    break;
                case 's':
                    ctx.drawImage(souche, (canvas.width/11)*j -10 +randomNumber, (canvas.width/11)*i -10 +randomNumber, 20, 20);
                    break;
                default:
                    break;
            }
            k++;
         }
     }

     //display the player, because contentAreaAlly does not take it into account.
     var randomPosPlayerX = Math.floor(Math.random() * 500) - 10; 
     var randomPosPlayerY = Math.floor(Math.random() * 500) - 10; 
     ctx.drawImage(ally, randomPosPlayerX , randomPosPlayerY , 25, 25); 

     //display allies(s) on area
     //TODO modifier getAllyNbr pour différencier les rôles, et ainsi afficher les leaders, ou bien créer une autre fct getHeroNbr()
     if (contentAreaAlly > 0){
        for(var i=0; i<contentAreaAlly; i++){
           var randomPosAllyX = Math.floor(Math.random() * 500) - 10; 
           var randomPosAllyY = Math.floor(Math.random() * 500) - 10; 
           ctx.drawImage(ally, randomPosAllyX , randomPosAllyY , 30, 30); 
        }
    }

    //Crée et affiche les left_btn correspondant aux ressources disponibles
    //TODO ? : inserver : ne pas afficher le bouton par défaut et l'afficher s'il y a un arbre sur map.
    if (isTree==0){
        if(document.getElementById('harvest-tree')){ //A priori, pas de récolte dans les dj
            document.getElementById('harvest-tree').parentNode.style.display='none';
        }
    }
    if (isTree1==0){
        if(document.getElementById('harvest-tree1')){ //A priori, pas de récolte dans les dj
            document.getElementById('harvest-tree1').parentNode.style.display='none';
        }
    }
    if (isOrange==0){
        if(document.getElementById('harvest-orange')){ //A priori, pas de récolte dans les dj
            document.getElementById('harvest-orange').parentNode.style.display='none';
        }
    }
    if (isHoney==0){
        if(document.getElementById('harvest-honey')){ //A priori, pas de récolte dans les dj
            document.getElementById('harvest-honey').parentNode.style.display='none';
        }
    }
    //if (isRock==0){
    //    document.getElementById('harvest-rock').parentNode.style.display='none';
    //}

    isTree=isTree2=isRock=isHoney=isOrange=isHoney=0;

    // Si la case est l'entrée d'une ville, afficher l'image de la ville
    if(is_city){
       ctx.drawImage(citadel, 50 , 50 , 400, 400);
    }

    // Si la case est l'entrée d'un donjon, afficher l'image de la grotte
    else if(is_cave_entry && !is_camp){
       ctx.drawImage(cave, 100 , 100 , 300, 300);
    }

    // Si la case est un camp, afficher l'image du camp
    else if(is_camp && !is_cave_entry){
       ctx.drawImage(camp, 100 , 100 , 300, 300); 
    }

    //si la case est un donjon avec un camp, afficher l'image combinée.
    else if(is_camp && is_cave_entry){
        ctx.drawImage(cavecamp, 100 , 100 , 300, 300); 
    }

    //si la case est une sortie de dj, une entrée de dj2, une sortie de dj2, une entrée d'enfer ou une sortie d'enfer, afficher l'image caverne.
    else if(is_dj2_entry || is_cave_exit || is_abyss_exit || is_hell_entry || is_hell_exit){
        ctx.drawImage(cave, 100 , 100 , 300, 300);
    }
}    
 
/*
 function checkMapLimitSides(){    
     var str = "checkMapLimitSides";
    
     var xhttp = new XMLHttpRequest();
     xhttp.onreadystatechange = function() {
       if (this.readyState === 4 && this.status === 200) {
            if (this.responseText === 'north') 
                document.getElementById("up-arrow").style.display = 'none';
            if (this.responseText === 'east') 
                document.getElementById("right-arrow").style.display = 'none';
            if (this.responseText === 'south') 
                document.getElementById("down-arrow").style.display = 'none';
            if (this.responseText === 'west') 
                document.getElementById("left-arrow").style.display = 'none';
            
            if (this.responseText === 'northeast') {
                document.getElementById("up-arrow").style.display = 'none';
                document.getElementById("right-arrow").style.display = 'none';
                //document.getElementById("up-right-arrow").style.display = 'none';
            }
            if (this.responseText === 'southeast') {
                document.getElementById("down-arrow").style.display = 'none';
                document.getElementById("right-arrow").style.display = 'none';
                //document.getElementById("down-right-arrow").style.display = 'none';
            }
            if (this.responseText === 'southwest') {
                document.getElementById("down-arrow").style.display = 'none';
                document.getElementById("left-arrow").style.display = 'none';
               // document.getElementById("down-left-arrow").style.display = 'none';
            }
            if (this.responseText === 'northwest') {
                document.getElementById("left-arrow").style.display = 'none';
                document.getElementById("up-arrow").style.display = 'none';
               // document.getElementById("up-left-arrow").style.display = 'none';
            }
            
             //alert(this.responseText);
       }
     };
     xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
     xhttp.send();
     
 } */
 /*
 function checkMapLimitCorners(){    
     var str = "checkMapLimitCorners";
    
     var xhttp = new XMLHttpRequest();
     xhttp.onreadystatechange = function() {
       if (this.readyState === 4 && this.status === 200) {
           
            if (this.responseText === 'northeast') {
                document.getElementById("up-right-arrow").style.display = 'none';
            }
            if (this.responseText === 'southeast') {
                document.getElementById("down-right-arrow").style.display = 'none';
            }
            if (this.responseText === 'southwest') {
                document.getElementById("down-left-arrow").style.display = 'none';
            }
            if (this.responseText === 'northwest') {
                document.getElementById("up-left-arrow").style.display = 'none';
            }
            
             //alert(this.responseText);
       }
     };
     xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
     xhttp.send();
 }
 */
function getPosX()
{ 
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "getPosX"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                document.getElementById("displayPosX").innerHTML = response;
            }

        }
    });

    //var str = "getPosX";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //  if (this.readyState === 4 && this.status === 200) {
    //    document.getElementById("displayPosX").innerHTML = this.responseText;
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}

function getPosY()
{
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "getPosY"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                document.getElementById("displayPosY").innerHTML = response;
            }

        }
    });

    //var str = "getPosY";
    //
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //  if (this.readyState === 4 && this.status === 200) {
    //    document.getElementById("displayPosY").innerHTML = this.responseText;
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
 
//Minimap functions

//loads and displays the minimap if hidden. Else hide the map.
if(document.getElementById("displayMiniMap")){
    document.getElementById("displayMiniMap").addEventListener("click", function(event) {

        if (document.getElementById("minimap").style.display === "none") {
            document.getElementById("minimap").style.display = "block";

            //reinitialize style values if OutOfTheMap message displayed.
            document.getElementById('minimap').style.backgroundColor = ''; 
            document.getElementById('minimap').style.border = '';
            document.getElementById('minimap').style.color = 'black';

            // Displaying the minimap side arrows on the outside map only
            if(document.getElementById('map') !== null)
            {
                document.getElementById("minimap-side-north").style.display = "block";
                document.getElementById("minimap-side-east").style.display = "block";
                document.getElementById("minimap-side-west").style.display = "block";
                document.getElementById("minimap-side-south").style.display = "block";
            }
            
            //loading png is here i guess
            //TODO : prevent multiple loadings if the user multiclicks.
            loadMiniMap('center');

          } else {

            document.getElementById("minimap").style.display = "none";
            document.getElementById("minimap-side-north").style.display = "none";
            document.getElementById("minimap-side-east").style.display = "none";
            document.getElementById("minimap-side-west").style.display = "none";
            document.getElementById("minimap-side-south").style.display = "none";
            
          }
        
    });

    //reload minimap on side arroy clic, on the outside map only
    if(document.getElementById('map') !== null)
    {
        document.getElementById("minimap-side-north").addEventListener("click", function() {
            loadMiniMap('n');
        });
        document.getElementById("minimap-side-east").addEventListener("click", function() {
            loadMiniMap('e');
        });
        document.getElementById("minimap-side-south").addEventListener("click", function() {
            loadMiniMap('s');
        });
        document.getElementById("minimap-side-west").addEventListener("click", function() {
            loadMiniMap('w');
        });
    }
}

function loadMiniMap(direction){ //no svg, onclick

    var tree=0;
    var tree1=0;
    var honey=0;
    var orange=0;
    var k=1;
    var id = 0; //id of the received array (array id checking is done server side)

    var city=false;
    var dj_entry=false;
    var dj_exit=false;
   // var cave=false;
    var dj2_entry=false;
    var dj2_exit=false;
    var hell_entry=false;
    var hell_exit=false;
    var camp=false;
   // var djCave=false;
    var djCamp=false;


    //ajax call to get 20*20 areas around the player, colors, foes, is_discovered, content, players, leaders
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {action: "loadMiniMap",
            direction: direction}, 
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                location.reload();
            }

            //If 400 areas are not returned, then we are on the border of the map, and we won't display any area. Improvement : put an ocean here.
            if(response == 'outofthemap'){

                document.getElementById('minimap').style.backgroundColor = 'black';
                document.getElementById('minimap').style.border = '1px solid white';
                document.getElementById('minimap').innerHTML = 'Vous tentez d\'observer les régions noires, mais d\'épais nuages vous en empêchent.';
                document.getElementById('minimap').style.color = 'white';
                document.getElementById('minimap').style.textAlign = 'center';

                document.getElementById("minimap-side-north").style.display = "none";
                document.getElementById("minimap-side-east").style.display = "none";
                document.getElementById("minimap-side-west").style.display = "none";
                document.getElementById("minimap-side-south").style.display = "none";

            }
            else if(response == 'noDiscover'){

                document.getElementById('minimap').style.backgroundColor = 'black';
                document.getElementById('minimap').style.border = '1px solid white';
                document.getElementById('minimap').innerHTML = 'Personne n\'est jamais allé las-bas et il n\'y a donc pas de carte. Allez-y pour cartographier la région.';
                document.getElementById('minimap').style.color = 'white';
                document.getElementById('minimap').style.textAlign = 'center';

                document.getElementById("minimap-side-north").style.display = "none";
                document.getElementById("minimap-side-east").style.display = "none";
                document.getElementById("minimap-side-west").style.display = "none";
                document.getElementById("minimap-side-south").style.display = "none";

            } 
            else {

                var mapArray = JSON.parse(response); 
                //console.log(mapArray);
                /* mapArray = {'content' => 'tttttutututtutt', 
                            'is_discovered' => '1',
                            'area_type' => 'field',
                            'foe' => '4',
                            'players' => '5',
                            ...}
                */
                //build the grid as a table, w/ tooltip on each area
                var minimap = '';
                minimap += '<table>';
                for (var i = 0; i <= 19; i++){
                    minimap += '<tr>';
                    for (var j = 0; j <= 19; j++){
                        minimap += '<td ';

                        //if this is a DJ, and the area is discovered, define the borders according to walls
                        if(parseInt(mapArray[id].is_discovered) == 1){
                            if(mapArray[id].area_type=='dj'){
                                minimap += 'style="';
                                if(mapArray[id].u == '1')
                                    minimap += 'border-top: 2px solid white; ';
                                if(mapArray[id].r == '1')
                                    minimap += 'border-right: 2px solid white; ';
                                if(mapArray[id].d == '1')
                                    minimap += 'border-bottom: 2px solid white; ';
                                if(mapArray[id].l == '1')
                                    minimap += 'border-left: 2px solid white; ';
                                minimap += '" ';
                            }
                        }

                        //check if the area is discovered. If yes, display the area.
                        if(parseInt(mapArray[id].is_discovered) == 0){
                            minimap += 'class="undiscovered"></td>';
                        } else {

                            //get the resources from string
                            var l = mapArray[id].content.length;
                            for (var k=1; k<=l; k++) {
                                
                                var resource = mapArray[id].content.substr(k,1);
                            
                                switch (resource) {
                                    case 't':
                                        tree+=1;
                                        break;
                                    case 'u':
                                        tree1+=1;
                                        break;
                                    case 'n':
                                        break;
                                    //case 'r':
                                        //rock+=1;
                                        //break;
                                    case 'o':
                                        orange+=1;
                                        break;
                                    case 'h':
                                        honey+=1; 
                                        break;
                                    default:
                                        break;
                                }
                                
                            }

                            //set the color of the area : red if monster, blue if ally, violet if both, else biome color
                            //TODO : better way ?
                            minimap += 'class="';
                            if (mapArray[id].foe != 0 && mapArray[id].ally != 0){
                                minimap += 'area-contested ';
                            } else if (mapArray[id].foe != 0 && mapArray[id].ally == 0){
                                minimap += 'area-danger ';
                            } else if (mapArray[id].foe == 0 && mapArray[id].ally != 0){
                                minimap += 'area-ally ';
                            } else {
                                switch(mapArray[id].area_type){
                                    case 'field':
                                        minimap += 'area-field ';
                                    break;
                                    case 'abyss':
                                        minimap += 'area-abyss ';
                                    break;
                                    default: //case noire
                                        minimap += 'area-abyss ';
                                }
                            }

                            
                            //set if the area is a DJ, a cave, the city, or the player itself
                            $playerIsHere = 0;

                            if(mapArray[id].x == mapArray[0].player_pos_x && mapArray[id].y == mapArray[0].player_pos_y){
                                $playerIsHere = 1;
                            }

                            if($playerIsHere == 1){
                                minimap += 'area-is-player ';

                            } else {

                                if(mapArray[id].is_city && +mapArray[id].is_city == 1){ // + = parseInt
                                    minimap += 'area-is-city ';
                                    city=true;
                                }
                                else if(mapArray[id].is_dj_entry && +mapArray[id].is_dj_entry == 1){ //a call for abyss area won't return is_dj
                                    minimap += 'area-is-dj-entry ';
                                    dj_entry=true;
                                }
                                // In dj
                                else if(mapArray[id].is_dj_exit && +mapArray[id].is_dj_exit == 1){ //a call for abyss area won't return is_dj
                                    minimap += 'area-is-dj-exit ';
                                    dj_exit=true;
                                }
                                /*
                                else if(mapArray[id].is_cave && +mapArray[id].is_cave == 1){
                                    minimap += 'area-is-cave ';
                                    cave=true;
                                }*/
                                // In dj
                                else if(mapArray[id].is_dj2_entry && +mapArray[id].is_dj2_entry == 1){
                                    minimap += 'area-is-dj2-entry ';
                                    dj2_entry=true;
                                }

                                // In dj2
                                else if(mapArray[id].is_dj2_exit && +mapArray[id].is_dj2_exit == 1){
                                    minimap += 'area-is-dj2-exit ';
                                    dj2_exit=true;
                                }
                                else if(mapArray[id].is_hell_entry && +mapArray[id].is_hell_entry == 1){
                                    minimap += 'area-is-hell-entry ';
                                    hell_entry=true;
                                }
                                else if(mapArray[id].is_hell_exit && +mapArray[id].is_hell_exit == 1){
                                    minimap += 'area-is-hell-exit ';
                                    hell_exit=true;
                                }

                                if(mapArray[id].is_camp && +mapArray[id].is_camp == 1){
                                    minimap += 'area-is-camp ';
                                    camp=true;
                                }
                                /*
                                else if(mapArray[id].is_camp && +mapArray[id].is_camp == 1
                                    && mapArray[id].is_cave && +mapArray[id].is_cave == 1){
                                    minimap += 'area-is-cave-camp ';
                                    caveCamp=true;
                                }*/
                                else if(mapArray[id].is_camp && +mapArray[id].is_camp == 1
                                    && mapArray[id].is_dj && +mapArray[id].is_dj == 1){
                                    minimap += 'area-is-dj-camp ';
                                    djCamp=true;
                                }

                                if(tree == 0 && tree1 == 0 && orange == 0 && honey == 0){
                                    minimap += 'empty ';
                                }
                            }

                            minimap += '"'; //close the class tag

                            //set the area id
                            minimap += 'id="area'+id+'">';
                            
                            //Area info in tooltip
                            minimap += '<span class="tooltiptext">';
                    
                            //add tooltip title
                            if(camp){
                                minimap += '<span style="font-weight:bold; color:green;">Camp</span> ';
                            }
                            else if(city){
                                minimap += '<span style="font-weight:bold; color:green;">Citadelle</span> ';
                            }
                            else if(dj_entry){
                                minimap += '<span style="font-weight:bold; color:red;">Donjon</span> ';
                            }
                            else if(dj_exit){
                                minimap += '<span style="font-weight:bold; color:red;">Sortie du donjon</span> ';
                            }/*
                            else if(cave){
                                minimap += '<span style="font-weight:bold; color:red;">Caverne</span> ';
                            }*/
                            else if(dj2_entry){
                                minimap += '<span style="font-weight:bold; color:red;">Fond du donjon</span> ';
                            }
                            else if(dj2_exit){
                                minimap += '<span style="font-weight:bold; color:red;">Sortie</span> ';
                            }
                            else if(hell_entry){
                                minimap += '<span style="font-weight:bold; color:red;">Enfers</span> ';
                            }
                            else if(hell_exit){
                                minimap += '<span style="font-weight:bold; color:red;">Sortie des Enfers</span> ';
                            }
                            /*
                            else if(djCave){
                                minimap += '<span style="font-weight:bold; color:green;">Caverne aménagée</span> ';
                            }*/
                            // Camp at the DJ entrance (sprite camp + cave entry)
                            else if(djCamp){
                                minimap += '<span style="font-weight:bold; color:green;">Donjon aménagé</span> ';
                            }

                            //add coordinates
                            minimap += '(' + mapArray[id].x + ',' + mapArray[id].y + ')';

                            if(mapArray[id].x == mapArray[0].player_pos_x && mapArray[id].y == mapArray[0].player_pos_y){
                                minimap += '<br/><strong>Vous êtes ici</strong>';
                            }
                            //add a breaking line
                            minimap += '<hr>';

                            //'<hr>Alliés: '+mapArray[id].players+
                            //'<hr>Leaders: '+mapArray[id].leaders+
                            if(mapArray[id].ally != 0){
                                minimap += '<span style="color:rgb(0, 140, 255);">Joueurs: '+mapArray[id].ally+ '</span><br/>';
                            }
                            if(mapArray[id].foe != 0){
                                minimap += '<span style="color:red;">Ombres: '+mapArray[id].foe+ '</span><br/>';
                            }
                            if(tree != 0){
                                minimap += '<span>Bois: ' +tree+ '</span><br/>';
                            }
                            if(tree1 != 0){
                                minimap += '<span>Bois précieux: '+tree1+ '</span><br/>';
                            }
                            if(orange != 0){
                                minimap += '<span>Oranges: '+orange+ '</span><br/>';
                            }
                            if(honey != 0){
                                minimap += '<span>Miel: '+honey +  '</span><br/>';
                            }
                            if(mapArray[id].ally == 0 && mapArray[id].foe == 0 && tree == 0 && tree1 == 0 && orange == 0 && honey == 0){
                                minimap += 'Désert<br/>';
                            }
                            minimap += '</span></td>';

                                
                            // Reinitilizing the variables for the next loop
                            tree=0;
                            tree1=0;
                            honey=0;
                            orange=0;

                            city=false;
                            dj_entry=false;
                            dj_exit=false;
                            //cave=false;
                            dj2_entry=false;
                            dj2_exit=false;
                            hell_entry=false;
                            hell_exit=false;
                            camp=false;
                            djCave=false;
                            djCamp=false;

                            
                        } //else

                        id++; //go to the next area.

                    } //for j
                    

                    minimap += '</tr>';

                } //for i
                minimap += '</table>';
                //console.log(minimap);
                document.getElementById('minimap').innerHTML=minimap;
            } //else 
        }, //on success
        error: function(response){
            console.log(response);
        }
    });

}                                   

// Minimap en SVG (abandonné)
/*
function displayMiniMap(){
    if (document.getElementById("minimap").style.display === "none") {
      document.getElementById("minimap").style.display = "block";
    } else {
      document.getElementById("minimap").style.display = "none";
    }
}

function loadMiniMap(){
    var svgParent = new SvgContainer({
    'width': 400,
    'height': 400
    });
    
    var rows = 20;
    var columns = 20;
    
    var minimap = document.getElementById('minimap');
    
    
    //Put the right biome color from DB
    var str = "loadBiomeColors";
    var biomeColors = [];
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {
             biomeColors = this.responseText;
          }
        };
    xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    xhttp.send();
    
    //Remplissage par carrés de map
    var rect = [];
    var id=0;
    for (var i = 0; i < rows; i++){
      for (var j = 0; j < columns; j++){
        rect[i] = new Rect({
              'x': (400/columns)*i,
              'y': (400/rows)*j,
              'width':(400/columns),
              'height': (400/rows),
              'style': 'fill:rgb(0,255,0)', //"fill:rgb(0,0,255);stroke-width:3;stroke:rgb(0,0,0)"
              'onmouseenter': 'displayMapHover('+id+')',
              'onmouseout': 'hideMapHover('+id+')'
        });
        svgParent.appendChild(rect[i]);
        svgParent.lastChild.setAttribute("id", 'rect'+id);
        //addevent listener onmouseover // tooltip avec ressources et foes
        //svgParent.lastChild.setAttribute("class", "area");
        
        //mise des couleurs sur les carré
        switch (biomeColors[id]){
            case "0":
                svgParent.lastChild.setAttribute("fill", "yellow");
                break;
            case "1":
                svgParent.lastChild.setAttribute("fill", "green");
                break;
            case "2":
                svgParent.lastChild.setAttribute("fill", "gray");
                break;
        }
        id++;
        
        }
    }
    
    //Quadrillage de lignes
    var line = [];
    
    for (var i = 0; i < columns; i++){
        //var row = (svgParent.height/20)*i;
        line[i] = new Line({
            'x1': 0,
            'y1': (400/columns)*i,
            'x2': 400,
            'y2': (400/columns)*i,
            'style': 'stroke:rgb(0,0,0);stroke-width:1'
        });
        svgParent.appendChild(line[i]);
    }
    for (var i = 0; i < rows; i++){
        //var row = (svgParent.height/20)*i;
        line[i] = new Line({
            'x1': (400/rows)*i,
            'y1': 0,
            'x2': (400/rows)*i,
            'y2': 400,
            'style': 'stroke:rgb(0,0,0);stroke-width:1'
        });
        svgParent.appendChild(line[i]);
    }
    
    //display the raw map without calls to the server yet
    minimap.appendChild(svgParent);

    ///populate the map

    //get biome color, is_discovered, is_dj, coordinates, content, foeNbr for the displayed areas only (20*20 with the player at the center)
        //from ajax and json_parse($result[])
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {
          action: 'populateMap'
        }, 
        success: function (response) {
            //create an array with the response, with same size as svgParent
            for (var i = 0; i < rows; i++){
                for (var j = 0; j < columns; j++){
                }
                    
            }
        }

    });

    //link the area data to each svg rectangles with their ids (rect[i] <=> result[i])

    //change the rectangle color according to area_type and is_discovered (rect[i].style=darkgreen, )

    //display the city image if x=0 and y=0 and area=outside (if exist getId "map")

    //display the cave img if cave, the DJ img if DJ

    //display a tooltip for each element w/ resources (count content) and foeNbr

    //hide the side arrows w/ map image OR make them display=none AND add new side arrows for minimap

    //ajax calls on side arrows -> populate the map w/ 20*20 other areas in the selected direction, only if at least 1 area is discovered here, and only if the 20*20 areas exist.
        //if the query returns less than 20*20 results, just load a single row or column (20 results). If again there is less than 20 results. Do not load, as the border of the
        //map is reached.
    
    

    
}
 function SvgContainer(obj) {
    var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    for(var prop in obj) {
        svg.setAttribute(prop, obj[prop]);  
    }
    return svg;
}
 function Line(obj){
    var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
    for(var prop in obj) {
        line.setAttribute(prop, obj[prop]);  
    }
    return line;
}
 function Rect(obj){
    var rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
    //var id=1;
    for(var prop in obj) {
        rect.setAttribute(prop, obj[prop]);  
      //  rect.id= id;
     //  id++;
    }
    return rect;
}
 function displayMapHover(id){
     /*display tooltip containing :
        - foe nbr
        - area is dj
        - area is city
        - area is cave
        - biome type
        - is dis
    
     
    document.getElementById('rect'+id).style.fill = 'rgb(0,0,255)';
}
 function hideMapHover(id){
    document.getElementById('rect'+id).style.fill = 'rgb(0,255,0)';
}
 */
  
//Bigmap functions

/*
function loadBigMap(){
    
    var rows = 20;
    var columns = 20;
    var bigmap = document.getElementById('bigmap');
    
      ///Put the right biome color from DB
    var str = "loadBiomeColors";
    var biomeColors = [];
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
    if (this.readyState === 4 && this.status === 200) {
             biomeColors = this.responseText;
          }
        };
    xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    xhttp.send();
    
      //Remplissage par carrés de map
    for (var i = 1; i <= rows; i++){
      for (var j = 1; j <= columns; j++){
        var ctx=bigmap.getContext("2d");
        ctx.fillRect(floor(bigmap.width/20)*i,floor(bigmap.height/20)*j,1,1);
      }
    }
    
}

 function plantTree(){
     var str = "plantTree";
     var xhttp = new XMLHttpRequest();
     xhttp.onreadystatechange = function() {
     if (this.readyState === 4 && this.status === 200) {
           alert(this.responseText);
           //alert("test");
       }
     };
     xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
     xhttp.send();
 } 
 */


//Movement functions
function moveUp()
{
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveUp"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });

    //var str = "moveUp";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
function moveRight()
{
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveRight"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });


    //     var str = "moveRight";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
function moveDown()
{
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

   $.ajax({
       type: "POST",
       url: "core/ajax/ajax_outside.php",
       data: {param : "moveDown"},
       success: function (response) {
           if(response == "error")
           {
               alert("Une erreur est survenue, merci de recharger la page.");
               //location.reload();
           }
           else
           {
               location.reload();
           }
       },
       complete: function() {
        me.data('requestRunning', false);
    }
   });
   //     var str = "moveDown";
   //var xhttp = new XMLHttpRequest();
   //xhttp.onreadystatechange = function() {
   //if (this.readyState === 4 && this.status === 200) {
   //      //alert(this.responseText);
   //      location.reload();
   //  }
   //};
   //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
   //xhttp.send();
}
 function moveLeft()
 {
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveLeft"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });


    //     var str = "moveLeft";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
 function moveUpLeft()
 {
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveUpLeft"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });

    //     var str = "moveUpLeft";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
 function moveDownLeft()
 {
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveDownLeft"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });

    //     var str = "moveDownLeft";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
 function moveUpRight()
 {
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveUpRight"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });

    //     var str = "moveUpRight";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}
 function moveDownRight()
 {
    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_outside.php",
        data: {param : "moveDownRight"},
        success: function (response) {

            if(response == "error")
            {
                alert("Une erreur est survenue, merci de recharger la page.");
                //location.reload();
            }
            else
            {
                location.reload();
            }

        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });


    //     var str = "moveDownRight";
    //var xhttp = new XMLHttpRequest();
    //xhttp.onreadystatechange = function() {
    //if (this.readyState === 4 && this.status === 200) {
    //      //alert(this.responseText);
    //      location.reload();
    //  }
    //};
    //xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
    //xhttp.send();
}


// ----
// 
//Usage unique en début de server
/*
 function createMap(){
     var str = "createMap";
     var xhttp = new XMLHttpRequest();
     xhttp.onreadystatechange = function() {
     if (this.readyState === 4 && this.status === 200) {
           alert(this.responseText);
       }
     };
     xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
     xhttp.send();
 }
 
 //Test functions
 function reInitializeMap(){
     var str = "reInitializeMap";
     var xhttp = new XMLHttpRequest();
     xhttp.onreadystatechange = function() {
     if (this.readyState === 4 && this.status === 200) {
           //alert(this.responseText);
       }
     };
     xhttp.open("GET", "core/ajax/ajax_outside.php?param=" + str, true);
     xhttp.send();
 }

 */