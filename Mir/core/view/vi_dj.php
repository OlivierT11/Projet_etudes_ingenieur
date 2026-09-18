<?php
$_SESSION['player_area'] = 'dj';
$_SESSION['player_sub_area'] = 'dj'; //to put on map loading if several biomes in hell are added
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
	
		<h3 style="font-weight:bold;"> Vous êtes dans une caverne </h3>
        <p>
            Vous entrez dans un dédale de tunnels qui semblent avoir été aménagés il y a longtemps. <br/>
            Des éboulements ont rempli les chemins de rochers, et certaines voies sont bloquées.<br/>
            Les Ombres sont nombreuses ici, et la lumière de votre lanterne vacille ...
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
            <canvas id="dj" width="500" height="500"></canvas>

            <div id="up-right-arrow" onclick="moveUpRight()"></div>
            <div id="down-right-arrow" onclick="moveDownRight()"></div>
            <div id="down-left-arrow" onclick="moveDownLeft()"></div>
            <div id="up-left-arrow" onclick="moveUpLeft()"></div>

            <div id="up-wall" style="display:none"></div>
            <div id="left-wall" style="display:none"></div>
            <div id="down-wall" style="display:none"></div>
            <div id="right-wall" style="display:none"></div>
            
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
            
            <!-- Masque les flêches si on se trouve à une limite x ou y de la carte -->
            
            <div id="minimap" style="display: none;"></div>
            <div id="displayMiniMap"></div>
            <div id="minimap-side-north" style="display: none;"></div>
            <div id="minimap-side-east" style="display: none;"></div>
            <div id="minimap-side-south" style="display: none;"></div>
            <div id="minimap-side-west" style="display: none;"></div>
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

        <div id="actions-wrapper-for-map">
            <ul>
                <?php echo $actionsListHTML; ?>
            </ul>
        </div>
    </div>

    <?php //include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>
<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>