<!DOCTYPE html>
<html class="no-scrollbar-element">
	<head>
		<meta charset="utf-8">
        <meta name="google-signin-client_id" content="<?php echo Constants::$google_signin_client_id ?>">
		<title> Mir </title>
	    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="www/css/general_UI.css">
	</head>
	<body>
        <div id="login-video-wrapper">
            <video id="login-video" width="100%" height="100%" autoplay loop muted poster="www/media/login-video-720p-poster.jpg">
                <source src="www/media/login-video-720p.mp4" type="video/mp4">
                <source src="movie.ogg" type="video/ogg">
                Your browser does not support the video tag.
            </video>
        </div>
		<div id="login-wrapper" class="login">
			<form method="post" id="login-form">
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
                <!-- <div id="my-signin2"></div> -->
                <!-- <button onclick="xxx();" >click</button> -->
			</form>		
        </div>
        <div id="game-presentation-wrapper">
            test
            kkkkkkkkkkkk

            kkkkkkokk

        </div>

        <script>
            // function xxx()
            // {
            //     var id_token = "106807772820961709282";

            //     $.ajax({
            //         type: "POST",
            //         // url: baseURL,
            //         url: 'core/ajax/ajax_login.php',
            //         // headers: {
            //         //     'Content-Type' : 'application/x-www-form-urlencoded',
            //         //     'Access-Control-Allow-Headers' : '*',
            //         //     'Access-Control-Request-Method' : '*'
            //         // },
            //         data: {xxx: id_token}, 
            //         success: function (response) 
            //         {
            //             console.log("ororo");
            //             // Account already exists -> user logged in
            //             if(response === '1')
            //             {
            //                 window.location.replace("index.php?page=home"); 
            //             }
            //             // Account don't exist -> prompt for username
            //             else
            //             {
            //                 displayUsernameLinkHTML();
            //             }
            //         },
            //         error: function(response)
            //         {
            //             console.log("h");
            //             console.log(response);
            //         }
            //     });
            // }
            function onSignIn(googleUser)
            {
                var profile = googleUser.getBasicProfile();
                //console.log('ID: ' + profile.getId());
                var id_token = googleUser.getAuthResponse().id_token;

                //var baseURL = "<?php //Constants::$baseUrl ?>";

                $.ajax({
                    type: "POST",
                    // url: baseURL,
                    url: 'core/ajax/ajax_login.php',
                    headers: {
                        'Content-Type' : 'application/x-www-form-urlencoded'
                    },
                    data: {googleAuthtoken: id_token}, 
                    success: function (response) 
                    {
                        //console.log(response);
                        // Account already exists -> user logged in
                        if(response === '1')
                        {
                            //window.location.href = window.location.href.split('?')[0] + "?page=home";
                            window.location.replace("index.php?page=home"); 
                        }
                        // Account don't exist -> prompt for username
                        else
                        {
                            displayUsernameLinkHTML();
                        }
                    }
                });
            }
            function onFailure(error)
            {
                console.log(error);
            }
            function renderButton()
            {
                gapi.signin2.render('my-signin2', {
                    'scope': 'profile email',
                    'width': 240,
                    'height': 50,
                    'longtitle': true,
                    'theme': 'dark',
                    'onsuccess': onSignIn,
                    'onfailure': onFailure
                });
            }

            function displayUsernameLinkHTML()
            {
                // Change the login form to link username to google account
                document.getElementById("my-signin2").remove();

                // Change the shape of the login form
                // document.getElementById("login-form").style.width = "500px";
                document.getElementById("login-wrapper").style.width = "500px";
                document.getElementById("login-wrapper").style.margin = "-100px 0 0 -250px"; //adapt left-margin

                document.getElementById("login-form").innerHTML = 
                // Titre
                '<h1>Une dernière étape...</h1>' +
                // Username input
                '<input type="text" name="usernameRegister" placeholder="Pseudonyme" id="usernameRegister" required>' +
                // Info message about the username
				'<small class="form-text text-muted">Votre nom d\'utilisateur(rice) doit <span style="color:red;">commencer par une majuscule, ' +
                    '</span> faire <span style="color:red;">moins de 20 charactères,</span> et ne <span style="color:red;">pas contenir de charactères spéciaux ' + 
                    'ni de nombres (espaces acceptés).</span></small>' +
                // Checkbox
                '<input type="checkbox" id="cblogin" name="usernameInput" value="c1" required>' +
                '<label for="cblogin"> J\'ai bien compris que ce jeu est en test et je donnerai mon avis et des idées d\'amélioration si possible.</label>' +
                // Submit
                '<input type="submit" value="Finaliser la création du compte">';
            }

        </script>
        <!-- <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script> -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
	</body>
</html>

