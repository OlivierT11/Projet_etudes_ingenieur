<?php
$_SESSION['player_area'] = 'inside';

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="center-content">
		<h3 style="font-weight:bold;">Horloge de l'apocalypse</h3>
        <p>
            Il y a des marques luisantes sur la paroi, entre les plantes grimpantes et la mousse.<br/>
        <p>
        <br><br><br>
        <div id="info-horloge">
            <div id="img-clock"></div>
            <div id="world-quest">
                <h1 style="text-align:center;">Quête Globale</h1>
                <h3 style="text-align:center;">Empêcher l'extinction du monde</h3>
            </div>
            <h3 id="world-count" style="text-align:center;">
                Temps restant 0 : 00 : 00 : 00
                <noscript>Autorisez javascript sur votre navigateur pour afficher l'horloge.</noscript>
            </h3>
            <button id="link-to-city-center" style="margin:0 auto; display:block;" class="btn btn-light border border-dark">Aller vers la place de la ville...</button>
        </div>
        
    </div>
		
</div>
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>