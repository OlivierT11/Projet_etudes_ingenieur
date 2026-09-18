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
        <h3 style="font-weight:bold;">Champs et fermes</h3>
        
        <?php if($_SESSION['era']=='red'){
            echo '<p>
                    La terre devient rouge. Les cultures commencent à mourir, pour une raison inconnue.<br/>
                </p>';
        } else if ($_SESSION['era']=='dark') {
            echo '<p>
                    La terre est noire et désertique. Plus rien ne pousse, à part quelques plantations restées indemnes.<br/>
                </p>';
        } else {
            echo '<p>
                    De vastes forêts s\'étendent à l\'extérieur des murailles, autour de la montagne. <br/>
                    Avec un peu de travail, ces étendues deviendront des champs fertiles. <br/>
                    Le terrain est réparti en parcelles identiques entre les habitants.<br/>
                    Toute nourriture plantée et arrosée mettra <strong>deux</strong> jours pour arriver à maturité. Soyez sûr(e) de cultiver ce qu\'il faut. <br/>
                </p>';
        } ?>
        
        <div id="wrapper-forge">
            <!-- CRAFT-->
            <div id="wrapper-craft-zone">
                <p>
                    <strong>Actions</strong> 
                </p>
                <div class="btn-group-vertical">
                    <button id="btn-plant-orange-farm" type="button" class="btn btn-light">Planter un fruit (<?php echo $orangeCount; ?>)</button>
                    <button id="btn-plant-honey-farm" type="button" class="btn btn-light">Fabriquer une ruche (<?php echo $honeyCount; ?>)</button>
                    <button id="btn-harvest-orange-farm" type="button" class="btn btn-light">Récolter des fruits (<?php echo $orangeReadyCount; ?>)</button>
                    <button id="btn-harvest-honey-farm" type="button" class="btn btn-light">Récolter du miel (<?php echo $honeyReadyCount; ?>)</button>
                    <button id="btn-water-all" type="button" class="btn btn-light">Arroser tout (<?php echo $isNotWatered; ?>)</button>
                </div> 

            </div>

            <div id="wrapper-invent-player-equip">
                <div id="wrapper-invent-for-tooltip">
                    <div id="invent-player"><?php echo $inventPlayer; ?></div>
                </div>
            </div>
        </div> <!-- wrapper-forge -->

        <div class="clear-float"></div>

        <div id="invent-farm"><?php echo $inventFarm; ?></div>

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