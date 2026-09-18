<?php


if (!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

// Seul un admin peut visualiser la page
//if ($_SESSION['is_admin'] != '1'){
if ($_SESSION['id_player'] != '1'){
    header("Location:index.php?page=".$_SESSION['player_area']);
    die();
}

$database = new Database();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);

function getReportMessageList($database, $log)
{
    $err = '';
    $reportMessageList = [];
    $reportMessageListHTML = '';

    $query = 'SELECT fm.id_message, fm.message_content, fr.report_details
                FROM forum_report fr INNER JOIN forum_message fm ON fr.id_message = fm.id_message
                LIMIT 100';
    try 
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            while($data=$res->fetch(PDO::FETCH_ASSOC))
            {
                $reportMessageList['id_message'][] = $data['id_message'];
                $reportMessageList['message_content'][] = $data['message_content'];
                $reportMessageList['report_details'][] = $data['report_details'];
            }
        }
    } 
    catch (PDOException $e)
    {
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
        
        // (Logging)
        $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
        $log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
    }

    $reportMessageListHTML = formatForumMessageList($reportMessageList);

    return $reportMessageListHTML;
}


function formatForumMessageList($list)
{
    // Initialize $n even if no result returned
    if(!isset($list['id_message']))
        $n=0;
    else
        $n = sizeof($list['id_message']);

    $listHTML = '';
    for ($i=0; $i<$n; $i++)
    {
        $reportedContent = $list['message_content'][$i];
        $providedDetails = $list['report_details'][$i];
        $idMessageReported = $list['id_message'][$i];
    
        $listHTML .= 
        '<tr>
            <td>'.$reportedContent.'</td>
            <td>'.$providedDetails.'</td>
            <td><button class="btn-delete-reported-message" data-id-message="'.$idMessageReported.'"</td>
        </tr>';
    }

    return $listHTML;
}

/**
 * Count the number of distinct reported messages for each reported player, to know if they have crossed a limit.
 */
function getFrequentlyReportedPlayersList($database, $log)
{
    $err = '';
    $playersList = [];
    $playersListHTML = '';

    // find DISTINCT values in a particular field, count the number of occurrences of that value and then order the results by the count.
    // https://stackoverflow.com/questions/1346345/count-the-occurrences-of-distinct-values 
    /**
     * example db
     *
     *  id         name
     *  -----      ------
     *  1          Mark
     *  2          Mike
     *  3          Paul
     *  4          Mike
     *  5          Mike
     *  6          John
     *  7          Mark
     *
     *  expected result
     *
     *  name       count
     *  -----      -----
     *  Mike       3
     *  Mark       2
     *  Paul       1
     *  John       1
     * 
     * SELECT name,COUNT(*) as count 
     * FROM tablename 
     * GROUP BY name 
     * ORDER BY count DESC;
     * 
     */
    
    $query = 'SELECT fm.id_author, COUNT(*) as nbr_reports
                FROM forum_report fr INNER JOIN forum_message fm ON fr.id_message = fm.id_message
                GROUP BY fr.id_message
                ORDER BY nbr_reports';

    $query = 'SELECT id_message,COUNT(*) as nbr_reports
        FROM forum_report
        GROUP BY id_message
        ORDER BY nbr_reports DESC';  

    try 
    {
        $res = $database->mysql->query($query);
        if($res)
        {
            while($data=$res->fetch(PDO::FETCH_ASSOC))
            {
                $playersList['id_author'][] = $data['id_message'];
                $playersList['nbr_reports'][] = $data['nbr_reports'];
            }
        }
    } 
    catch (PDOException $e)
    {
        // Display a friendly error message to the user.
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
        
        // (Logging)
        $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
        $log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
    }

    $playersListHTML = formatForumMessageList($playersList);

    return $playersListHTML;
}

function formatFrequentlyReportedPlayersList($list)
{
    // Initialize $n even if no result returned
    if(!isset($list['id_author']))
        $n=0;
    else
        $n = sizeof($list['id_author']);

    $listHTML = '';
    for ($i=0; $i<$n; $i++)
    {

        $nbrReportsOfSamePlayer = $list['nbr_reports'][$i];
        $idPlayerReported = $list['id_author'][$i];
    
        $listHTML .= 
        '<tr>
            <td>'.$idPlayerReported.'</td>
            <td>'.$nbrReportsOfSamePlayer.'</td>
            <td><button class="btn-give-avertissement-to-reported-player" data-id-player="'.$idPlayerReported.'"</td>
            <td><button class="btn-ban-reported-player-1-day" data-id-player="'.$idPlayerReported.'"</td>
            <td><button class="btn-ban-reported-player-1-week" data-id-player="'.$idPlayerReported.'"</td>
            <td><button class="btn-ban-reported-player-life" data-id-player="'.$idPlayerReported.'"</td>
        </tr>';
    }

    return $listHTML;
}