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
        <h3 style="font-weight:bold;">Cuisines</h3>

        <!-- Sets the area description according to the area -->
        <?php if($_SESSION['player_area']=='inside'){
            echo '<p>
                    Ici sont cuisinés les ingrédients cultivés à la ferme ou récoltés dehors.<br/>
                    Des panniers de fruits sont disposés autour d\'anciens fourneaux, à côté de bols de miel.<br/>
                    Choisissez la recette (menu Aide en haut) et les ingrédients dans votre inventaire.<br/>
                    <strong>Utilisez un matériau via clic ou glisser-déposer dans les cases roses.</strong> N\'oubliez pas de donner vos plats à la ville.
                </p>';
        } else { // camp
            echo '<p>
                    Vous pouvez cuisiner les ingrédients récoltés autour du camp, ou importés depuis les champs de la Citadelle.<br/>
                    Les aménagements étant sommaires, vous ne bénéficiez pas des bonus de bâtiments, mais pouvez produire des rations rapidement.<br/>
                </p>';
        } ?>

        <div id="wrapper-kitchen">
            <!-- CRAFT-->
            <div id="wrapper-cook-zone">
                
                <div id="wrapper-cook-mats">
                    <div id="craft-mat-1" class="wrapper-cook-mat" ondrop="dropCookMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <div id="craft-mat-2" class="wrapper-cook-mat" ondrop="dropCookMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <br/>
                    <div id="craft-mat-3" class="wrapper-cook-mat" ondrop="dropCookMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <div id="craft-mat-4" class="wrapper-cook-mat" ondrop="dropCookMatdIn(event, this)" ondragover="allowDrop(event)" ></div>
                    <!-- <div id="craft-mat-5" class="wrapper-craft-mat" ondrop="dropCraftMatdIn(event)" ondragover="allowDrop(event)" ></div> -->
                </div>

                <div id="wrapper-craft-btns">
                    <select id="item-craft-selector">
                        <option value="baked_orange">Tarte aux fruits</option>
                        <option value="baked_honey">Miel raffiné</option>
                        <option value="cake">Gâteau</option>
                    </select>
                    <br><br>
                    <div id="wrapper-cook-btn">
                        <button id="btn-craft" class="btn btn-light border border-dark">Fabriquer</button>
                    </div>
                </div>
            </div>

            <div id="wrapper-invent-player-equip">
                <div id="wrapper-invent-for-tooltip">
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
		
</div>
	
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>