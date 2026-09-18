<?php

#region Session

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Prevent backtab
if(isset($_SESSION['player_area']) && $_SESSION['player_area'] != 'inside')
{
    header("location: index.php?page=".$_SESSION['player_area']);
    die();
}

#endregion

#region File inclusions

$database = new Database();
$log = new Log();

require dirname(dirname(__FILE__))."/class/class.law.php";

#endregion

// Get the data of the laws to display
// TODO mettre dans class.law.php mais pb : on ne peut pas créer d'objet law dans la classe LAW. Il faudrait ajouter un namespace DataLayer qui prend en charge les appels BDD
function getLawsList($database, $log) : array
{
    $lawsList = [];
    $err = '';
    $_SESSION['session_Id_Array_LawList'] = [];
    $i = 0;

    $query="SELECT l.law_name, l.law_descr, l.is_TOR, l.id_parent_law, l.is_child_law, l.is_major,
                   lc.id_law_city, lc.current_value, lc.is_proposed, lc.value_propose,
                   p.player_name
            FROM law l 
            INNER JOIN law_city lc ON l.id_law = lc.id_law
            INNER JOIN player p ON p.id_player = lc.id_player_propose
            WHERE lc.id_city=".$this->idCity;
    try
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            while($data=$res->fetch(PDO::FETCH_ASSOC)) 
            {
                $law = new Law();
                
                $law->idLawToDisplay = $i;
                $law->lawName = $data['law_name'];
                $law->lawDescr = $data['law_descr'];
                $law->isTor = $data['is_TOR'];
                $law->idParentLaw = $data['id_parent_law'];
                $law->isChildLaw = $data['is_child_law'];
                $law->isMajor = $data['is_major'];
                $law->idLawCity = $data['id_law_city'];
                $law->currentValue = $data['current_value'];
                $law->isProposed = $data['is_proposed'];
                $law->proposedValue = $data['value_propose'];
                $law->playerPropose = $data['player_name'];

                $lawList[] = $law;

                // Create the law Array session ID
                $_SESSION['session_Id_Array_LawList'][$i] = $data['id_law_city'];
                $i++;
            }
        }
    }
    catch (PDOException $e)
    {
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
        
        $err = 'Erreur dans la requête SQL de la fonction getlawlist : '.$e->getMessage();
        
        $log->addLog($err, 'error', $_SESSION['id_player']);

        die();
    }

    return $lawsList;
}

