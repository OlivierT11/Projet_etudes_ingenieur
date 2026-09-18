<?php

if(!isset($_SESSION['id_player'])){
    $_SESSION['errorMsg'] = 'Déconnecté pour cause d\'inactivité';
    header("location: index.php?page=login");
    die();
}

#region File inclusions

$database = new Database();
$log = new Log();
$cache = new Cache($_SESSION['id_city']);

require dirname(dirname(__FILE__))."/class/class.forum.php";
$forum = new Forum($database, $log);

#endregion

$boardListHTML = "";
$topicListHTML = "";
$messageListHTML = "";
$title = '';

if (isset($_GET['isGlobal']))
{
    $isGlobal = htmlspecialchars(trim($_GET['isGlobal']));
}
if (isset($_GET['board']))
{
    $idBoard = htmlspecialchars(trim($_GET['board']));
}


#region Forum GLOBAL
if(isset($_GET['isGlobal']) && $_GET['isGlobal'] == "1")
{
    // Si arrive sur le forum GLOBAL, on liste les BOARDS
    if(!isset($_GET['board']) && !isset($_GET['topic']))
    {
        try
        {
            $boardListHTML = $forum->getForumBoardList();
        }
        catch(Exception $e)
        {
            // Reload the page to display the error message
            header("Location: index.php?page=forum&isGlobal=1");
            die();
        } 
    }

    // Si on est sur la page des topics d'un board
    else if(isset($_GET['board']) && $_GET['board'] != "" && !isset($_GET['topic'])) // revoir la logique
    {

        // on ne peut pas comparer des id BDD pour avoir la liste des topics si elle n'existe pas en session, cad si un user tape l'url directement sans être passé par la page des boards.
        // if(!isset($_SESSION['session_Id_Array_BoardList']))
        // {
        //     header("Location: index.php?page=forum&isGlobal=1");
        //     die();
        // }

        $pager = 1;

        // If no pager > get 1st page
        if(!isset($_GET['pager']))
        {
            try
            {
                $topicListHTML = $forum->getForumGlobalTopicList($idBoard, $pager);
                $title = $forum->getForumCurrentBoardTitle($idBoard);
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }
        }

        else if(isset($_GET['pager']) && $_GET['pager'] != '' && $_GET['pager'] != 'last_page')
        {
            // Make sure the pager is numeric or the cast will fail
            if(!is_numeric($_GET['pager']))
            {
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

            $pager = (int)htmlspecialchars(trim($_GET['pager']));
            
            try
            {
                $topicListHTML = $forum->getForumGlobalTopicList($idBoard, $pager);
                $title = $forum->getForumCurrentBoardTitle($idBoard);
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }
        }

        // If last page required
        else if (isset($_GET['pager']) && $_GET['pager'] == 'last_page')
        {
            $pager = htmlspecialchars(trim($_GET['pager']));

            try
            {
                $topicListHTML = $forum->getForumLastPageTopicList($idBoard);
                $title = $forum->getForumCurrentBoardTitle($idBoard);
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

        }

    }
    // Si on est sur la page des messages d'un topic, on les liste
    else if(isset($_GET['board']) && isset($_GET['topic']) && $_GET['topic'] != "")
    {
        // on ne peut pas comparer des id BDD pour avoir la liste des topics si elle n'existe pas en session, cad si un user tape l'url directement sans être passé par la page des topics.
        // if(!isset($_SESSION['session_Id_Array_BoardList']) || !isset($_SESSION['session_Id_Array_TopicList']))
        // {
        //     header("Location: index.php?page=forum&isGlobal=1");
        //     die();
        // }

        $idTopic = htmlspecialchars(trim($_GET['topic']));

        $pager = 1;

        // If no pager > get 1st page
        if(!isset($_GET['pager']))
        {
            try
            {
                $messageListHTML = $forum->getForumMessageList($idTopic, $pager);
                $title = $forum->getForumCurrentTopicTitle($idTopic);

                // Add the "Add message" module at the end of the list.
                $addMessageModuleHTML = $forum->formatNewMessageModule();
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }
        }

        else if(isset($_GET['pager']) && $_GET['pager'] != ''  && $_GET['pager'] != 'last_page')
        {
            // Make sure the pager is numeric or the cast will fail
            if(!is_numeric($_GET['pager']))
            {
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

            $pager = (int)htmlspecialchars(trim($_GET['pager']));

            try
            {
                $messageListHTML = $forum->getForumMessageList($idTopic, $pager);
                $title = $forum->getForumCurrentTopicTitle($idTopic);

                // Add the "Add message" module at the end of the list.
                $addMessageModuleHTML = $forum->formatNewMessageModule();
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

        }

        // If last page required
        else if (isset($_GET['pager']) && $_GET['pager'] == 'last_page')
        {
            $pager = htmlspecialchars(trim($_GET['pager']));

            try
            {
                $messageListHTML = $forum->getForumLastPageMessageList($idTopic);
                $title = $forum->getForumCurrentTopicTitle($idTopic);

                // Add the "Add message" module at the end of the list.
                $addMessageModuleHTML = $forum->formatNewMessageModule();
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

        }

    }

}

#endregion

#region Forum LOCAL

else if (!isset($_GET['isGlobal']) || (isset($_GET['isGlobal']) && $_GET['isGlobal'] == "0"))
{
    // Si arrive sur le forum local
    if(!isset($_GET['topic'])) // revoir la logique
    {
        /// TODO

        
        // on ne peut pas comparer des id BDD pour avoir la liste des topics si elle n'existe pas en session, cad si un user tape l'url directement sans être passé par la page des boards.
        // if(!isset($_SESSION['session_Id_Array_BoardList']))
        // {
        //     header("Location: index.php?page=forum");
        //     die();
        // }

        // $idBoard = $_GET['board'];

        $pager = 1;
        if(isset($_GET['pager']) && $_GET['pager'] != '')
        {
            // Make sure the pager is numeric or the cast will fail
            if(!is_numeric($_GET['pager']))
            {
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

            $pager = (int)htmlspecialchars(trim($_GET['pager']));
        }

        try
        {
            $topicListHTML = $forum->getForumLocalTopicList($pager);
            $title = "Forum local de la ville" . $_SESSION['city_name'];
        }
        catch(Exception $e)
        {
            // Reload the page to display the error message
            header("Location: ".$_SERVER['PHP_SELF']);
            die();
        }
    }
    // Si on est sur la page des messages d'un topic, on les liste
    else if(isset($_GET['topic']) && $_GET['topic'] != "")
    {
        // on ne peut pas comparer des id BDD pour avoir la liste des topics si elle n'existe pas en session, cad si un user tape l'url directement sans être passé par la page des topics.
        // if(!isset($_SESSION['session_Id_Array_TopicList']))
        // {
        //     header("Location: index.php?page=forum&isGlobal=0");
        //     die();
        // }

        $idTopic = htmlspecialchars(trim($_GET['topic']));

        $pager = 1;

        // if there is a page in URL different from last page
        if(isset($_GET['pager']) && $_GET['pager'] != ''  && $_GET['pager'] != 'last_page')
        {
            // Make sure the pager is numeric or the cast will fail
            if(!is_numeric($_GET['pager']))
            {
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

            $pager = (int)htmlspecialchars(trim($_GET['pager']));

            try
            {
                $messageListHTML = $forum->getForumMessageList($idTopic, $pager);
                $title = $forum->getForumCurrentTopicTitle($idTopic);

                // Add the "Add message" module at the end of the list.
                $addMessageModuleHTML = $forum->formatNewMessageModule();
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

        }

        // If last page required
        else if (isset($_GET['pager']) && $_GET['pager'] == 'last_page')
        {
            $pager = htmlspecialchars(trim($_GET['pager']));

            try
            {
                $messageListHTML = $forum->getForumLastPageMessageList($idTopic);
                $title = $forum->getForumCurrentTopicTitle($idTopic);

                // Add the "Add message" module at the end of the list.
                $addMessageModuleHTML = $forum->formatNewMessageModule();
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }

        }

        //no pager in URL (page 1)
        else
        {
            try
            {
                $messageListHTML = $forum->getForumMessageList($idTopic, $pager);
                $title = $forum->getForumCurrentTopicTitle($idTopic);

                // Add the "Add message" module at the end of the list.
                $addMessageModuleHTML = $forum->formatNewMessageModule();
            }
            catch(Exception $e)
            {
                // Reload the page to display the error message
                header("Location: ".$_SERVER['PHP_SELF']);
                die();
            }
        }
    }
}

#endregion

// else
// {
//     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recharger la page.";
//     header("Location: ".$_SERVER['PHP_SELF']);
//     die();
// }




