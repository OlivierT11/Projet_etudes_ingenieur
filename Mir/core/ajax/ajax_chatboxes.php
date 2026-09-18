<?php
if(!isset($_SESSION))
session_start();

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    die();
}


/*********** */
/* Chatboxes */
/*********** */

if (!isset($_SESSION['is_cb_hidden']))
{
    $_SESSION['is_cb_hidden'] = 0;
}

// Check the Show / Hide state of the chatboxes from session (false by default)
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'check-cb') {
    
    $_SESSION['is_cb_hidden'] == 1 ? ($state = '25px') : ($state = '280px');
    echo $state;

}
// Keep in memory the Show / Hide state of the chatboxes.
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'hide-cb') {
    
    // Toggle the CB display state
    $_SESSION['is_cb_hidden'] = $_REQUEST['hidden'];
    echo $_SESSION['is_cb_hidden'];

}