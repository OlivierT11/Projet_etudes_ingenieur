<?php
include(dirname(__FILE__)."/modules/vi_head.php");

// Adds a "return" btn in left menu button intead of the actual btns of the area (is unset right after use)
//$_SESSION['page_needs_return'] = 1;

?><!DOCTYPE html>

<div id="content">
        
    <?php //include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content-info">
        <h3 style="font-weight:bold; text-align:center;">Statistiques de jeu</h3>
        <div id="player-local-statistic-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Vos statistiques pour la ville en cours</h5>
            <ul>
                <?php echo $localPlayerStatisticsHTML; ?>
            </ul>
        </div>
        <div id="city-local-statistic-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Les statistiques de la ville en cours</h5>
            <ul>
                <?php 
                    //echo $localCityStatistics; 
                    echo "En cours de développement";
                ?>
            </ul>
        </div>
        <div id="global-statistic-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Vos statistiques générales</h5>
            <ul>
                <?php 
                    //echo $globalPlayerStatistics; 
                    echo "En cours de développement";
                ?>
            </ul>
        </div>
    </div>
    
    <!-- Right menus (aborted)
    <?php //include(dirname(__FILE__)."/modules/vi_right_menus.php");
    ?>
    -->
         
</div>    
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>