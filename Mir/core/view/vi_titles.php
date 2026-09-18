<?php
$_SESSION['player_area'] = 'info';

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="main_view">
        <ul>
            <?php foreach($player_titles as $title) {
                echo "<li class='title' id=".$title['id'].">".$title['name']."&nbdp;".$title['decr']."</li>";
            } ?>
        </ul>
    </div>

    <?php include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
	
 <?php 
 include(dirname(__FILE__)."/modules/vi_chatboxes.php");
 //include(dirname(__FILE__)."/modules/vi_footer.php");
 
 
 //jQuery onclick title-id -> session(title)
 ?>	
 
 