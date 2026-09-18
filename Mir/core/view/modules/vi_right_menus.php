<!DOCTYPE html>

<div id="right_section">
	<div id="council">
		<?php if (!$_SESSION['is_shadow']) {
            echo '<img src="www/img/sun.png" alt="sun">';
        } else {
            echo '<img src="www/img/dark_sun.png" alt="dark_sun">';
        }?>
	</div>
	<div>
        <?php if ($_SESSION['player_area'] == 'inside') { ?>
		<ul>
			<li>
				<a class="menubutton  selected" href="index.php?page=field" target="_self">
					<span class="textlabel">Champs et fermes</span>
				</a>
			</li>
            <!-- AMEL
			<li>
				<a class="menubutton  selected" href="index.php?page=vi_training_field" target="_self">
					<span class="textlabel">Terrain d'entraînement</span>
				</a>
			</li>
            
			<li>
				<a class="menubutton  selected" href="index.php?page=vi_wall" target="_self">
					<span class="textlabel">Muraille</span>
				</a>
			</li>
            -->
			<li>
				<a class="menubutton  selected" href="index.php?page=outside" target="_self">
					<span class="textlabel">Trou dans la muraille</span>
				</a>
			</li>
		</ul>
        <?php } else if ($_SESSION['player_area'] == 'outside') { ?>
        <!-- Réservé pour la vision à distance -->
        <!-- Test : inventaire -->
            <div class="side-invent" id="invent-equip">
                    <div class='item-wrapper spear' id="equip-weapon"></div>
                    <div class='item-wrapper schield' id="equip-schield"></div>
                    <div class='item-wrapper helmet'id="equip-helmet" ></div>
                    <div class='item-wrapper mask'id="equip-mask" ></div>
                    <div class='item-wrapper upper' id="equip-upper" ></div>
                    <div class='item-wrapper lower' id="equip-lower" ></div>
            </div> <br>
            <div class="side-invent" id="invent-side"></div> <br>
            
        <?php } else if ($_SESSION['player_area'] == 'camp') { ?>
        <!-- Réservé pour les statistiques du camp et des alentours -->
        <?php } else if ($_SESSION['player_area'] == 'infos') { ?>
        <!-- Pas de menus si écran d'infos (écran de mort, intro etc)-->
        <?php } else { echo 'erreur: player_area non reconue'; } ?>
	</div>
</div>

	