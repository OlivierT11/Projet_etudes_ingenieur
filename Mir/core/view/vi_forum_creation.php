<?php
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="forum-content">
    <?php //include(dirname(__FILE__)."/modules/vi_forum_left_menu.php");?>
		
    <div id="forum-center-content">

        <div id="forum-header-wrapper">
            <h3 style="font-weight:bold;"> Créer un nouveau sujet </h3>

            <?php 
				//display error message on submit error
				if(isset($_SESSION['errorMsg'])) {
					echo '<div class="alert alert-danger" role="alert" style="text-align:center;">'.$_SESSION['errorMsg'].'</div>';
					unset($_SESSION['errorMsg']); 
				} 
				if(isset($_SESSION['validMsg'])) {
					echo '<div class="alert alert-success" role="alert" style="text-align:center;">'.$_SESSION['validMsg'].'</div>';
					unset($_SESSION['validMsg']); 
				} 
			?>

            <p></p>
        </div>

        <div id="forum-content-wrapper">

            <form action="index.php?page=forum_preview" method="post" id="new-topic-form">

                <div id="new-topic-header">
                    <div class="form-group" id="new-topic-title">
                        <label for="inputTitle"><b>Titre de la discussion</b></label>
                        <input type="text" class="form-control" placeholder="Titre de la discussion" name="topicTitle" id="inputTitle">
                    </div>

                    <div class="form-check" id="toggle-survey-checkbox">
                        <label for="topicHasSurvey">
                            <!-- Input in label makes the label clickable -->
                            <input type="checkbox" class="form-check-input" name="topicHasSurveyCheckbox" value="hasSurvey" id="topicHasSurvey">
                            <b>Ajouter un sondage</b>
                        </label>
                    </div>

                    <div id="hidden-survey-wrapper" style="display:none">
                        <div class="form-check" id="allow-multiple-choices-checkbox">
                            <label for="multipleChoicesEnabled">
                                <!-- Input in label makes the label clickable -->
                                <input type="checkbox" class="form-check-input" name="multipleChoicesEnabledCheckbox" value="multipleChoices" id="multipleChoicesEnabled">
                                <b>Autoriser les choix multiples</b>
                            </label>
                        </div>


                        <div class="form-group"  id="survey-title-wrapper">
                            <label for="surveyTitle"><b>Question du sondage</b></label>
                            <input type="text" class="form-control" placeholder="Question du sondage" name="surveyTitle" id="surveyTitle">
                        </div>

                        <div id="survey-choices-wrapper">
                            <div class="form-group" id="new-topic-survey-choices" style="display: visible;">
                                <div class="topic-survey-choice" id="wrapper-choice-1" data-id-choice="1"> 
                                    <label for="Choix1">Choix 1 </label> <div class="topic-del-choice"></div>
                                    <input type="text" class="form-control" name="Choix1" id="Choix1">
                                </div>
                            </div>
                            <div id="topic-add-choice"></div>
                        </div>
                    </div>
                </div>

                <br/>

                <div id="new-topic-wrapper">
                    <div id="new-topic-format">
                        <div id="format-text">
                            <div id="format-text-bold" title="Gras"></div>
                            <div id="format-text-underline" title="Souligné"></div>
                            <div id="format-text-quote" title="Citation"></div>
                        </div>
                        <div id="format-emotes">
                            <div id="emote-thumb" class="forum-emote emote-thumb" title="J'aime"></div>
                            <div id="emote-heart" class="forum-emote emote-heart" title="Coeur"></div>
                            <div id="emote-laugh" class="forum-emote emote-laugh" title="Rire"></div>
                            <div id="emote-smile" class="forum-emote emote-smile" title="Sourire"></div>
                            <div id="emote-sad" class="forum-emote emote-sad" title="Triste"></div>
                            <div id="emote-angry" class="forum-emote emote-angry" title="Colère"></div>
                            <div id="emote-fear" class="forum-emote emote-fear" title="Peur"></div>
                            <div id="emote-surprised" class="forum-emote emote-surprised" title="Choc"></div>
                            <div id="emote-pls" class="forum-emote emote-pls" title="PLS"></div>
                            <div id="emote-vomit" class="forum-emote emote-vomit" title="Vomir"></div>
                            <div id="emote-danger" class="forum-emote emote-danger" title="Danger"></div>

                            <!-- Empty area -->
                            <div class="forum-emote"></div>

                            <div id="emote-wood" class="forum-emote icon-wood" title="Bois"></div>
                            <div id="emote-metal" class="forum-emote icon-metal" title="Métal"></div>
                            <div id="emote-orange" class="forum-emote icon-orange" title="Fruit"></div>
                            <div id="emote-honey" class="forum-emote icon-honey" title="Miel"></div>
                            <div id="emote-cake" class="forum-emote icon-cake" title="Gâteau"></div>
                            <div id="emote-mask" class="forum-emote icon-mask" title="Masque"></div>
                            <div id="emote-helmet" class="forum-emote icon-helmet" title="Casque"></div>
                            <div id="emote-shield" class="forum-emote icon-shield" title="Bouclier"></div>
                            <div id="emote-upper-armor" class="forum-emote icon-upper-armor" title="Armure haute"></div>
                            <div id="emote-lower-armor" class="forum-emote icon-lower-armor" title="Armure basse"></div>
                            <div id="emote-spear" class="forum-emote icon-spear" title="Lance"></div>

                            <div class="forum-emote"></div>

                            <div id="emote-foe" class="forum-emote icon-foe" title="Monstre"></div>
                            <div id="emote-tree" class="forum-emote icon-tree" title="Arbre"></div>
                            <div id="emote-hive" class="forum-emote icon-hive" title="Ruche"></div>
                            <div id="emote-tree-fruit" class="forum-emote icon-tree-fruit" title="Arbre fruitier"></div>
                            <div id="emote-dj" class="forum-emote icon-dj" title="Donjon"></div>
                        </div>
                        <!-- <div id="new-topic-preview">
                            Prévisualiser
                        </div>
                        <div id="new-topic-validate">
                            Valider
                        </div> -->
                    </div>
                    <div class="new-topic-text">
                        <textarea id="new-topic-textarea" name="newTopicContent" cols="200" rows="100" maxlength="<?php Constants::$forum_max_message_length;?>"></textarea>
                    </div> 
                </div>

            </form>

            <div class="clear-float"></div>
            
        </div>

        <br>

        <button type="button" id="btn-new-topic-preview"  class="btn btn-info">Prévisualiser</button>
        <button type="button" id="btn-new-topic-validate" class="btn btn-success">Valider</button>

        <!-- Add some space at the end of the page for the chatbox -->
        <br>
        <br>

    </div>

</div>
	
<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>


<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>

<!-- Scripts -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script> <!-- Bootstrap modal -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script type="text/javascript" src="www/js/forum.js"></script>
<script type="text/javascript" src="www/js/ajax_forum.js"></script>

<noscript>Sorry, your browser does not support JavaScript!</noscript>

</body>
</html>