<?php
$_SESSION['player_area'] = 'inside';
$_SESSION['player_sub_area'] = 'inside';
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
        
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
        <div id="area-description-wrapper">
            <h3 style="font-weight:bold;">Grande place</h3>
            <p>
                Vous êtes au point le plus haut de la Citadelle. C'est l'endroit le plus sûr, qui offre la meilleure vue sur les environs.<br/>
                Les maisons en pierre descendent en escalier jusqu'aux forêts, en bas de la montagne.<br/>
                Un feu brûle dans une tour et guide les explorateurs dans ce vaste monde.<br/>
                Au loin, vous appercevez la muraille de pierre, qui fait le tour de la Citadelle.
            </p>
        </div>
        <div id="major-news-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Nouveautés de la ville</h5>
            <ul>
                <?php echo $majorNewsListHTML; ?>
            </ul>
        </div>
        <div id="minor-news-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Actions des alliés</h5>
            <ul>
                <?php echo $minorNewsListHTML; ?>
            </ul>
        </div>
        <div id="observation-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Observations</h5>
            <?php if($_SESSION['era']=='red'){
                echo "<p>
                        Le ciel est <span style='font-weight:bold; color:red'>rouge</span>.<br/>
                        Autour de la citadelle, il y a des <span style='font-weight:bold; color:red;'>groupes d'Ombres</span>.<br/>
                        Les <span style='font-weight:bold; color:red;'>trainées de fumée noire</span> ont grossit et forment des colonnes.<br/>
                        L'activité des Ombres autour de la Citadelle est <span style='font-weight:bold; color:darkred'>forte</span>.
                    </p>";
            } else if ($_SESSION['era']=='dark') {
                echo "<p>
                        Le ciel est <span style='font-weight:bold;'>noir, sans soleil</span>.<br/>
                        Autour de la citadelle, il y a des <span style='font-weight:bold; color:red;'>hordes d'Ombres</span>.<br/>
                        Tout est noir, on y voit plus rien.<br/>
                        L'activité des Ombres autour de la Citadelle est <span style='font-weight:bold; color:red'>extrême</span>.
                    </p>";
            } else {
                echo "<p>
                    Le ciel est <span style='font-weight:bold; color:blue'>bleu, sans nuages</span>.<br/>
                    Autour de la citadelle, il y a des <span style='font-weight:bold; color:green;'>forêts de bois précieux</span>.<br/>
                    Par endroits, des <span style='font-weight:bold; color:red;'>trainées de fumée noire</span> montent vers le ciel.<br/>
                    L'activité des Ombres autour de la Citadelle est <span style='font-weight:bold; color:blue'>faible à nulle</span>.
                </p>";
            }?>
            
        </div>
        <!--
        <div id="weather-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Météo</h5>
            <p>Il n'y a <span style="color:green;">pas</span> de tempête en vue.</p>
        </div>
        -->

    </div>
    
    <!-- Right menus (deleted)
    <?php //include(dirname(__FILE__)."/modules/vi_right_menus.php");
    ?>
    -->
         
</div>    
 
<?php ////include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>