<?php
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="forum-content">
		
    <div id="forum-center-content">

        <div id="forum-header-wrapper">
            <?php 
                // Titre de la page
                if($title != "")
                {
                    echo '<h3 style="font-weight:bold;"> '.$title.' </h3>';
                }
                else
                {
                    echo '<h3 style="font-weight:bold;"> Forum de la Citadelle </h3>';
                }
            ?>

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

            <?php
                // Bouton ajouter un sujet
                if($topicListHTML != "")
                {
                    // Forum global (présence d'un idBoard)
                    if ($isGlobal == "1")
                    {
                        echo '<button type="button" id="btn-create-topic" class="btn btn-info" onclick="window.location.href=\'index.php?page=forum_creation&board='.$idBoard.'&isGlobal='.$isGlobal.'\'">Créer un sujet</button>';
                    }
                    else
                    {
                        echo '<button type="button" id="btn-create-topic" class="btn btn-info" onclick="window.location.href=\'index.php?page=forum_creation&isGlobal='.$isGlobal.'\'">Créer un sujet</button>';
                    }
                }

                // Bouton retour vers liste des catégories
                if($topicListHTML != "" && $isGlobal == '1')
                {
                    // Forum global (présence d'un idBoard)
                    echo '<button type="button" id="btn-goback-board" class="btn btn-info" onclick="window.location.href=\'index.php?page=forum&isGlobal='.$isGlobal.'\'">Retour liste des catégories</button>';
                }

                // Bouton retour vers liste des topics
                if($messageListHTML != "")
                {
                    // Si forum global, on a un idBoard
                    if (isset($idBoard))
                    {
                        echo '<button type="button" id="btn-goback-topic" class="btn btn-info" onclick="window.location.href=\'index.php?page=forum&board='.$idBoard.'&isGlobal=1\'">Retour liste des sujet</button>';
                    }
                    else
                    {
                        echo '<button type="button" id="btn-goback-topic" class="btn btn-info" onclick="window.location.href=\'index.php?page=forum&isGlobal=0\'">Retour liste des sujet</button>';
                    }
                }

            ?>
            
            <?php 
                // Return btn
                // if($messageListHTML != "")
                // {
                //     $link = "index.php?page=forum&board=".$_GET['board'];
                //     echo '<a href="'.$link.'">&lt;$lt; Retour à la liste des sujets.</a> <br/>';
                // }

                // Liste des éléments du forum
                if($boardListHTML != "")
                {
                    echo $boardListHTML;
                }
                else if($topicListHTML != "")
                {
                    echo $topicListHTML;
                }
                else if($messageListHTML != "")
                {
                    echo $messageListHTML;
                }
            ?>
     
        </div>

        <?php
            // Module d'ajout de message
            if($messageListHTML != "")
            {
                echo $addMessageModuleHTML;
            }
        ?>

        <!-- Add some space at the end so the last element is not hidden befind the chatbox -->
        <br/>
        <br/>

    </div>
</div>

<!-- DEBUT MODAL REPORT -->
<div class="modal" id="myModal" data-id-message="">
    <div class="modal-dialog">
        <div class="modal-content">
        <!-- Modal Header -->
        <div id="modal-header">
            <h4 class="modal-title">Signaler un message</h4>
            <button type="button" class="close" data-dismiss="modal">&times;</button>
        </div>
        <!-- Modal body -->
        <div class="modal-body">
            <p>
                Vous pouvez signaler ce message à la modération s'il contient des insultes, du harcèlement, ou tout autre contenu
                contraire aux conditions d'utilisation du site. Un modérateur consultera le message et le supprimera si justifié. 
                <b>Merci pour votre aide !</b>
                <br/>
                <br/>
                <b>Ajouter une explication (facultatif, 200 lettres max) :</b>
                <br/>
            </p>
            <textarea id="report-message-textarea" cols="47" maxlength="200"></textarea>
        </div>
        <!-- Modal footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="btn-validate-report" data-dismiss="modal">Envoyer le signalement</button>
            <button type="button" class="btn btn-danger" data-dismiss="modal">Annuler</button>
        </div>
        </div>
    </div>
</div>
    
    <!-- FIN MODAL REPORT -->
	
<?php 
    // We do not want to display the footer when we are in the message list (the center page's content is too long)
    // if(!isset($_GET['topic']))
    // {
    //     //include(dirname(__FILE__)."/modules/vi_footer.php");
    // } 
    // else
    // {
    //     // Set an empty footer
    //     echo 
    //     '<div class="clear-float"></div>
    //     <footer></footer>
    //     <div class="clear-float"></div>
    //     <br>
    //     </div>';
    //  }
?>

<div id="right-img">
    <?php 
        echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']);
    ?>
</div>

<!-- Scripts -->
<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script type="text/javascript" src="www/js/forum.js"></script>
<script type="text/javascript" src="www/js/ajax_forum.js"></script>
<script type="text/javascript" src="www/js/ajax_general.js"></script> <!-- For chatboxes only, TODO : remove the rest -->


<noscript>Sorry, your browser does not support JavaScript!</noscript>

</body>
</html>