<!DOCTYPE html>

<?php
include(dirname(dirname(dirname(__FILE__)))."/control/modules/ctrl_forum_left_menu.php");?>

<div id="left-menu">
	<div id="menubutton-left">
        <ul>
            <li>
                <a href="index.php?page=<?php echo $_SESSION['player_area']; ?>">
                    <span class="textlabel">Retour</span>
                </a>
            </li>
        </ul>
        <br/>
        Catégories :
        <br/>
		<ul>
            <?php echo $categoriesList; ?>
		</ul>
	</div>
</div>