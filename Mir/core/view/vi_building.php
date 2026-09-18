<?php
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
        <h3 style="font-weight:bold;"> Architecture </h3>
        <p>
            Ici sont choisies les prochaines constructions.<br/>
            Des bûches de bois précieux sont empillées un peu partout, et les murs sont recourverts de plans et de dessins.<br/>
            Votez pour le bâtiment que vous aimeriez voir construit.<br/>
            Chaque soir à minuit, les bâtiments avec le plus de votes seront construits si les ressources dans les coffres sont suffisantes.<br/>
            <strong>Chaque bâtiment coûte 50 bois rares et 50 métaux.</strong>
        </p>

        <p>Bois rare en stock : <strong><?php echo $woodNbr; ?></strong></p>
        <p>Métal en stock : <strong><?php echo $metalNbr; ?></strong></p>
		<div id="buildings">
            <table id="building_list">
                <tr>
                    <th>Catégorie</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Construire</th>
                    <th>Votes</th>
                </tr>
                <?php echo $buildingListHTML; ?>
            </table>
        </div>
        <div id="minor-news-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Archives de construction</h5>
            <ul>
                <?php echo $newsListHTML; ?>
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