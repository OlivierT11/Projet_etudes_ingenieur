<?php

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <h3 style="font-weight:bold; margin-left:10px; margin-top:10px;">Vous avez péri</h3>
    <div id="info-screen-text-defeat">

       <div>
            <p style="font-weight:bold;">
                Le monde est tombé. <br/>
                <br/>
                L'horloge a indiqué zéro, et tout ce qui le peuplait est mort avec lui.<br/>
                <br/>
                L'Ombre et le froid ont tout recouverts, sans espoir de retour, jamais.<br/>
                <br/>
                Mais votre labeur n'est pas perdu ! Vos statistiques sont conservées sur votre profil :<br/>
            </p>
        </div>
       
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
        <div>
            <p style="font-weight:bold;">
                Un classement général des Citadelles sera bientôt disponible, ainsi qu'un système d'Alliance pour rejoindre une Citadelle avec les mêmes joueurs. Consultez le topic
                officiel sur le forum pour connaître la liste des améliorations prévues et en discuter.<br/>
                <br/>
                Je travaille seul sur ce jeu, Merci de donner vos avis sur le forum général: <a href="forum/index.php?board=6.0">Forum des avis.</a><br/>
                <!--<span><?php //echo $hopeQuote ?></span><br/>-->
                <br/>
                Merci d'avoir joué ! Réessayez pour une autre fin, et vous verrez la Citadelle, à nouveau.<br/>
            </p>
        </div>

        <a href="index.php?page=home">Je me repose.</a>

    </div>	
</div>
	

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>