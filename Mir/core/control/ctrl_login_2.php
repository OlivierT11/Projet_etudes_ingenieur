<?php

// !!!!!!!!!!!!!!!!!!!!!
// OLD file, BEFORE google auth. Part of the content has been moved to class.account.php
// !!!!!!!!!!!!!!!!!!!!!!!

//if this parameter is sent in the url, then the users wants to disconnect
if(isset($_GET['action']) && $_GET['action'] == 'logout'){
    
    //destroy and recreate the session
    unset($_SESSION);
    session_destroy();
    session_start();

    //destroy the connection cookie

        //

    header("location: index.php?page=login");
} 

//check if the guest is connected (prevent backspace + prevent logging in if already logged in)
if(isset($_SESSION['player_is_logged_in']) && $_SESSION['player_is_logged_in'] != 0){
    header("location: index.php?page=".$_SESSION['player_area']);
}

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

// Do the input sanitizing things, and either register or login the user depending on the existing fiels in post().
if($_SERVER["REQUEST_METHOD"] == "POST") {

    //error handling other than required

        //

    // "raw" inputs
    $sanitizedUsername = $_POST['username'];
    $sanitizedPasswd = $_POST['password'];

    //sanitize inputs and hash password
    //$sanitizedUsername = validateInput($_POST['username']);
    ///$sanitizedPasswd = validateLoginPassword($password, '', $sanitizedUsername, true);


    //cookie login/logout

    // ...

    //force password to be 10 char long

    // ...

    //spam / bot / bruteforce protection

    // ...

    //check for banned players

    // ...

    //If the user wants to login
    if(isset($_POST['username']) && $_POST['username'] != ''){
        loginUser($database, $log, $sanitizedUsername, $sanitizedPasswd);
        $database->mysql->commit();
        header("location: index.php?page=". $_SESSION['player_area']);
        die();
    }
    
    // If the user wants to register
    if(isset($_POST['usernameRegister']) && $_POST['usernameRegister'] != ''){
        registerUser($database);
        $database->mysql->commit();
        header("location: index.php?page=home");
        die();
    }

    //si ces 2 champs sont remplis ??

    //register or login complete ? go to the correct page.

}


