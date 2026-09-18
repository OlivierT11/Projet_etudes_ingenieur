<?php

include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <h3 style="font-weight:bold; margin-left:10px; margin-top:10px;">Vous avez péri</h3>
    <div id="info-screen-text">
        <p>
            <b><?php echo $deathReason; ?></b><br/>
            <br/>
            Votre corps gît sans vie, lentement recouvert par l'Ombre et la glace.<br/>
            <br/>
            Dans ce monde, la mort n'est pas grave ... pour vous !<br/>
            Vous perdez tout votre équipement, vos objets, et votre ventre se vide dangereusement !<br/>
            Ce manque de ressources fait perdre du temps à la ville, et tous les joueurs risquent de perdre la partie.<br/>
            <br/>
            Si vous êtes perdu, posez une question dans la chatbox, dans le forum général ou local, et consultez l'aide en haut de la page.<br/>
            <br/>
            Ce jeu n'en est qu'au début (alpha). Si vous avez des remarques, des propositions d'améliorations, que vous trouvez certaines choses mal faites ou que vous rencontrez un bug, 
            n'hésitez pas à donner vos avis sur le forum en haut de la page. Merci !<br/>
            <!--<span><?php //echo $hopeQuote ?></span><br/>-->
            <br/>
            Vous vous réveillez dans un bain de lumière, sur un autel, en face de l'horloge de l'apocalypse ...<br/>
        </p>
        <!-- <a href="index.php?page=horlogis">Je revis !</a> -->
        <button type="button" id="btn-revive" class="btn btn-success" onclick="window.location.href='index.php?page=inside'">Revivre.</button>
    </div>	
</div>
	

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>