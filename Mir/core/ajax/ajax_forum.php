<?php
// Appels AJAX du forum

if(!isset($_SESSION))
    session_start();
    
if(!isset($_SESSION['id_player']))
{
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    //header("location: index.php?page=login");
    die();
}

#region Classes

//loadCoreClassesForAjaxFiles(); //database, log, stat etc

require dirname(dirname(__FILE__))."/config/config.php";

require dirname(dirname(__FILE__))."/class/class.constants.php";

require dirname(dirname(__FILE__))."/class/class.database.php";
$database = new Database();

require dirname(dirname(__FILE__))."/tools/tools.php";

require dirname(dirname(__FILE__))."/class/class.log.php";
$log = new Log();

require dirname(dirname(__FILE__))."/class/class.cache.php";
$cache = new Cache($_SESSION['id_city']);

require dirname(dirname(__FILE__))."/class/class.news.php";
$news = new News($database, $log);

require dirname(dirname(__FILE__))."/class/class.action.php";
$action = new Action($database, $log);

require dirname(dirname(__FILE__))."/class/class.stat.php";
$stat = new Stat($database, $log, $action);

require dirname(dirname(__FILE__))."/class/class.statistic.php";
$statistic = new Statistic($database, $log, $news);

require dirname(dirname(__FILE__))."/class/class.forum.php";
$forum = new Forum($database, $log);

#endregion

// The user creates a topic
if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'addTopic')
{
    #region input sanitation
    $title = "";
    $message = "";
    $date = $forum->createDateCarbonFormat(); 
    $hasSurvey = "0";
    $idBoard = 0;
    $authorizeMultipleChoices = "0";
    $surveyTitle = "";
    $arraySurveyAnswers = [];

    $title = htmlspecialchars(trim($_POST['title']));
    if ($title == "")
    {
        $_SESSION['errorMsg'] = "Erreur : Titre vide.";
        echo "err";
        die();
    }

    $message = htmlspecialchars(trim($_POST['message']));
    if ($message == "")
    {
        $_SESSION['errorMsg'] = "Erreur : Message vide.";
        echo "err";
        die();
    }

    // Forum local = pas de idBoard
    if (isset($_POST['idBoard']) && $_POST['idBoard'] != 0)
    {
        $idBoard = htmlspecialchars(trim($_POST['idBoard']));
        if(!is_numeric($_POST['idBoard']) || $idBoard < 0)
        {
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
            $log->addLog('url-user-submitted id_board invalide', 'error', $_SESSION['id_player']);
            echo "err";
            die();
        }
    }

    $hasSurvey = htmlspecialchars($_POST['topicHasSurvey']); 
    $authorizeMultipleChoices = htmlspecialchars($_POST['authorizeMultipleChoices']); 
    if($hasSurvey)
    {
        $surveyTitle = htmlspecialchars(trim($_POST['topicSurveyTitle']));
        if ($surveyTitle == "")
        {
            $_SESSION['errorMsg'] = "Erreur : Titre du sondage vide.";
            echo "err";
            die();
        }

        $arraySurveyAnswers = json_decode($_POST['topicArraySurveyAnswers']);
        if(sizeof($arraySurveyAnswers) < 2)
        {
            $_SESSION['errorMsg'] = "Erreur : Inclure au moins deux choix dans le sondage.";
            echo "err";
            die();
        }

        foreach ($arraySurveyAnswers as $answer)
        {
            // encode for safety
            $answer = htmlspecialchars(trim($answer));

            // Error if empty
            if($answer === '')
            {
                $_SESSION['errorMsg'] = "Erreur : L'un des choix du sondage est vide.";
                echo "err";
                die();
            }
        }
    }

    #region

    // Transform the smiley strings etc
    $message = $forum->parseMessageEmotesToDisplay($message);

    $database->mysql->beginTransaction();

    $forum->addNewTopic($title, $message, $idBoard, $date, $hasSurvey, $authorizeMultipleChoices, $surveyTitle, $arraySurveyAnswers);

    $database->mysql->commit();

    $_SESSION['validMsg'] = "Info : La création est validée.";
}

