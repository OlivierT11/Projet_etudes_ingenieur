<?php
include(dirname(__FILE__)."/modules/vi_head.php");

// Adds a "return" btn in left menu button intead of the actual btns of the area (is unset right after use)
$_SESSION['page_needs_return'] = 1;

?><!DOCTYPE html>

<div id="content">
        
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
        <div id="area-description-wrapper">
            <h3 style="font-weight:bold;">Liste des alliés proches</h3>
            <p>
                <?php
                // Define the screen message according to area
                switch($_SESSION['player_area']){
                    case 'inside':
                        echo "Liste des alliés avec vous dans la citadelle.";
                        break;
                    case 'camp':
                        echo "Liste des alliés avec vous dans ce campement.";
                        break;
                    //if outside/dj/dj2/abyss/hell or any other area in session(player_area)
                    default:
                        echo "Liste des alliés autour de vous (1km dans chaque direction).";
                }
                
                ?>
            </p>
        </div>
        <div id="players-wrapper">
            <ul>
                <?php echo $playerListHTML; ?>
            </ul>
        </div>
        
        <hr>
        <h3 style="font-weight:bold;">Liste de tous les alliés</h3>
        <div id="players-wrapper-general">
            <ul>
                <?php echo $playerListGeneralHTML; ?>
            </ul>
        </div>
    </div>

</div>    
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>