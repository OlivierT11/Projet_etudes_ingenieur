<?php
$_SESSION['player_area'] = 'inside';

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="main_view">
		<h3>Flamme du voyageur</h3>
        <br/>
        <p>En constuction</p><br/>
        <p>Ce bâtiment permettra d'observer les régions alentours. Il évoluera en
        Phare, rendant la ville visible de loin quand la Nuit viendra.</p><br/>
        <p>Cette section va évoluer, n'hésitez pas à suivre les infos sur l'écran d'accueil ou le forum :)</p><br/>
        <p>Permettra de visualiser la météo et les orages pour préparer les expéditions.</p><br/>
        <p>Permettra de voir les Ombres Titanesques à distance.</p><br/>
        <p>Permettra de repérer les puits de magie.</p><br/>
        <p>Permettra de suivre à distance les découvertes des explorateurs.</p><br/>
        <p>Permettra de voir les montagnes, forêts, camps et donjons à distance de vue.</p><br/>
        <p>Permettra de voir les événements aléatoire.</p><br/>
        <p>Pour voir plus loin, améliorez le bâtiment depuis l'onglet Architecture.</p><br/>
       
    </div>

    <?php include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
	
 <?php 
 include(dirname(__FILE__)."/modules/vi_chatboxes.php");
 //include(dirname(__FILE__)."/modules/vi_footer.php");
 ?>	