// Get topic list with pagination
// TODO : PAS BON : recharger la page avec le numéro de la page en paramètre dans l'URL. Appeler getForumMessageList normalement car le paramètre &topic existe.
// if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'GetTopicListPager')
// {
//     #region Input Sanitation

//     $idBoard = htmlspecialchars(trim($_POST['idBoard']));
//     $page = htmlspecialchars(trim($_POST['page']));

//     #endregion

//     $forum->getForumTopicList($idBoard, $page);
// }

// Navigate to a topic content
// useless, check id when loading the messages of a topic
// if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'goToTopicMessages')
// {
//     // Sanitize input
//     $id = htmlspecialchars($_POST['id']);

//     // Check the ID
//     if(!isset($_SESSION['session_Id_Array_TopicList'])) { throw new Exception('TopicIdNoMatch'); }

//     $idBDD = $_SESSION['session_Id_Array_TopicList'][$id];
        
// }

// The user creates a message
if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'addMessage')
{
    #region input sanitation

    $message = "";
    $idTopic = "";

    // $message = htmlspecialchars(trim($_POST['messageToAdd'])); //les ( ) et : sont-ils des special chars ?
    $message = htmlspecialchars($_POST['messageToAdd']); // Pas de trim car espace avant et après chaque smiley dans le parseur.
    
    // Message is too long
    if(strlen($message) > Constants::$forum_max_message_length)
    {
        $_SESSION['errorMsg'] = "Votre message est trop long (". Constants::$forum_max_message_length . " caractères max).";
        echo "err";
        die();
    }

    // Id topic modifié manuellement
    if(is_numeric($_POST['idTopic'])) // trim avant ?
    {
        $idTopic = htmlspecialchars(trim($_POST['idTopic']));
    }
    else
    {
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
        $log->addLog('url-user-submitted id_topic invalide', 'error', $_SESSION['id_player']);
        echo "err";
        die();
    }

    // Compare topic id

    #region

    // Transform the smiley strings etc
    $message = $forum->parseMessageTextDecorationToStore($message);

    $database->mysql->beginTransaction();

    $forum->addNewMessage($idTopic, $message);

    $database->mysql->commit();

    $_SESSION['validMsg'] = "Info : Votre message a été ajouté.";
}


// The user modifies a message
if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'editMessage')
{
    #region input sanitation

    $message = '';
    // $idBoard = "";
    // $idTopic = "";
    $idMessage = '';

    // if(is_numeric($_POST['idBoard'])) // trim avant ?
    // {
    //     $idBoard = htmlspecialchars(trim($_POST['idBoard']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_board invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdBoard";
    //     die();
    // }
    
    // if(is_numeric($_POST['idTopic'])) // trim avant ?
    // {
    //     $idTopic = htmlspecialchars(trim($_POST['idTopic']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_topic invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdTopic";
    //     die();
    // }

    // if(is_numeric($_POST['idMessage']))
    // {
    //     $idMessage = htmlspecialchars(trim($_POST['idMessage']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_message invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdMessage";
    //     die();
    // }

    // As we do not delete messages yet, we want to prevent empty messages
    if (strlen($_POST['messageToEdit']) <= 0)
    {
        $_SESSION['errorMsg'] = "Erreur : Merci de laisser au moins un caractère.";
        echo "err";
        die();
    }

    #region

    // Encode the smiley strings etc
    $idMessage = htmlspecialchars(trim($_POST['idMessage']));
    $message = htmlspecialchars(trim($_POST['messageToEdit']));

    $database->mysql->beginTransaction();

    // $forum->editMessage($message, $idMessage, $idTopic, $idBoard, $_SESSION['id_city'],  $_SESSION['id_player']);
    $forum->editMessage($message, $idMessage);

    $database->mysql->commit();

    $_SESSION['validMsg'] = "Info : Votre message a été édité.";
}