function loginUser($database, $log, $user, $passwd){

    #region Initialize session variables needed later

    // Display chatboxes by default
    $_SESSION['is_cb_hidden'] = 0;

    // No era on home !
    $_SESSION['era'] = '';

    // The abyss is closed unless changed from cron.php
    $_SESSION['abyss_is_open'] = 0;

    #endregion

    // Envoie le joueur vers home.php s'il n'est dans aucune ville
    $playerIsInsideCity = checkIfPlayerInCity($database, $user, $passwd);

    if($playerIsInsideCity == 0){

        // The user is not inside a city. Connect the user with the data not linked to a city
        $query='SELECT p.id_player, p.id_city, p.id_dj, p.id_abyss, p.id_camp, p.player_area, p.player_pos_x, p.player_pos_y, 
                p.player_name, p.player_thumbs,
                a.is_new, a.acc_token, a.email
            FROM player p INNER JOIN account a ON a.id_player = p.id_player
            WHERE acc_username= :username AND acc_password= :passwd';

        try
        {
            $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':username' => $_POST['username'], ':passwd' => $_POST['password']));  
            $data = $sth->fetchAll();
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();

            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
            $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

            // (Logging)
            $log->addLog($err, 'error', $_SESSION['id_player']);

            // Reload the page
            header("Location: ".$_SERVER['PHP_SELF']);
            die();
        }

        //player vars
        $_SESSION['id_player'] = $data[0]['id_player'];
        $_SESSION['id_city'] = $data[0]['id_city'];
        $_SESSION['player_area'] = $data[0]['player_area'];
        $_SESSION['player_sub_area'] = $data[0]['player_area'];

        //sub area (plain, forest) will be checked on map loading.
        //sub area (dj, abyss, dj2, hell) will be checked on page loading
        $_SESSION['player_sub_area'] = '';

        $_SESSION['player_pos_x'] = $data[0]['player_pos_x'];
        $_SESSION['player_pos_y'] = $data[0]['player_pos_y'];
        $_SESSION['cb_token'] = $data[0]['acc_token'];
        $_SESSION['email'] = $data[0]['email'];

        $_SESSION['player_name'] = $data[0]['player_name'];

        //Redirect the user to the home page
        header("Location: index.php?page=home");
        die();
    }

    // If the player is inside a city, select player and city data on connection
    $query='SELECT p.id_player, p.id_city, p.id_dj, p.id_abyss, p.id_camp, p.player_area, p.player_pos_x, p.player_pos_y, 
                p.player_name, p.player_thumbs,
                plb.player_food, plb.player_morale,
                a.is_new, a.acc_token, a.email,
                city.city_name, city.city_img_path
            FROM (((player p
                INNER JOIN account a           ON a.id_player = p.id_player)
                INNER JOIN city                ON city.id_city = p.id_city)
                INNER JOIN player_life_bar plb ON p.id_player = plb.id_player)
            WHERE acc_username= :username AND acc_password= :passwd';

    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array(':username' => $_POST['username'], ':passwd' => $_POST['password']));
        $data = $sth->fetchAll();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();

        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    // Check if the user exists
    if($sth->rowCount() == 0)
    { 
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Nom d\'utilisateur ou mot de passe incorrect.';
        header("location: index.php?page=login");
        die();

    }

    //player vars
    $_SESSION['id_player'] = $data[0]['id_player'];
    $_SESSION['id_city'] = $data[0]['id_city'];
    $_SESSION['player_area'] = $data[0]['player_area'];
    $_SESSION['player_sub_area'] = $data[0]['player_area'];

    //sub area (plain, forest) will be checked on map loading.
    //sub area (dj, abyss, dj2, hell) will be checked on page loading
    $_SESSION['player_sub_area'] = '';

    $_SESSION['player_pos_x'] = $data[0]['player_pos_x'];
    $_SESSION['player_pos_y'] = $data[0]['player_pos_y'];
    $_SESSION['cb_token'] = $data[0]['acc_token'];
    $_SESSION['email'] = $data[0]['email'];

    //if the player is inside a dj. Used in loadMap() to load the dj area
    if($data[0]['id_dj'] != 0){
        $_SESSION['id_dj'] = $data[0]['id_dj'];
    }

    //if the player is inside dj2
    if($_SESSION['player_area'] == 'dj2'){
        $_SESSION['id_dj2'] = $data[0]['id_dj'];
    }

    //if the player is inside the abyss
    if($data[0]['id_abyss'] != 0){
        $_SESSION['id_abyss'] = $data[0]['id_abyss'];
    }

    //if the player is inside hell
    if($_SESSION['player_area'] == 'hell'){
        $_SESSION['id_hell'] = $data[0]['id_abyss'];
    }

    // Si le joueur est dans un camp
    if($data[0]['id_camp'] != 0){

        // Vérifier que le camp existe toujours

        
        $_SESSION['player_area'] = 'camp';
    }
    
    $_SESSION['player_name'] = $data[0]['player_name'];
    //$_SESSION['player_title'] = $data[0]['player_title'];
    
    //life indicators -> dans ctrl_life_bars.php car appel à chaque page.
    //  $_SESSION['player_food'] = $data['player_food'];
    //  $_SESSION['player_morale'] = $data['player_morale'];
    //  $_SESSION['player_shield'] = $data['player_shield'];
    //  $_SESSION['player_head'] = $data['player_head'];
    //  $_SESSION['player_mask'] = $data['player_mask'];
    //  $_SESSION['player_upper'] = $data['player_upper'];
    //  $_SESSION['player_lower'] = $data['player_lower'];
    
    //city vars
    $_SESSION['city_name'] = $data[0]['city_name'];
    $_SESSION['city_img_path'] = $data[0]['city_img_path'];
    
    $_SESSION['is_new'] = $data[0]['is_new'];





    #region Player Stats

    $action = new Action($database, $log);
    $stat = new Stat($database, $log, $action);

    // Get the stat list
    $_SESSION['player_stat'] = $stat->getPlayerStats();

    // Define the max invent size based on the "carry" stat
    $_SESSION['max-invent'] = $stat->defineMaxInventSize($_SESSION['player_stat']);

    // Define the master session, if the lvl of the best stat is worth twice the value of the second.
    $_SESSION['is_master'] = 0;
    $_SESSION['master_stat'] = '';

    $stat->defineMasterStat($_SESSION['player_stat'], $_SESSION['is_master'], $_SESSION['master_stat']);

    
    #endregion

    
    // Get the local player's statistics
    $_SESSION['player_local_statistics'] = array();
    
    $query='SELECT slp.amount, s.statistic_name, s.statistic_descr
                FROM `statistic` s INNER JOIN statistic_local_player slp ON slp.id_statistic = s.id_statistic
                WHERE slp.id_player = '.$_SESSION['id_player'].'
                ORDER BY slp.id_statistic';
    try 
    {
        $res=$database->mysql->query($query);
        $name = '';
        if ($res) 
        {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) 
            {
                $name = $data['statistic_name'];
                $_SESSION['player_local_statistics'][$name]['amount'] = $data['amount'];
                $_SESSION['player_local_statistics'][$name]['descr'] = $data['statistic_descr'];
            }
            unset($name);
        }
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    //login session variables
    $_SESSION['player_is_logged_in'] = 1;

    //go to the correct page
    /*
    if($_SESSION['is_new']){
        $query = "UPDATE account SET is_new=0";
        $database->mysql->query($query);
        unset($_SESSION['is_new']);
        header("location: index.php?page=horlogis");
    } else {
        header("location: index.php?page=".$_SESSION['player_area']);
    }*/

    //get max-city-invent 
    
    // get effects of built buildings if inside a city
    if($_SESSION['id_city'] != 0)
    {
        $_SESSION['id_bld'] = [];

        $query="SELECT id_bld FROM building_city WHERE id_city=".$_SESSION['id_city']." AND is_built=1";
        $result=[];
        try
        {
            $res = $database->mysql->query($query);
            if($res)
            {
                // Empty array is no building built
                //if(count($res->fetchAll(PDO::FETCH_ASSOC)) > 0)
                //{
                    while($data=$res->fetch(PDO::FETCH_ASSOC))
                    {
                        $_SESSION['id_bld'][] = $data['id_bld']; // access with in_array
                    }
                //}
                //else
                //{
               //     $_SESSION['id_bld'] = [];
               // }
            }
        } 
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
            $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

            // (Logging)
            $log->addLog($err, 'error', $_SESSION['id_player']);

            // Reload the page
            header("Location: ".$_SERVER['PHP_SELF']);
            die();
        }
    }
}

