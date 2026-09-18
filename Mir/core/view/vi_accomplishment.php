<?php
$_SESSION['player_area'] = 'info';

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
		
    <div id="main_view">
		
		<h3> Accomplissements collectifs </h3>
        
        <br>
        <div class="invent">
            <ul>  
                <?php foreach ($news_in_city as $new) {
                            echo'<li>'
                            .$new['date'].'  '
                            .$new['player'].'  '
                            .$new['descr'].'
                            <div class="thumb" onclick="addThumb()"><img src="../www/img/thumb" alt="thumb_up"></div>
                            </li>';
                        }
                        ?>
            </ul>
        </div><br>

    </div>

    <?php include(dirname(__FILE__)."/modules/vi_right_menus.php"); ?>
		
</div>
	
 <?php 
 include(dirname(__FILE__)."/modules/vi_chatboxes.php");
 //include(dirname(__FILE__)."/modules/vi_footer.php");
 ?>