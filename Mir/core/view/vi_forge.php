<?php
if (!$_SESSION){
    session_start();
}
?><!DOCTYPE html>

<?php
include(dirname(__FILE__)."/modules/vi_head.php");
?>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
        <h3 style="font-weight:bold;">Ateliers et forge</h3>

        <!-- Sets the area description according to the area -->
        <?php if($_SESSION['player_area']=='inside'){
            echo '<p>
                    Dans ce bâtiment de fortune, situé en hauteur, vous et vos alliés avez disposé des tables faisant office d\'établis. <br/>
                    Vous avez rassemblé tout ce qui pouvait servir d\'outils et avez improvisé une petite forge. <br/>
                    La qualité des objets que vous fabriquez dépend de la qualité des matériaux et de votre propre compétence. <br/>
                    <strong>Utilisez un matériau (bois) via clic ou glisser-déposer dans les cases vertes.</strong> N\'oubliez pas de donner vos équipements à la ville.
                </p>';
        } else { // camp
            echo '<p>
                    Dans un bâtiment de fortune, vous et vos alliés avez disposé des tables faisant office d\'établis. <br/>
                    Vous avez fait venir des outils de la Citadelle et avez fabriqué une petite forge. <br/>
                    L\'endroit ayant été improvisé avec les moyens du bord, les améliorations de bâtiments de la Citadelle ne marcheront pas ici. <br/>
                </p>';
        } ?>
        
        <div id="wrapper-forge">
            <!-- CRAFT-->
            <div id="wrapper-craft-zone">
                
                <div id="wrapper-craft-mats">
                    <div id="craft-mat-1" class="wrapper-craft-mat" ondrop="dropCraftMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <div id="craft-mat-2" class="wrapper-craft-mat" ondrop="dropCraftMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <br/>
                    <div id="craft-mat-3" class="wrapper-craft-mat" ondrop="dropCraftMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <div id="craft-mat-4" class="wrapper-craft-mat" ondrop="dropCraftMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <!-- <div id="craft-mat-5" class="wrapper-craft-mat" ondrop="dropCraftMatdIn(event)" ondragover="allowDrop(event)" ></div> -->
                </div>

                <div id="wrapper-craft-btns">
                    <select id="item-craft-selector">
                        <option value="upper-armor">Armure haute</option>
                        <option value="lower-armor">Armure basse</option>
                        <option value="helmet">Casque</option>
                        <option value="mask">Masque</option>
                        <option value="shield">Bouclier</option>
                        <option value="spear">Lance</option>
                    </select>
                    <br><br>
                    <div id="wrapper-craft-btn">
                        <button id="btn-craft" class="btn btn-light border border-dark">Fabriquer</button>
                    </div>
                </div>
                <div class="clear-float"></div>
            </div>

            <div id="wrapper-invent-player-equip">
                <!-- EQUIPEMENT-->
                <div id="wrapper-invent-for-tooltip">
                    <div id="wrapper-equip">
                        <div id="equip-upper" title="Armure haute" ondrop="dropUpperIn(event, this)" ondragover="allowDrop(event)" ><?php echo $upperEquiped; ?></div>
                        <div id="equip-lower" title="Armure basse" ondrop="dropLowerIn(event, this)" ondragover="allowDrop(event)" ><?php echo $lowerEquiped; ?></div>
                        <div id="equip-helmet" title="Casque" ondrop="dropHelmetIn(event, this)" ondragover="allowDrop(event)" ><?php echo $helmetEquiped; ?></div>
                        <div id="equip-mask" title="Masque" ondrop="dropMaskIn(event, this)" ondragover="allowDrop(event)" ><?php echo $maskEquiped; ?></div>
                        <div id="equip-shield" title="Bouclier" ondrop="dropShieldIn(event, this)" ondragover="allowDrop(event)" ><?php echo $shieldEquiped; ?></div>
                        <div id="equip-spear" title="Lance" ondrop="dropSpearIn(event, this)" ondragover="allowDrop(event)" ><?php echo $spearEquiped; ?></div>
                        <!-- <div id="equip-label">Equipement</div> -->
                    </div>
                    <hr>
                    <div id="invent-player" ondrop="drop(event, this)" ondragover="allowDrop(event)" ><?php echo $inventPlayer; ?></div>
                </div>
            </div>
        </div> <!-- wrapper-forge -->
        <div class="clear-float"></div>

        <?php if($_SESSION['player_area']=='inside'){
            echo '<button id="give-all-to-city" class="btn btn-success">Tout offir à la ville</button><br/>';
        } else { // camp
            echo '<button id="give-all-to-city" class="btn btn-success">Vider son sac dans le camp</button><br/>';
        } ?>


        <div id="invent-city" ondrop="drop(event, this)" ondragover="allowDrop(event)" ><?php echo $inventCity; ?></div>
        
        <div id="actions-wrapper">
            <ul>
                <?php echo $actionsListHTML; ?>
            </ul>
        </div>
        <!--<div id="follow-items-wrapper">
            <div id="follow-items-content">
                <ul>
                <?php //echo $itemsHistory; ?>
                </ul>
            </div>
        </div>
        <div class="clear-float"></div>
        -->

        
    </div>

    <?php //include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
	
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>