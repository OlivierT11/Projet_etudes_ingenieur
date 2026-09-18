<?php
$_SESSION['player_area'] = 'info';

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="main_view">
		
    </div>

    <?php include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
	
 <?php 
 include(dirname(__FILE__)."/modules/vi_chatboxes.php");
 //include(dirname(__FILE__)."/modules/vi_footer.php");
 ?>	