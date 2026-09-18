<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title> Mïr </title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
   <link rel="stylesheet" type="text/css" href="www/css/general_UI.css">
	</head>
	<body>
		<nav class="navbar navbar-expand-sm bg-dark navbar-dark justify-content-between fixed-top">
			<!-- Brand -->
  			<a class="navbar-brand" href="#">La citadelle inconnue</a>
			<form method="post" class="form-inline my-2 my-lg-0">
				<input class="form-control mr-sm-2 " type="text" placeholder="Nom d'utilisateur" name="username" required>
				<input class="form-control mr-sm-2 " type="text" placeholder="Mot de passe" name="password" required>
				<button class="btn btn-success" type="submit" formmethod="post">Se connecter</button>
			</form>
		</nav> 
		
		<div id="login-wrapper" class="login">
				<form method="post">
					<h1>Créer un compte</h1>
					<?php 
						//display error message when login, register, or session / cookie timeout
						if(isset($_SESSION['errorMsg'])) {
							echo '<div class="alert alert-danger" role="alert" style="text-align:center;">'.$_SESSION['errorMsg'].'</div>';
							unset($_SESSION['errorMsg']); 
						} 
						if(isset($_SESSION['validMsg'])) {
							echo '<div class="alert alert-success" role="alert" style="text-align:center;">'.$_SESSION['validMsg'].'</div>';
							unset($_SESSION['validMsg']); 
						} 
					?>
					<input type="text" name="usernameRegister" placeholder="Nom de joueur" id="usernameRegister" required>
					<small class="form-text text-muted">Votre nom d'utilisateur doit <span style="color:red;">commencer par une majuscule, </span> faire <span style="color:red;">moins de 20 charactères,</span> et ne <span style="color:red;">pas contenir de charactères spéciaux ni de nombres (espaces acceptés).</span></small>
					
					<input type="password" name="passwordRegister" placeholder="Mot de passe" id="passwordRegister" required>
					<input type="password" name="passwordVerif" placeholder="Vérifier le mot de passe" id="passwordVerif" required>
					<input type="text" name="email" placeholder="E-mail (facultatif)" id="email">
					<small id="emailHelp" class="form-text text-muted">Email nécessaire pour obtenir votre mot de passe en cas d'oubli. Adresses jetables acceptées.</small>
					<input type="text" name="emailVerif" placeholder="Vérifier l'e-mail" id="emailVerif">
					<input type="submit" value="Créer un compte">
				</form>
		
      </div>
	</body>
</html>

