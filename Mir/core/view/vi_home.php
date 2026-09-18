<?php
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="content">

    <?php 
        if(isset($_SESSION['errorMsg'])) {
            echo '<div class="alert alert-danger" role="alert" style="text-align:center;">'.$_SESSION['errorMsg'].'</div>';
            unset($_SESSION['errorMsg']); 
        } 
        if(isset($_SESSION['validMsg'])) {
            echo '<div class="alert alert-success" role="alert" style="text-align:center;">'.$_SESSION['validMsg'].'</div>';
            unset($_SESSION['validMsg']); 
        } 
    ?>

	<h3 style="font-weight:bold; margin-left:10px; margin-top:10px;">Bienvenue !</h3>
    <br>
	<h4 style="font-weight:bold; margin-left:10px; margin-top:10px;">Vous êtes dans l'accueil. Vous pouvez :</h4>
    <ul>
        <li>Consultez les <a href="index.php?page=advice">principes de base du jeu</a>.</li>
        <li>Baladez-vous sur le <a href="index.php?page=forum">forum général</a>.</li>
        <li>Consultez les <a href="#wrapper-news">nouveautés</a>.</li>
        <li>Proposez une <a href="index.php?page=forum?isGeneral=1&topic=">idée d'amélioration</a> du jeu ou <a href="index.php?page=forum?isGeneral=1&topic=">signalez un bug</a>.</li>
        <li>Donnez votre <a href="index.php?page=forum?isGeneral=1&topic=">avis sur le jeu</a>. Il sera pris en compte pour les futures améliorations. Merci !</li>
        <li>Consultez vos <a href="index.php?page=statitic">statistiques</a> de jeu.</li>
        <li>Modifiez les <a href="index.php?page=account">données de votre compte et vos préférences de jeu</a>.</li>
        <li>Vous êtes prêt(e) ? <a href="#wrapper-join-city" style="color:purple;">Rejoignez une ville</a> et commencez la partie.</li>
    </ul>
    <br>

    <div id="wrapper-join-city">
        <button type="button" id="create-city-btn" class="btn btn-info">Fonder une citadelle</button>
        <table>
            <?php echo $citiesListHTML; ?>
        </table>
        <!-- Pagination here <br> -->
    </div>

    <div id="wrapper-news">
        <h5 style="font-weight:bold; text-align:center;">Les nouveautés du jeu</h5>
        <ul>
            <?php echo $newsListHTML; ?>
        </ul>  
    </div>
    
    <!-- <div id="info-screen-text-home">
        <h3 style="font-weight:bold; margin-top:5px; margin-bottom:10px;">Vous n'êtes dans aucune ville.</h3>

        <p style="color:#a34651; font-weight:bold;">
            Important : Ce jeu est en version d'essai. Il a été conçu et codé par une seule personne, en environ 1 an.
            Il n'y a pas de publicités, ni de système de paiement. L'amélioration de l'ergonomie et des graphismes est en cours.
            Merci de donner votre avis sur le forum pour l'amélioration du jeu. Vous pouvez consulter la liste des ajouts prévus en vous
            baladant sur le forum, ou envoyer un message
            à l'administrateur via le contact en bas à droite pour toute question. Bon jeu !
        </p>

        <p><strong>Avant de commencer, prenez 5 minutes pour découvrir les principes clé du jeu.</strong></p>
        <button id="go-to-advice-page" class="btn btn-success" style="border: 1px solid black;">Description du jeu et conseils</button>
        <p><br></p>

        <p><strong>Rejoindre une ville et commencer l'aventure.</strong></p>
        <button id="enter-new-city" class="btn btn-success" style="border: 1px solid black;">Rejoindre une ville</button>
        <p><br></p>

        <p><strong>Accéder au forum général :</strong></p>
        <button id="enter-forum" class="btn btn-success" style="border: 1px solid black;">Forum</button>

    </div> -->