// The user signals a message
if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'reportMessage')
{
    #region input sanitation

    /*
    $message = "";
    $idBoard = "";
    $idTopic = "";
    */
    $idMessage = '';
    $detailText = '';

    /*
    $message = htmlspecialchars(trim($_POST['messageToReport'])); //les ( ) et : sont-ils des special chars ?

    if(is_numeric($_POST['idBoard'])) // trim avant ?
    {
        $idBoard = htmlspecialchars(trim($_POST['idBoard']));
    }
    else
    {
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
        $log->addLog('url-user-submitted id_board invalide', 'error', $_SESSION['id_player']);
        echo "ErrorInvalidIdBoard";
        die();
    }
    
    if(is_numeric($_POST['idTopic'])) // trim avant ?
    {
        $idTopic = htmlspecialchars(trim($_POST['idTopic']));
    }
    else
    {
        $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
        $log->addLog('url-user-submitted id_topic invalide', 'error', $_SESSION['id_player']);
        echo "ErrorInvalidIdTopic";
        die();
    }

    */
    
    // if(is_numeric($_POST['idMessage'])) // trim avant ?
    // {
    //     $idMessage = htmlspecialchars(trim($_POST['idMessage']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_message invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdMessage";
    //     die();
    // }

    // // Check the primary key in the ID-table
    // $idMessageDB = $_SESSION['message-list-id'][$idMessage];
    // if($idMessageDB == NULL) // '' is considered as NULL
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_message invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdMessage";
    //     die();
    // }

    $idMessage = htmlspecialchars(trim($_POST['idMessage']));
    $detailText = htmlspecialchars(trim($_POST['detailText']));

    #region

    $database->mysql->beginTransaction();

    //$forum->reportMessage($message, $idMessage, $idTopic, $idBoard, $_SESSION['id_city']);
    $forum->reportMessage($idMessage, $detailText);

    $database->mysql->commit();

    $_SESSION['validMsg'] = "Info : Le message a été signalé.";
}


// The user votes to a survey
if(isset($_POST['forumAction']) && $_POST['forumAction'] == 'voteToSurvey')
{
    #region input sanitation

    // $idChoice = "";
    // $idBoard = "";
    // $idTopic = "";
    // $idMessage = "";
    // $hasVoted = true;

    // if(isset($_POST['idChoiceSurvey']) && $_POST['idChoiceSurvey'] != "")
    // {
    //     $idChoice = htmlspecialchars(trim($_POST['idChoiceSurvey']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Merci de sélectionner une réponse.";
    //     echo "ErrorResponseChoiceNull";
    //     die();
    // }

    // if(is_numeric($_POST['idBoard'])) // trim avant ?
    // {
    //     $idBoard = htmlspecialchars(trim($_POST['idBoard']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_board invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdBoard";
    //     die();
    // }
    
    // if(is_numeric($_POST['idTopic'])) // trim avant ?
    // {
    //     $idTopic = htmlspecialchars(trim($_POST['idTopic']));
    // }
    // else
    // {
    //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de réessayer.";
    //     $log->addLog('url-user-submitted id_topic invalide', 'error', $_SESSION['id_player']);
    //     echo "ErrorInvalidIdTopic";
    //     die();
    // }

    #region Get the array of selected choices

    $arraySurveyIdChoices = json_decode($_POST['arraySurveyIdChoices']);
    if(sizeof($arraySurveyIdChoices) <= 0)
    {
        $_SESSION['errorMsg'] = "Erreur : Merci de sélectionner au moins un choix.";
        echo "err";
        die();
    }

    foreach ($arraySurveyIdChoices as $answer)
    {
        $answer = htmlspecialchars(trim($answer));
        if($answer === '')
        {
            $_SESSION['errorMsg'] = "Une erreur est survenue, merci de recharger la page.";
            echo "err";
            die();
        }
    }

    #endregion

    $database->mysql->beginTransaction();

    // Get the id Survey from the topic id
    $idTopic = htmlspecialchars(trim($_POST['idTopic']));
    $idSurvey = $forum->getIdSurveyFromIdTopic($idTopic);

    // Get the id of the selected choices

    $forum->voteToSurvey($idSurvey, $arraySurveyIdChoices);

    $database->mysql->commit();

    $_SESSION['validMsg'] = "Info : Votre vote a été pris en compte.";
}