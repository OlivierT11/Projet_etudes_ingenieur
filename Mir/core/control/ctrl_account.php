<?php

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

if($_SERVER["REQUEST_METHOD"] == "POST") {

    #region Confirm password

    //sanitize the confirm password
    $passwordFinal = $_POST['passwdFinal'];
    $sanitizedConfirmPassword = validateLoginPassword($passwordFinal, '', true);

    // check if the confirm password given is valid
    $query="SELECT acc_password FROM account
    WHERE acc_password = :passwd AND acc_username = :username
    LIMIT 1";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array(':passwd' => $sanitizedConfirmPassword, ':username' => $_SESSION['player_name']));
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Account : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    $data = $sth->fetchAll();
    if(empty($data))
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Le mot de passe est incorrect.';
        header("location: index.php?page=account");
        die();
    }

    #endregion

    #region Change password

    // if the user wants to change his password
    // if(isset($_POST['passwd']) && $_POST['passwd'] != ''){
    //     if(isset($_POST['passwd2']) && $_POST['passwd2'] != ''){

    //         //check if the 2 given passwd are the same
    //         $password = $_POST['passwd'];
    //         $password2 = $_POST['passwd2'];
    //         if($password != $password2)
    //         {
    //             $database->mysql->rollBack();
    //             $_SESSION['errorMsg'] = "Les mots de passe ne correspondent pas.";
    //             header("location: index.php?page=account");
    //             die();
    //         }

    //         //sanitize the new password (mir)
    //         $sanitizedNewPassword = validateLoginPassword($password, '', true);

    //         //sanitize the new password (elkarte)
    //         $sanitizedNewPasswordElkarte = validateLoginPassword($password, '', true);

    //         // finally modify the password (mir)
    //         $query="UPDATE account SET acc_password = ".$sanitizedNewPassword."
    //         WHERE acc_password = :passwd AND acc_username = :username";
    //         $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    //         try
    //         {
    //             $sth->execute(array(':passwd' => $sanitizedConfirmPassword, ':username' => $_SESSION['player_name']));
    //         }
    //         catch (PDOException $e)
    //         {
    //             $database->mysql->rollBack();
            
    //             // Display a friendly error message to the user.
    //             $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
    //             $err = 'Erreur dans la requête SQL Account : ' . $e->getMessage() . ' /// ' . $query;
        
    //             // (Logging)
    //             $log->addLog($err, 'error', $_SESSION['id_player']);
        
    //             // Reload the page
    //             header("Location: ".$_SERVER['PHP_SELF']);
    //             die();
    //         }

    //         // finally modify the password (elkarte)
    //         $query="UPDATE elkarte_members SET passwd = ".$sanitizedNewPasswordElkarte."
    //         WHERE passwd = :passwd AND member_name = :username";
    //         $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    //         try
    //         {
    //             $sth->execute(array(':passwd' => $sanitizedConfirmPassword, ':username' => $_SESSION['player_name']));
    //         }
    //         catch (PDOException $e)
    //         {
    //             $database->mysql->rollBack();
            
    //             // Display a friendly error message to the user.
    //             $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
    //             $err = 'Erreur dans la requête SQL Account : ' . $e->getMessage() . ' /// ' . $query;
        
    //             // (Logging)
    //             $log->addLog($err, 'error', $_SESSION['id_player']);
        
    //             // Reload the page
    //             header("Location: ".$_SERVER['PHP_SELF']);
    //             die();
    //         }

    //         $database->mysql->commit();
    //         $_SESSION['successMsg'] = "Votre mot de passe a été modifié.";
    //         header("location: index.php?page=account");
    //         die();

    //     }
    // }

    #endregion

    #region change email

    // if the user wants to change his email
    // if(isset($_POST['email']) && $_POST['email'] != ''){

    //     //check if the 2 given passwd are the same
    //     $email = $_POST['email'];
    //     $email2 = $_POST['emailVerif'];
    //     if($email != $email2)
    //     {
    //         $database->mysql->rollBack();
    //         $_SESSION['errorMsg'] = "Les addresses ne correspondent pas.";
    //         header("location: index.php?page=account");
    //         die();
    //     }

    //     // sanitize email
    //     $sanitizedEmail = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    //     // change email (mir)
    //     $query="UPDATE account SET email = ".$sanitizedEmail."
    //     WHERE acc_password = :passwd AND acc_username = :username";

    //     $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    //     try
    //     {
    //         $sth->execute(array(':passwd' => $sanitizedConfirmPassword, ':username' => $_SESSION['player_name']));
    //     }
    //     catch (PDOException $e)
    //     {
    //         $database->mysql->rollBack();
            
    //         // Display a friendly error message to the user.
    //         $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
    //         $err = 'Erreur dans la requête SQL Account : ' . $e->getMessage() . ' /// ' . $query;
    
    //         // (Logging)
    //         $log->addLog($err, 'error', $_SESSION['id_player']);
    
    //         // Reload the page
    //         header("Location: ".$_SERVER['PHP_SELF']);
    //         die();
    //     }

    //     // change email (elkarte)
    //     $query="UPDATE elkarte_members SET email_address = ".$sanitizedEmail."
    //     WHERE passwd = :passwd AND member_name = :username";

    //     $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    //     try
    //     {
    //         $sth->execute(array(':passwd' => $sanitizedConfirmPassword, ':username' => $_SESSION['player_name']));
    //     }
    //     catch (PDOException $e)
    //     {
    //         $database->mysql->rollBack();
            
    //         // Display a friendly error message to the user.
    //         $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
    //         $err = 'Erreur dans la requête SQL Account : ' . $e->getMessage() . ' /// ' . $query;
    
    //         // (Logging)
    //         $log->addLog($err, 'error', $_SESSION['id_player']);
    
    //         // Reload the page
    //         header("Location: ".$_SERVER['PHP_SELF']);
    //         die();
    //     }

    //     $database->mysql->commit();
    //     $_SESSION['successMsg'] = "Votre address mail a été modifiée.";
    //     header("location: index.php?page=account");
    //     die();
    // }

    #endregion

}

