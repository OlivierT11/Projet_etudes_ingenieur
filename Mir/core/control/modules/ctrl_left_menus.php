<?php

$menuList='';

// If the page needs a return btn (Allies, Statistic, Account), then do not display the session(player_area) menus.
if(isset($_SESSION['page_needs_return']) && $_SESSION['page_needs_return'] == 1){
    unset($_SESSION['page_needs_return']);
    $menuList='
	<li>
        <a href="index.php?page='.$_SESSION['player_area'].'">
            <span class="textlabel">Retour</span>
        </a>
    </li>';

} else {

    if ($_SESSION['player_area'] == "inside"){
        $menuList='
        <!--
        <li>
            <a href="index.php?page=watchtower" >
                <span class="textlabel">Flamme du voyageur</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=armory" >
                <span class="textlabel">Armurerie</span>
            </a>
        </li>
        -->
        <li>
            <a href="index.php?page=inside" >
                <span class="textlabel">Grande place</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=building" >
                <span class="textlabel">Charpenterie</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=treasure_room" >
                <span class="textlabel">Salle aux trésors</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=forge" >
                <span class="textlabel">Ateliers</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=kitchen" >
                <span class="textlabel">Cuisines</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=players" >
                <span class="textlabel">Alliés</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=horlogis" >
                <span class="textlabel">Horloge</span>
            </a>
        </li>';

        $menuList .=
        '<li>
            <a href="index.php?page=farm" >
                <span class="textlabel">Champs</span>
            </a>
        </li>
        <li style="background-color:#e5712d;">
            <button id="btn-go-outside" class="btn-left-menu" style="background-color:#e5712d;">
                <span class="textlabel" style="background-color:#e5712d;">Sortir dehors</span>
            </button>
        </li>';

        if ($_SESSION['abyss_is_open']==1){
            $menuList .= '
            <li style="background-color:#c581ef;">
                <button id="btn-go-inside-abyss" class="btn-left-menu" style="background-color:#c581ef;">
                    <span class="textlabel" style="background-color:#c581ef;">Abysse</span>
                </button>
            </li>';
        }
        
    }

    /********* */
    /* OUTSIDE */
    /********* */

    else if ($_SESSION['player_area'] == "outside"){ 
        if ($_SESSION['player_pos_x'] == 0 and $_SESSION['player_pos_y'] == 0) {

            $menuList .= '
            <li>
                <button id="btn-go-inside-city" class="btn-left-menu">
                    <span class="textlabel">Rejoindre la citadelle</span>
                </button>
            </li>';
        }

        //display harvest btns (if the corresponding resource does not exist on map, the button is deleted from ajax_map.js)
        $menuList .= '
        <li>
            <button type="button" id="harvest-tree" class="btn-left-menu"><strong>Couper un arbre</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-tree1" class="btn-left-menu"><strong>Couper un arbre rare</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-orange" class="btn-left-menu"><strong>Récolter un fruit</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-honey" class="btn-left-menu"><strong>Récolter du miel</strong></button>
        </li>
        <!--<li>
            <button type="button" id="harvest-rock" class="btn-left-menu"><strong>Casser un rocher</strong></button>
        </li>
        -->';

        //Display allies menu
        $menuList .= '
            <li>
                <a href="index.php?page=players" >
                    <span class="textlabel">Alliés</span>
                </a>
            </li>';

        //display camp menu
        if(isset($_SESSION['id_camp']) && $_SESSION['id_camp'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="enter-camp" class="btn-left-menu"><strong>Rejoindre le camp</strong></button>
            </li>';
        } 
        else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){

        }
        else {
            $menuList .='  
            <li>
                <button type="button" id="build-camp" class="btn-left-menu"><strong>Construire un camp</strong></button>
            </li>';
        }

        //display fight menu
        if(isset($foeNbr) && $foeNbr != 0){
            $menuList .='
            <li>
                <button id="btn-battle" class="btn-left-menu">
                    <span class="textlabel">Combattre</span>
                </button>
            </li>';
        }

        //display enter dj menu
        if($_SESSION['id_dj'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="btn-go-inside-dj" class="btn-left-menu"><strong>Entrer dans la caverne</strong></button>
            </li>';
        } 
        //else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){

        //}
    }


    else if ($_SESSION['player_area'] == "camp"){
        $menuList .='
        <li>
            <a href="index.php?page=camp">
                <span class="textlabel">Centre du camp</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=forge">
                <span class="textlabel">Ateliers de fortune</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=kitchen">
                <span class="textlabel">Cuisines improvisées</span>
            </a>
        </li>
        <li>
            <a href="index.php?page=players" >
                <span class="textlabel">Alliés</span>
            </a>
        </li>
        <li>
            <button id="btn-go-outside-camp" class="btn-left-menu">
                <span class="textlabel">Quitter le camp</span>
            </button>
        </li>';
    } 


    else if ($_SESSION['player_area'] == "dj"){ //???
        //display harvest btns (if the corresponding resource does not exist on map, the button is deleted from ajax_map.js)
        $menuList .= '
        <li>
            <button type="button" id="harvest-tree" class="btn-left-menu"><strong>Couper un arbre</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-tree1" class="btn-left-menu"><strong>Couper un arbre rare</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-orange" class="btn-left-menu"><strong>Récolter un fruit</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-honey" class="btn-left-menu"><strong>Réculter du miel</strong></button>
        </li>
        <!--<li>
            <button type="button" id="harvest-rock" class="btn-left-menu"><strong>Casser un rocher</strong></button>
        </li>
        -->';
        
        //Display allies menu
        $menuList .= '
            <li>
                <a href="index.php?page=players" >
                    <span class="textlabel">Alliés</span>
                </a>
            </li>';

        //display camp menu
        if(isset($_SESSION['id_camp']) && $_SESSION['id_camp'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="enter-camp" class="btn-left-menu"><strong>Rejoindre le camp</strong></button>
            </li>';
        } 
        else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){

        }
        else {
            $menuList .='  
            <li>
                <button type="button" id="build-camp" class="btn-left-menu"><strong>Construire un camp</strong></button>
            </li>';
        }

        //display fight menu
        if(isset($foeNbr) && $foeNbr != 0){
            
            $menuList .='
            <li>
                <button id="btn-battle" class="btn-left-menu">
                    <span class="textlabel">Combattre</span>
                </button>
            </li>';

        }

        if (isset($_SESSION['dj_x']) && ($_SESSION['player_pos_x'] == $_SESSION['dj_x']) && ($_SESSION['player_pos_y'] == $_SESSION['dj_y'])) {

            $menuList .= '
            <li>
                <button id="btn-go-outside-dj" class="btn-left-menu">
                    <span class="textlabel">Sortir de la caverne</span>
                </button>
            </li>';

        }
        if (isset($_SESSION['dj2_x']) && $_SESSION['player_pos_x'] == $_SESSION['dj2_x'] && $_SESSION['player_pos_y'] == $_SESSION['dj2_y']) {

            $menuList .= '
            <li>
                <button id="btn-go-inside-dj2" class="btn-left-menu">
                    <span class="textlabel">Atteindre le fond du gouffre</span>
                </button>
            </li>';

        }

    } 


    else if ($_SESSION['player_area'] == "abyss"){ 
        if ($_SESSION['player_pos_x'] == $_SESSION['abyss_x'] && $_SESSION['player_pos_y'] == $_SESSION['abyss_y']) {

            $menuList .= '
            <li>
                <button id="btn-go-inside-city" class="btn-left-menu">
                    <span class="textlabel">Sortir de l\'abysse</span>
                </button>
            </li>';
        }

        //display harvest btns (if the corresponding resource does not exist on map, the button is deleted from ajax_map.js)
        $menuList .= '
        <li>
            <button type="button" id="harvest-tree" class="btn-left-menu"><strong>Couper un arbre</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-tree1" class="btn-left-menu"><strong>Couper un arbre rare</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-orange" class="btn-left-menu"><strong>Récolter un fruit</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-honey" class="btn-left-menu"><strong>Réculter du miel</strong></button>
        </li>
        <!--<li>
            <button type="button" id="harvest-rock" class="btn-left-menu"><strong>Casser un rocher</strong></button>
        </li>
        -->';

        //Display allies menu
        $menuList .= '
            <li>
                <a href="index.php?page=players" >
                    <span class="textlabel">Alliés</span>
                </a>
            </li>';

        //display camp menu
        if(isset($_SESSION['id_camp']) && $_SESSION['id_camp'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="enter-camp" class="btn-left-menu"><strong>Rejoindre le camp</strong></button>
            </li>';
        } 
        else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){

        }
        else {
            $menuList .='  
            <li>
                <button type="button" id="build-camp" class="btn-left-menu"><strong>Construire un camp</strong></button>
            </li>';
        }

        //display fight menu
        if(isset($foeNbr) && $foeNbr != 0){
            $menuList .='
            <li>
                <button id="btn-battle" class="btn-left-menu">
                    <span class="textlabel">Combattre</span>
                </button>
            </li>';
        }

        //display enter A2 menu
        /*
        if($_SESSION['id_dj'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="btn-go-inside-dj">Entrer dans la caverne</button>
            </li>';
        } 
        */
        //else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){

        //}
        if ($_SESSION['player_pos_x'] == $_SESSION['hell_x'] && $_SESSION['player_pos_y'] == $_SESSION['hell_y']) {
            $menuList .='
            <li>
                <button id="btn-go-inside-hell" class="btn-left-menu">
                    <span class="textlabel">Descendre en Enfer</span>
                </button>
            </li>';
        }
    }


    else if ($_SESSION['player_area'] == "hell"){ 
        if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0) { //hell center

            $menuList .= '
            <li>
                <button id="btn-go-outside-hell" class="btn-left-menu">
                    <span class="textlabel">Grimper hors des Enfers</span>
                </button>
            </li>';
        }

        //Display allies menu
        $menuList .= '
            <li>
                <a href="index.php?page=players" >
                    <span class="textlabel">Alliés</span>
                </a>
            </li>';

        //display camp menu
        if(isset($_SESSION['id_camp']) && $_SESSION['id_camp'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="enter-camp" class="btn-left-menu"><strong>Rejoindre le camp</strong></button>
            </li>';
        } 

        //display fight menu
        if(isset($foeNbr) && $foeNbr != 0){
            $menuList .='
            <li>
                <button id="btn-battle" class="btn-left-menu">
                    <span class="textlabel">Combattre</span>
                </button>
            </li>';
        }

    }
        

    else if ($_SESSION['player_area'] == "dj2"){ 
        if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0) {

            $menuList .= '
            <li>
                <button id="btn-go-outside-dj2" class="btn-left-menu">
                    <span class="textlabel">Remonter dans les cavernes</span>
                </button>
            </li>';
        }

        //display harvest btns (if the corresponding resource does not exist on map, the button is deleted from ajax_map.js)
        $menuList .= '
        <li>
            <button type="button" id="harvest-tree" class="btn-left-menu"><strong>Couper un arbre</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-tree1" class="btn-left-menu"><strong>Couper un arbre rare</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-orange" class="btn-left-menu"><strong>Récolter un fruit</strong></button>
        </li>
        <li>
            <button type="button" id="harvest-honey" class="btn-left-menu"><strong>Réculter du miel</strong></button>
        </li>
        <!--<li>
            <button type="button" id="harvest-rock" class="btn-left-menu"><strong>Casser un rocher</strong></button>
        </li>
        -->';

        //Display allies menu
        $menuList .= '
            <li>
                <a href="index.php?page=players" >
                    <span class="textlabel">Alliés</span>
                </a>
            </li>';

        //display camp menu
        if(isset($_SESSION['id_camp']) && $_SESSION['id_camp'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="enter-camp" class="btn-left-menu"><strong>Rejoindre le camp</strong></button>
            </li>';
        } 
        else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){}
        else {
            $menuList .='  
            <li>
                <button type="button" id="build-camp" class="btn-left-menu"><strong>Construire un camp</strong></button>
            </li>';
        }

        /*
        //display fight menu
        if(isset($foeNbr) && $foeNbr != 0){
            $menuList .='
            <li>
                <button id="btn-battle" class="btn-left-menu">
                    <span class="textlabel">Combattre</span>
                </button>
            </li>';
        }*/

        //display enter A2 menu
        /*
        if($_SESSION['id_dj'] != 0){
            $menuList .='  
            <li>
                <button type="button" id="btn-go-inside-dj" class="btn-left-menu">Entrer dans la caverne</button>
            </li>';
        } 
        */
        //else if ($_SESSION['player_pos_x'] == 0 && $_SESSION['player_pos_y'] == 0){

        //}
        
    }


    else { 
        echo 'Erreur: player_area invalide ou inexistant'; 
    }
}