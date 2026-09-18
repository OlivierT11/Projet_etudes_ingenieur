<?php
$_SESSION['player_area'] = 'dj2';
$_SESSION['player_sub_area'] = 'dj2'; //to put on map loading if several biomes in hell are added
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
	
		<h3 style="font-weight:bold;"> Vous atteignez une vaste salle souterraine</h3>
		<p> 
            Elle est remplie d'arbres, à demi fossilisés, qui semblent très anciens. <br/>
            Ce bois est suffisament dur pour servir de charpente et résister aux orages. <br/>
            Les Ombres ici sont différentes, recouvertes de métal. Faites de la place dans le sac avant d'en affronter.<br/>
        </p>
        <div >
            <p style="font-weight:bold;"> Position: <span id="displayPosX"></span> / <span id="displayPosY"></span></p>
        </div>
        <div id="ally-wrapper" style="float:left; margin-right:30px;">
            <p id="allyNbr"> Alliés: <span id="displayAllyNbr"></span></p>
        </div>
        <div id="fight-wrapper" style="float:left;">
            <p id="shadowNbr"> Ombres: <span id="displayShadowNbr"></span><span> Niv. <?php echo $foeLvl; ?></span><span id="life" style="display:none;"> Vie: </span></p>
            <div id="foe-life-bar-full" style="display:none;"> 
                <div id="foe-life-bar-current" style="display:none;"></div>
            </div>
        </div>
        <div class="clear-float"></div>
        
        <div id="main-map">
                           
            <!-- Make div elements float over a canvas : https://stackoverflow.com/questions/5763911/placing-a-div-within-a-canvas#5764395 -->
            <canvas id="dj2" width="500" height="500"></canvas>

            <div id="up-wall" style="display:none"></div>
            <div id="left-wall" style="display:none"></div>
            <div id="down-wall" style="display:none"></div>
            <div id="right-wall" style="display:none"></div>

            <div id="up-right-wall" style="display:none"></div>
            <div id="up-left-wall" style="display:none"></div>
            <div id="down-left-wall" style="display:none"></div>
            <div id="down-right-wall" style="display:none"></div>
            
            <div id="up-arrow" class="flex-container-row" onclick="moveUp()">
                <div id="foe-nbr-up" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-up" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="right-arrow" class="flex-container-column" onclick="moveRight()">
                <div id="foe-nbr-right" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-right" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="down-arrow" class="flex-container-row"  onclick="moveDown()">
                <div id="foe-nbr-down" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-down" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="left-arrow" class="flex-container-column"  onclick="moveLeft()">
                <div id="foe-nbr-left" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-left" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="up-right-arrow" class="flex-container"  onclick="moveUpRight()">
                <div id="foe-nbr-up-right" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-up-right" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="down-right-arrow" class="flex-container"  onclick="moveDownRight()">
                <div id="foe-nbr-down-right" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-down-right" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="down-left-arrow" class="flex-container"  onclick="moveDownLeft()">
                <div id="foe-nbr-down-left" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-down-left" class="ally-nbr" title="Alliés"></div>
            </div>
            <div id="up-left-arrow" class="flex-container"  onclick="moveUpLeft()">
                <div id="foe-nbr-up-left" class="foe-nbr" title="Ombres"></div>
                <div id="ally-nbr-up-left" class="ally-nbr" title="Alliés"></div>
            </div>

            <div id="up-right-arrow" onclick="moveUpRight()"></div>
            <div id="down-right-arrow" onclick="moveDownRight()"></div>
            <div id="down-left-arrow" onclick="moveDownLeft()"></div>
            <div id="up-left-arrow" onclick="moveUpLeft()"></div>
            
        </div>

        <div id="player-invent-side">
            <div id="wrapper-invent-player-equip-side">
                <!-- EQUIPEMENT-->
                <div id="wrapper-invent-for-tooltip">
                    <div id="wrapper-equip">
                    <div id="equip-shield" title="Bouclier" ondrop="dropShieldIn(event, this)" ondragover="allowDrop(event)" ><?php echo $shieldEquiped; ?></div>
                        <div id="equip-upper" title="Armure Haute" ondrop="dropUpperIn(event, this)" ondragover="allowDrop(event)" ><?php echo $upperEquiped; ?></div>
                        <div id="equip-lower" title="Armure Basse" ondrop="dropLowerIn(event, this)" ondragover="allowDrop(event)" ><?php echo $lowerEquiped; ?></div>
                        <div id="equip-helmet" title="Casque" ondrop="dropHelmetIn(event, this)" ondragover="allowDrop(event)" ><?php echo $helmetEquiped; ?></div>
                        <div id="equip-mask" title="Masque" ondrop="dropMaskIn(event, this)" ondragover="allowDrop(event)" ><?php echo $maskEquiped; ?></div>
                        <div id="equip-spear" title="Lance" ondrop="dropSpearIn(event, this)" ondragover="allowDrop(event)" ><?php echo $spearEquiped; ?></div>
                        <!-- <div id="equip-label">Equipement</div> -->
                    </div>
                    <hr>
                    <div id="invent-player" ondrop="drop(event, this)" ondragover="allowDrop(event)" ><?php echo $inventPlayer; ?></div>
                </div>
            </div>
        </div>
        <div class="clear-float"></div>
    </div>

    <?php //include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>
<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>