function registerUser($database){

    // Get posted values
    $username = $_POST['usernameRegister'];
    $password = $_POST['passwordRegister'];
    $passwordVerify = $_POST['passwordVerif'];
    if(isset($_POST['email'])){
        $email = $_POST['email'];
        $emailVerify = $_POST['emailVerif'];
    }

    // The username must be below 20 chars
    $maxUserNameLenght = 20;
    if(strlen($username) > $maxUserNameLenght)
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Votre nom d\'utilisateur doit faire moins de 20 charactères.';
        header("location: index.php?page=login");
        die();
    }

    // The username must start with a capital.
    if(preg_match('/[^A-Z]/', substr( $username, 0, 1 ) ))
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Votre nom d\'utilisateur doit commencer par une majuscule.';
        header("location: index.php?page=login");
        die();
    }

    // The username must not contain any spacial char or number, except space
    if (preg_match('/[^a-zA-Z\s]+/', $username))
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Votre nom d\'utilisateur ne doit pas contenir de charactères spéciaux, d\'accents ou de chiffres.';
        header("location: index.php?page=login");
        die();
    }

    // sanitize inputs
    $sanitizedUsername = validateInput($username);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
      }

    //create pwd hash for mir
    $hashedPasswordMir = password_hash($password, PASSWORD_DEFAULT);

    //create pwd hash for elkarte DB
    $sanitizedPassword = validateLoginPassword($password, '', $sanitizedUsername, true);

    // do inputs correspond ?
    if($_POST['passwordRegister'] != $_POST['passwordVerif'])
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Les mots de passe ne correspondent pas.';
        header("location: index.php?page=login");
        die();
    }
    if($_POST['email'] != $_POST['emailVerif'])
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Les adresses mail ne correspondent pas.';
        header("location: index.php?page=login");
        die();
    }

    //do the account already exists ? BUG

        //if the user has given an email, use it.
    if($_POST['email']) {
        $query="SELECT acc_password, email FROM account
            WHERE acc_password = :passwd AND email = :email
            LIMIT 1";
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':passwd' => $sanitizedPassword, ':email' => $sanitizedEmail));
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
            $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

            // (Logging)
            $log->addLog($err, 'error', $_SESSION['id_player']);

            // Reload the page
            header("Location: ".$_SERVER['PHP_SELF']);
            die();
        }
    }

    //if the user has not given an email, use username (less secure).
    else {
        $query="SELECT acc_username, acc_password FROM account
            WHERE acc_password = :passwd AND acc_username = :username
            LIMIT 1";
        $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

        try
        {
            $sth->execute(array(':passwd' => $sanitizedPassword, ':username' => $sanitizedUsername));
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
            $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

            // (Logging)
            $log->addLog($err, 'error', $_SESSION['id_player']);

            // Reload the page
            header("Location: ".$_SERVER['PHP_SELF']);
            die();
        }
    }

    $data = $sth->fetchAll();
    if(!empty($data))
    {
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Ce compte existe déjà.';
        header("location: index.php?page=login");
        die();
    }


    //the account do not exists ? proceed to register the user
    $numberOfStats = 12;

    //get user IP
    $userIP = $_SERVER['REMOTE_ADDR'];

    //get register date to fit with the elkarte DB (10 digits)
    //$registerDate = (int) microtime(true); //equivalent
    $registerDate = time();

    //create account token to connect to node.js
    $bytes = bin2hex(random_bytes(255)); //more than 255 chars
    $token = substr($bytes,255); //255 chars for DB

    ///amèl : use transactions https://stackoverflow.com/questions/5178697/mysql-insert-into-multiple-tables-database-normalization 

    //populate the player table
    $query="INSERT INTO player(player_name)
            VALUES (:username)";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array(':username' => $sanitizedUsername));
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    //select the last account/player id
    $newIdPlayer = $database->mysql->lastInsertId();

    //populate the account table
    $query="INSERT INTO account(id_player, acc_username, acc_password, acc_token, user_ip, register_date, is_new)
            VALUES (".$newIdPlayer.", :username, :passwd, '".$token."', :ip, :regDate, 1)";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array
        (
            ':username' => $sanitizedUsername, 
            ':passwd' => $hashedPasswordMir, 
            ':ip' => $userIP,
            ':regDate' => $registerDate
        ));
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    //populate the player stat table
    $query="INSERT INTO player_stat(id_player, id_stat)
            VALUES (:id, :id_stat)";
    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    for($i=1; $i<=$numberOfStats; $i++)
    {
        try
        {
            $sth->execute(array(':id' => $newIdPlayer, ':id_stat' => $i));
        }
        catch (PDOException $e)
        {
            $database->mysql->rollBack();
            
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
            $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

            // (Logging)
            $log->addLog($err, 'error', $_SESSION['id_player']);

            // Reload the page
            header("Location: ".$_SERVER['PHP_SELF']);
            die();
        }
    }

    //populate the player global statistics table

        //

    //populate the elkarte account table
    $mysqlElkarte = getMyElkarteConnection();
    $query="INSERT INTO elkarte_members(member_name, date_registered, real_name, passwd, hide_email, member_ip, member_ip2, buddy_list, message_labels, openid_uri,
                    `signature`, ignore_boards)
            VALUES (:username, :regDate, :username, :passwd, 1, :ip, :ip, '', '', '', '', '')";
    $sth = $mysqlElkarte->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    
    try
    {
        $sth->execute(array
        (
            ':username' => $username,
            ':regDate' => $registerDate,
            ':passwd' => $sanitizedPassword,
            ':ip' => $userIP
        ));
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    // Registration complete ! 
    $_SESSION['validMsg'] = 'Votre compte a été créé avec succès.';
}

