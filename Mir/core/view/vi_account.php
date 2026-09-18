<?php
include(dirname(__FILE__)."/modules/vi_head.php");

// Adds a "return" btn in left menu button intead of the actual btns of the area (is unset right after use)
//$_SESSION['page_needs_return'] = 1;

?><!DOCTYPE html>

<div id="content">
    <?php // include(dirname(__FILE__)."/modules/vi_left_menus.php");?>
    
    <div id="center-content-info">
      <h3 style="font-weight:bold;  text-align:center;">Informations du compte</h3>
      <form id="account-form">
        <div class="form-group">
          </br>
            <?php
              if($_SESSION['email'] == '') {
                echo '<div class="alert alert-danger" role="alert">Ajoutez une addresse mail pour recevoir votre mot de passe en cas de perte.</div>';
              }
              if(isset($_SESSION['errorMsg'])) {
                echo '<div class="alert alert-danger" role="alert">'.$_SESSION['errorMsg'].'</div>';
                unset($_SESSION['errorMsg']); 
              } 
              if(isset($_SESSION['successMsg'])) {
                echo '<div class="alert alert-success" role="alert">'.$_SESSION['successMsg'].'</div>';
                unset($_SESSION['successMsg']); 
              }  
            ?>
          <label for="exampleInputEmail1"><strong>Nouvelle adresse mail</strong></label><br/>
          <input type="email" name="email" class="form-control" id="email" placeholder="Email"><br/>
          <input type="email" name="emailVerif" class="form-control" id="email" placeholder="Entrer à nouveau l'address mail">
          <small id="emailHelp" class="form-text text-muted">Cette addresse ne sera pas diffusée.</small>
        </div>
        <div class="form-group">
          <label for="pwd"><strong>Changer de mot de passe</strong></label><br/>
          <input type="password" name="passwd" class="form-control" id="passwd1" placeholder="Nouveau mot de passe"><br/>
          <input type="password" name="passwd2" class="form-control" id="passwd2" placeholder="Recopier le mot de passe">
        </div>
        <div class="form-group">
          <label for="pwd-verif">Entrez votre mot de passe actuel pour continuer</label>
          <input type="password" name="passwdFinal" class="form-control" id="passwd3" placeholder="Mot de passe">
        </div>
        <button type="submit" class="btn btn-primary">Valider</button>
        <div class="form-group">
          <label><strong>Quitter la ville</strong></label>
          <small class="form-text text-muted">Efface la progression pour la ville en cours, et vous permet d'en choisir une nouvelle. Vos alliés n'apprécieront pas.</small>
          <button type="button" id="btn-leave-city" class="btn btn-primary">Quitter la ville</button>
        </div>
        
      </form>
    </div>
</div>

<?php //include(dirname(__FILE__)."/modules/vi_footer.php"); ?>

<div id="right-img">
    <?php echo getSideImageRight($_SESSION['player_area'], $_SESSION['player_sub_area'], $_SESSION['era']); ?>
</div>

<?php include(dirname(__FILE__)."/modules/vi_chatboxes.php"); ?>
<?php include(dirname(__FILE__)."/modules/vi_end.php"); ?>