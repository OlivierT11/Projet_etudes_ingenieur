<?php
$_SESSION['player_area'] = 'inside';
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
            <h3 style="font-weight:bold;"> Salle aux trésors </h3>
            <p>
                Vous entrez dans une grande salle. Des matériaux et aliments sont empilées un peu partout.<br/>
                Vous pouvez y stocker et partager vos productions.<br/>
                L'endroit semble s'étendre vers le coeur de la montagne, mais des éboulements ont bouché les tunnels.<br/>
                Des aménagements permettront d'augmenter la place disponible et la durée de conservation des objets stockés.<br/>
                Place occupée : 
                <strong>
                    <?php 
                        //echo ($counterCityItems . '/' . $maxInventCity); 
                        echo $counterCityItems; 
                        ?>
                </strong>
            </p>

            <div id="wrapper-invent-player-for-treasure-room">
                <div id="wrapper-invent-for-tooltip">
                    <div id="invent-player" ondrop="drop(event)" ondragover="allowDrop(event)" ><?php echo $inventPlayer; ?></div>
                </div>
            </div>

            <button id="give-all-to-city" class="btn btn-success">Tout offir à la ville</button><br/>

            
                    <div id="invent-city" ondrop="drop(event, this)" ondragover="allowDrop(event)" ><?php echo $inventCity; ?></div>
                
		</div>
		
</div>

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>