</div>
 
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<!-- Hide map, maze and abyss generation. These elements will still be selectable by JS in ajax_new_city.js -->
<!-- <div style="display:none;">
    <p>Image to use:</p>
    <img id="image" width="100" height="100" src="www/img/map_100_100.png" alt="The image to transform">

    <p>Biome image to use :</p>
    <canvas id="myCanvas" width="100" height="100" style="border:1px solid #d3d3d3;">
    Your browser does not support the HTML5 canvas tag.
    </canvas>

    <div id="pixelArrayContentX"></div>
    <div id="pixelArrayContentY">
    </div>
    <div id="pixelArrayContentR">
    </div>
    <div id="pixelArrayContentG">
    </div>
    <div id="pixelArrayContentB">
    </div>
    <div id="pixelArrayContentA">
    </div>

    <button id="copyImageInDB">Copy the image in DB map_test</button>

    <div id="mapQuery">
    </div>

    <p>Maze</p>

    <table id="maze">
    <tbody></tbody>
    </table>

    <button id="copyMazeInDB">Copy the maze in DB maze_test</button>

    <div id="mazeQuery">
    </div>

    <p>Abyss</p>

    <p hidden >Image to use:</p>
    <img id="imageAbyss" width="50" height="50" src="www/img/abyss_50_50_test.png" alt="The image to transform" hidden>

    <p>Abyss image to use :</p>
    <canvas id="myCanvasAbyss" width="50" height="50" style="border:1px solid #d3d3d3;">
    Your browser does not support the HTML5 canvas tag.
    </canvas>
    <button id="copyAbyssInDB">Copy the abyss map in DB abyss_test</button>
    <div id="abyssQuery">
    </div>
</div> -->

<!-- Join an existing city -->
<script>
    
    var classNameJoinCity = document.getElementsByClassName("join-city-btn");
    var n = classNameJoinCity.length;
  
    for (var i = 0; i < n; i++)
    {
        classNameJoinCity[i].addEventListener("click", function(ev)
        {
           // ev.preventDefault(); // the <a> is not a link anymore

            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            var idCity = ev.target.getAttribute("id");

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_city.php",
                data: 
                {
                    action: 'joinCity',
                    idCity: idCity
                }, 
                success: function (response)
                {
                    window.location.replace("index.php?page=inside"); 
                },
                error: function (response)
                {
                    alert("Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.");
                },
                complete: function()
                {
                    me.data('requestRunning', false);
                }
            });
        });
    }

</script>

<!-- Create a new city -->
<script>
    document.getElementById("create-city-btn").addEventListener("click", function(ev)
    {
        var classNameJoinCity = document.getElementsByClassName("available-city-to-join");
        var n = classNameJoinCity.length;

        if(n > 1)
        {
            var confirmText = "Vous décidez d'explorer plus loin que personne n'est jamais allé. Une fois arrivé(e) vous lancerez un appel, ";
                confirmText + "mais d'autres vous rejoindront-ils ? ";
                confirmText + "\r\n";
                confirmText + "Il reste des Citadelles en cours, voulez-vous vraiment en fonder une nouvelle ?";

            if (confirm(confirmText)) 
            {
                var me = $(this);
                // Prevents multi clic
                if ( me.data('requestRunning') ) {
                    return;
                }
                me.data('requestRunning', true);

                $.ajax({
                    type: "POST",
                    url: "core/ajax/ajax_city.php",
                    data: {action: 'createCity'}, 
                    success: function (response)
                    {
                        window.location.replace("index.php?page=intro"); 
                    },
                    error: function (response)
                    {
                        alert("Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.");
                    },
                    complete: function()
                    {
                        me.data('requestRunning', false);
                    }
                });
            }
        }
        else
        {
            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_city.php",
                data: {action: 'createCity'}, 
                success: function (response)
                {
                    window.location.replace("index.php?page=inside"); 
                },
                error: function (response)
                {
                    alert("Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.");
                },
                complete: function()
                {
                    me.data('requestRunning', false);
                }
            });
        }
    });

</script>

<!-- script génération map/maze remplacé par PHP -->
<!-- <script type="text/javascript" src="www/js/ajax_new_city.js"></script> -->

<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>