// Basic input validation. To use on everything that has no special chars in it (username but not password)
// The password validation has its own function.
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

/**
 * Checks whether an entered password is correct for the user
 *
 * What it does:
 *
 * - called when logging in or whenever a password needs to be validated for a user
 * - used to generate a new hash for the db, used during registration or any password changes
 * - if a non SHA256 password is sent, will generate one with SHA256(user + password) and return it in password
 *
 * @package Authorization
 * @param string $password user password if not already 64 characters long will be SHA256 with the user name
 * @param string $hash hash as generated from a SHA256 password
 * @param string $user user name only required if creating a SHA-256 password
 * @param boolean $returnhash flag to determine if we are returning a hash suitable for the database
 */
function validateLoginPassword(&$password, $hash, $user = '', $returnhash = false)
{
	// Our hashing controller
	require_once dirname(dirname(__FILE__)) . '/class/PasswordHash.php';

	// Base-2 logarithm of the iteration count used for password stretching, the
	// higher the number the more secure and CPU time consuming
	$hash_cost_log2 = 10;

	// Do we require the hashes to be portable to older systems (less secure)?
	$hash_portable = false;

	// Get an instance of the hasher
	$hasher = new PasswordHash($hash_cost_log2, $hash_portable);

	// If the password is not 64 characters, lets make it a (SHA-256)
	if (strlen($password) !== 64)
		$password = hash('sha256', mb_strtolower($user, 'UTF-8') . un_htmlspecialchars($password));

	// They need a password hash, something to save in the db?
	if ($returnhash)
	{
		$passhash = $hasher->HashPassword($password);

		// Something is not right, we can not generate a valid hash that's <20 characters
		if (strlen($passhash) < 20)
			$passhash = false;
	}
	// Or doing a password check?
	else
	 	$passhash = (bool) $hasher->CheckPassword($password, $hash);

	unset($hasher);

	return $passhash;
}


