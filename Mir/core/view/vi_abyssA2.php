<?php
$_SESSION['player_area'] = 'outside';
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="main_view">
		
			<h3> Map </h3>
        
            <div >
                <p> Position X: <span id="displayPosX"></span></p>
            </div>
            <div>
                <p> Position Y: <span id="displayPosY"></span></p>
            </div>
            
            <div id="main-map">
                               
                <!-- Make div elements float over a canvas : https://stackoverflow.com/questions/5763911/placing-a-div-within-a-canvas#5764395 -->
                <canvas id="abyss" width="500" height="500"></canvas>
                <div class="side-map" id="up-arrow" onclick="moveUp()"></div>
                    <div id="up-foe-img"></div>
                    <div id="up-foe"></div>
                    <div id="up-player-img"></div>
                    <div id="up-player"></div>
                    <div id="up-leader-img"></div>
                    <div id="up-leader"></div>
                <div id="right-arrow" onclick="moveRight()"></div>
                    
                <div id="down-arrow" onclick="moveDown()"></div>
                <div id="left-arrow" onclick="moveLeft()"></div>
                <div id="up-right-arrow" onclick="moveUpRight()"></div>
                <div id="down-right-arrow" onclick="moveDownRight()"></div>
                <div id="down-left-arrow" onclick="moveDownLeft()"></div>
                <div id="up-left-arrow" onclick="moveUpLeft()"></div>
                
                <!-- Masque les flêches si on se trouve à une limite x ou y de la carte -->
                
                <div id="minimap" style="display: none;">
                </div><div id="displayMiniMap" onclick="displayMiniMap()"></div>
                <div id="bigmap" style="display: none;">
                </div><div id="displayBigMap" onclick="displayBigMap()"></div>
            </div>
            
    </div>

    <?php include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
<script type="text/javascript" src="www/js/ajax_map.js"></script>
 <?php 
 include(dirname(__FILE__)."/modules/vi_chatboxes.php");
 //include(dirname(__FILE__)."/modules/vi_footer.php");
 ?>	