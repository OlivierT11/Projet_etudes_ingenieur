<?php
//$_SESSION['player_area'] = 'home';
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">
		
    <div id="home-center-content">
        <div id="info-screen-text-advice">

            <h1 style="text-align:center;">Description du jeu et Conseils</h1>
            <br/>

            <h3 style="text-align:center;">Entraide et survie</h3>

            <ul>
                <li>Les joueurs sont répartis en villes de <?php echo Constants::$maxPlayersPerCity; ?> joueurs et doivent s'organiser pour survivre.</li>
                <li>Vous n'avez pas de points de vie. 1 coup et c'est la mort.</li>
                <li>Equipez des pièces d'armure pour absorber les dégâts.</li>
                <li>Les armes et armures peuvent être fabriquées à l'atelier contre des ressources.</li>
                <li>Le niveau de l'objet fabriqué dépend de la compétence du joueur et de la qualité des matières premières.</li>
            </ul>

            <h3 style="text-align:center;">Montée de niveau et progression</h3>

            <ul>
                <li>Chaque action rapporte des points d'expérience dans un domaine (ex: couper du bois, fabriquer un casque...).</li>
                <li>Il n'y a pas de métier/rôle fixe. Vous pouvez atteindre le niveau maximum dans chaque domaine, mais si vous choisissez de vous 
                    spécialiser, vous obtiendrez un gros bonus (Maîtrise <span id="mastery-logo-stat-box"><span>).
                </li>
                <li>Le niveau maximum atteignable dans un domaine commence à 10, et augmente de 5 chaque jour. Par exemple, si la ville est au jour 3, le niveau max sera 20.</li>
                <li>Ainsi, si vous jouez peu, vous pouvez vous spécialiser dans un domaine pour rester utile grâce au bonus de maîtrise. Si vous jouez beaucoup, vous
                    pourrez tout faire et vous adapter à toute situation, mais vous le ferez moins efficacement.
                </li>
                <li>Chaque ville dispose d'un forum local, d'une chatbox, et un forum général est dispo. Utilisez-les pour définir des stratégies.</li>
            </ul>
    
            <h3 style="text-align:center;">Conditions de victoire</h3>

            <ul>
                <li>Pour gagner, vous devez terminer le donjon final, qui s'ouvre sous la ville au bout de 6 jours. D'ici là, vous devez accumuler un maximum de ressources et
                     de niveaux de compétence. Si vous n'y parvenez pas, le monde s'éteindra au bout de 10 jours.
                </li>
                <li>Les ressources stoquées ont une durée de vie. Pour l'augmenter, il faut construire des bâtiments avec des ressources rares. On en trouve surtout au fond des donjons 
                    et au coeur des forêts. Les récolteurs et combattants peuvent aussi en trouver parfois.
                </li>
                <li>Ces zones sont trop éloignées et inatteignables seul(e). Il faut organiser des expéditions.</li>
                <li>Une bonne stratégie consiste à construire des camps autour des donjons et en lisière des forêts. Les joueurs y stoqueront et fabriqueront des ressources et de l'équipement.
                     Les guerriers et explorateurs les utiliseront pour nettoyer les zones à risque.
                </li>
                <li>Les Ombres réapparaissent à minuit. Vous perdez du moral si vous restez loin des autres joueurs trop longtemps,
                    et en gagnez si vous êtes proche.
                </li>
            </ul>

            <h3 style="text-align:center;">La Vague</h3>

            <ul>
                <li>
                    Tous les 5 jours, une tempête géante frappe la région, endommageant fortement les objets non stockés, réduisant de 50% le moral et augmentant de 50% la faim des joueurs.
                </li>
                <li>
                    De plus, tous les camps abandonnés (sans joueurs ni objets en stock) de la région au moment de la Vague seront détruits. Les camps sous terre ne sont pas touchés.
                </li>
                <li>
                    Pour échapper à la Vague, protégez vous à l'intérieur de la ville, ou dans un camp, au moment de son passage.
                </li>
            </ul>

            <h3 style="text-align:center;">Recettes</h3>

            <ul>
                <li>
                    Tarte aux fruits : 4 fruits.
                </li>
                <li>
                    Miel raffiné : 4 miels.
                </li>
                <li>
                    Gâteau : 2 Tartes + 2 Miels raffinés.
                </li>
            </ul>

            <div class="col text-center">
                <button id="go-to-home-page" class="btn btn-success border border-dark">J'ai compris.</button>
            </div>
        </div>
    </div>     
</div>    
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>