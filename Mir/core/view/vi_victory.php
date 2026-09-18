<?php

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <h3 style="font-weight:bold; margin-left:10px; margin-top:10px;">Victoire !</h3>
    <div id="info-screen-text-victory">

        <p style="font-weight:bold;">
            Vous avez coopéré avec vos alliés et avez vaincu l'Ombre.<br/>
            <br/>
            Les nuages noirs se dissipent, et le soleil se lève à nouveau.<br/>
            <br/>
            Vos statistiques sont conservées sur votre profil :<br/>
        </p>

        <div id="wrapper-city-stats">
            <h5 style="font-weight:bold; text-align:center;">Accomplissements de la ville</h5>
            <ul>
                <?php echo $cityStatsHTML; ?>
            </ul>  
        </div>

        <div id="wrapper-best-player-stats"> 
            <h5 style="font-weight:bold; text-align:center;">Meilleurs joueurs</h5>
            <ul>
                <?php echo $bestPlayerStatsHTML; ?>
            </ul>  
        </div>

        <!-- hack pour mettre le texte sous les div -->
        <br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
        <p style="font-weight:bold;">
            Un classement général des Citadelles sera bientôt disponible, ainsi qu'un système d'Alliance pour rejoindre une Citadelle avec les mêmes joueurs. Consultez le topic
            officiel sur le forum pour connaître la liste des améliorations prévues et en discuter.<br/>
            <br/>
            Je travaille seul sur ce jeu, Merci de donner vos avis sur le forum général: <a href="forum/index.php?board=6.0">Forum des avis.</a><br/>
            <!--<span><?php //echo $hopeQuote ?></span><br/>-->
            <br/>
            Merci d'avoir joué ! Réessayez pour une autre fin, et vous verrez la Citadelle, à nouveau.<br/>
        </p>

        <a href="index.php?page=home">Je me repose.</a>

    </div>	
</div>
	

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>