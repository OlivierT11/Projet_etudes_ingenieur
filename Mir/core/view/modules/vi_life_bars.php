<!DOCTYPE html>
<?php 
include(dirname(dirname(dirname(__FILE__)))."/control/modules/ctrl_life_bars.php");
?>
<div id="life_bars">
    <div id="food_bar">
        <div id="food_text">Nourriture</div>
        <div id="food_bar_wrapper">
            <div id="food_icon_wrapper" title="Gâteau !"></div>
            <div id="food_bar_display">
                <div id="food_bar_full">
                    <div id="food_bar_current" style="<?php echo $styleFoodBarCurrent; ?>%;" ></div>
                    <!--<div id="food_bar_text">9/10</div>-->
                </div> 
            </div>
        </div>
    </div>
    <div id="morale_bar">
        <div id="morale_text">Moral</div>
        <div id="morale_bar_wrapper">
            <div id="morale_icon_wrapper" title="Moral"></div>
            <div id="morale_bar_display">
                <div id="morale_bar_full">
                    <div id="morale_bar_current" style="<?php echo $styleMoraleBarCurrent; ?>%;" ></div>
                    <!--<div id="morale_bar_text">9/10</div>-->
                </div> 
            </div>
        </div>
    </div>
    <div id="shield_bar">
        <div id="shield_text">Bouclier</div>
        <div id="shield_bar_wrapper">
            <div id="shield_icon_wrapper"></div>
            <div id="shield_bar_display">
                <div id="shield_bar_full">
                    <div id="shield_bar_current" style="<?php echo $styleShieldBarCurrent; ?>%;"></div>
                    <!--<div id="shield_bar_text">9/10</div>-->
                </div> 
            </div>
        </div>
    </div>
    <div id="armors_bar">
        <div id="armors_text">Armures</div>
        <div id="armors_bar_wrapper">
            <div id="armors_icon_wrapper"></div>
            <div id="armors_bar_display">
                <div id="upper_bar_full" title="Vie de l'armure haute équipée">
                    <div id="upper_bar_current" title="Vie de l'armure haute" style="<?php echo $styleUpperBarCurrent; ?>%;"></div>
                </div> 
                <div id="lower_bar_full" title="Vie de l'armure basse équipée">
                    <div id="lower_bar_current" title="Vie de l'armure basse" style="<?php echo $styleLowerBarCurrent; ?>%;"></div>
                </div> 
                <div id="helmet_bar_full" title="Vie du casque équipé">
                    <div id="helmet_bar_current" title="Vie du casque équipé" style="<?php echo $styleHelmetBarCurrent; ?>%;"></div>
                </div> 
                <div id="mask_bar_full" title="Vie du masque équipé">
                    <div id="mask_bar_current" title="Vie du masque équipé" style="<?php echo $styleMaskBarCurrent; ?>%;"></div>
                </div> 
                <div class="clear-float"></div>
            </div>
        </div>
    </div>
    <div id="spear_bar">
        <div id="spear_text">Arme</div>
        <div id="spear_bar_wrapper">
            <div id="spear_icon_wrapper"></div>
            <div id="spear_bar_display">
                <div id="spear_bar_full">
                    <div id="spear_bar_current" style="<?php echo $styleSpearBarCurrent; ?>%;"></div>
                </div> 
            </div>
        </div>
    </div>
</div>

<?php include(dirname(__FILE__)."/vi_stat_box.php"); ?>