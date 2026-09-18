<?php
include(dirname(__FILE__)."/modules/vi_head.php");
?><!DOCTYPE html>

<div id="forum-content">
		
    <div id="forum-center-content">

        <div id="forum-header-wrapper">
            <?php 
                // Preview du titre si nouveau topic ou titre du topic existant si preview d'un message
                echo '<h3 style="font-weight:bold;"> '.$title.' </h3>';
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
        </div>

        <div id="forum-content-wrapper">

            <?php
                // Bouton valider la preview
                //echo '<button type="button" id="btn-validate-preview" class="btn btn-info" onclick="window.location.href=\'index.php?page=forum_creation&board='.$idBoard.'&isGlobal='.$isGlobal.'\'">Créer un sujet</button>';

                // Bouton modifier ?

                // Preview du corp du message mis en forme
                echo $messageHTML;
            ?>
     
        </div>

        <!-- Add some space at the end so the last element is not hidden befind the chatbox -->
        <br/>
        <br/>

    </div>
</div>

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