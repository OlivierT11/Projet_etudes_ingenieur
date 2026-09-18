<?php
$_SESSION['player_area'] = 'camp';
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
    <?php include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
    <div id="center-content">
        <h3 style="font-weight:bold;">Camp fortifié</h3>
        <p>
            Vous trouvez refuge dans un camp fortifié.<br/>
            Vous pouvez y fabriquer et entreposer des objets et vous protéger la nuit.<br/>
            Un bureau de poste vous permet de recevoir les actualités de la Citadelle.<br/>
            Les objets stockés dans cet entrepôt de fortune sont sensibles aux intempéries.<br/>
            <strong>Tout camp sans objets en stock sera considéré inutile et détruit par le passage de la Vague.</strong>
        </p>
        <div id="major-news-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Nouveautés de la ville</h5>
            <ul>
                <?php echo $majorNewsListHTML; ?>
            </ul>
        </div>
        <div id="minor-news-wrapper">
            <h5 style="font-weight:bold; text-align:center;">Actions des alliés</h5>
            <ul>
                <?php echo $minorNewsListHTML; ?>
            </ul>
        </div>
    </div>
</div>

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>