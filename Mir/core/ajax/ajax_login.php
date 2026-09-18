<?php

#region Session

if(!isset($_SESSION))
session_start();

#endregion


#region Classes

// For google auth classes
require dirname(dirname(dirname(__FILE__))).'/vendor/autoload.php';

require dirname(dirname(__FILE__))."/config/config.php";

require dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

require dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

require dirname(dirname(__FILE__))."/class/class.constants.php";

require dirname(dirname(__FILE__))."/class/class.account.php";

// require dirname(dirname(__FILE__))."/class/class.cache.php";
// $cache = new Cache($_SESSION['id_city']);

#endregion

// if(isset($_POST['xxx'])) 
// {
//   $userid = $_POST['xxx'];  
//   $log->addLog('test', 'error', 'rtrt');

//   // check if user exists (login user)
//   $account = new Account($database, $log, $userid);
//   $accountAlreadyExists = "0";
//   $accountAlreadyExists = $account->checkIfAccountExists();

//   // if yes, login then header to home
//   if ($accountAlreadyExists == "1")
//   {
//       // Regenerate the session to prevent security risks
//       session_destroy();
//       session_start();    

//       //get player data
//       $account->loginUser();

//       // return OK
//       exit("1");
//       // echo '1';
//       // die; 
//   }
// }

// If the player tries to connect
if(isset($_POST['googleAuthtoken'])) 
{
    $id_token = $_POST['googleAuthtoken'];
    
    // Get $id_token via HTTPS POST.
    $CLIENT_ID = Constants::$google_signin_client_id;
    $client = new Google_Client(['client_id' => $CLIENT_ID]);  // Specify the CLIENT_ID of the app that accesses the backend
    $payload = $client->verifyIdToken($id_token); 
    // Player connected
    if ($payload)
    {
        $userid = $payload['sub'];  

        // check if user exists (login user)
        $account = new Account($database, $log, $userid);
        $accountAlreadyExists = "0";
        $accountAlreadyExists = $account->checkIfAccountExists();

        // if yes, login then header to home
        if ($accountAlreadyExists == "1")
        {
            // Regenerate the session to prevent security risks
            session_destroy();
            session_start();    

            //get player data
            $account->loginUser();

            // return OK
            exit("1");
            // echo '1';
            // die; 
        }
        // if account do not exists, change the login menu to select the username (ajax)
        else
        {
          // Keep the token in session for when the user will have created a username
          $_SESSION['googleAuthtoken'] = $_POST['googleAuthtoken'];

          // return "NOT OK"
          exit("0");
        //   echo '0';
        //   die;
        }
    }
} 
// The player creates an account by linking his google account to a username
else if(isset($_POST['usernameInput'])) 
{
  //TODO : single transaction
  $account = new Account($database, $log, $_SESSION['googleAuthtoken']);
  $account->registerUser($_POST['usernameInput']);

  session_destroy();
  session_start();

  $account->loginUser();

  $_SESSION['validMsg'] = "Votre compte a été créé avec succès.";

  header("Location: index.php?page=home");
}
else
{
}