function un_htmlspecialchars($string)
{
	$string = htmlspecialchars_decode($string, ENT_QUOTES);
	$string = str_replace('&nbsp;', ' ', $string);

	return $string;
}

function checkIfPlayerInCity($database, $user, $passwd){

    $query='SELECT p.id_city
            FROM player p INNER JOIN account a ON p.id_player = a.id_player
            WHERE a.acc_username= :username AND a.acc_password= :passwd
            LIMIT 1';

    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array(':username' => $user, ':passwd' => $passwd));
        $data = $sth->fetchAll();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    // Check if the user exists
    if($sth->rowCount() == 0)
    { 
        $database->mysql->rollBack();
        $_SESSION['errorMsg'] = 'Nom d\'utilisateur ou mot de passe incorrect.';
        header("location: index.php?page=login");
        die();
 
    }

    if($data[0]['id_city'] == 0)
        return 0;
    else   
        return 1;

}

// reinitialize password 
function resetPasswd($database){

    // Create a unique GUID for the user
    $guid = com_create_guid();

    // Hash the GUID with the password method
    $hashedGuid = validateLoginPassword($guid, '', '', true);

    // Store the GUID in database, with 1 day expiry date
    $query='INSERT INTO passwd_guid(id_player, `datetime`
            FROM player p INNER JOIN account a ON p.id_player = a.id_player
            WHERE a.acc_username= :username AND a.acc_password= :passwd
            LIMIT 1';

    $sth = $database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

    try
    {
        $sth->execute(array(':username' => $user, ':passwd' => $passwd));
        $data = $sth->fetchAll();
    }
    catch (PDOException $e)
    {
        $database->mysql->rollBack();
            
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer.";
        $err = 'Erreur dans la requête SQL Login : ' . $e->getMessage() . ' /// ' . $query;

        // (Logging)
        $log->addLog($err, 'error', $_SESSION['id_player']);

        // Reload the page
        header("Location: ".$_SERVER['PHP_SELF']);
        die();
    }

    // Send email with the GUID
}

// Send email on password forgot
function sendMail($email, $company, $location, $startAt, $endAt, $date, $userId,$duration)
{
    $to = $email; /* '. ',';  separated by comma for another email (useful if you want to keep records(sending to yourself))*/;
    $subject = 'INSERT_SUBJECT_HERE';

    $bound_text = "----*%$!$%*";
    $bound = "--".$bound_text."\r\n";
    $bound_last = "--".$bound_text."--\r\n";

    $headers = "From: noreply@somewhere.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; boundary=\"$bound_text\""."\r\n" ;

    $message = " you may wish to enable your email program to accept HTML \r\n".
            $bound;

    $message .=
    'Content-Type: text/html; charset=UTF-8'."\r\n".
    'Content-Transfer-Encoding: 7bit'."\r\n\r\n".
    '
<!-- here is where you format the email to what you need, using html you can use whatever style you want (including the use of images)-->
            <BODY BGCOLOR="White">
            <body>
            <div Style="align:center;">
            <p>
            <img src="IMAGE_URL" alt= "IMAGE_NAME">
            </p>
            </div>
            </br>
            <div style=" height="40" align="left">

            <font size="3" color="#000000" style="text-decoration:none;font-family:Lato light">
            <div class="info" Style="align:left;">

            <p>information here<!--(im sure you know how to write html ;))--></p>

            <br>

            <p>Location:  '.$location.' <!-- $location is the variable you wish to insert as is $date etc --> </p>

            <p>Date:      '.$date.'      </p>

            <p>Time:      '.$startAt.'   </p>

            <p>Duration:  '.$duration.'  </p>

            <p>Company:   '.$company.'   </p>

                '. /* <p>Charge:    '.$charge.'    </p> */'
            <br>

                <p>Reference Number: '.$userId.'</p>

                            </div>

            </br>
            <p>-----------------------------------------------------------------------------------------------------------------</p>
            </br>
            <p>( This is an automated message, please do not reply to this message, if you have any queries please contact someone@someemail.com )</p>
            </font>
            </div>
            </body>
        '."\n\n".
                                                                    $bound_last;

    $sent = mail($to, $subject, $message, $headers); // finally sending the email


}