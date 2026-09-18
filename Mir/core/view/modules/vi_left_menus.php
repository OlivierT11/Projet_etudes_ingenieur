<!DOCTYPE html>

<?php
include(dirname(dirname(dirname(__FILE__)))."/control/modules/ctrl_left_menus.php");?>

<div id="left-menu">
	<div id="menubutton-left">
		<ul>
            <?php echo $menuList; ?>
		</ul>
	</div>
	<?php include(dirname(__FILE__)."/vi_life_bars.php"); ?>
</div>