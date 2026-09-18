<?php

/**
 * 
 * Class containing the forum logic
 * 
*/

// Carbon test for formatted dates
if (file_exists('vendor/Carbon/autoload.php'))
{
    require_once 'vendor/Carbon/autoload.php'; // path invalide quand appelé depuis ajax_forum.php
}
use Carbon\Carbon;

class Forum
{
    
    // Classes
    private $database;
    private $log;
    
    // Variables
    private $idCity;
    private $idPlayer;
    private $playerName;

    // Pagination
    private $nbrTopicsPerPage = 30;
    private $nbrMessagesPerPage = 10;

    public function __construct($database, $log)
    {
        $this->database = $database;
        $this->log = $log;

        $this->idCity = $_SESSION['id_city'];
        $this->idPlayer = $_SESSION['id_player'];
        $this->playerName = $_SESSION['player_name'];
    }

    #region Titres

    /**
     * Get current board name to display on the topic list
     */
    public function getForumCurrentBoardTitle($idBoard)
    {
        $err = '';
        $title = '';

        $query = 'SELECT board_name
                    FROM `forum_board`
                    WHERE id_board = :idBoard
                    LIMIT 1';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
           
            $sth->bindValue(':idBoard', $idBoard); 
            $res = $sth->execute();

            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $title = htmlspecialchars(trim($data['board_name']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        return $title;
    }

    /**
     * Get current topic name to display on the message list
     */
    public function getForumCurrentTopicTitle($idTopic)
    {
        $err = '';
        $title = '';

        $query = 'SELECT topic_title
                    FROM `forum_topic`
                    WHERE id_topic = :idTopic
                    LIMIT 1';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
           
            $sth->bindValue(':idTopic', $idTopic); 
            $res = $sth->execute();

            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $title = htmlspecialchars(trim($data['topic_title']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        return $title;
    }

    #endregion

    #region Page liste des catégories
    
    // Get the list of boards, then format into html code
    public function getForumBoardList() //$isGlobal = false
    {
        $err = '';
        $boardsList = [];
        //$isGlobal = $isGlobal ? 1 : 0;

        $query = 'SELECT id_board, board_name, board_descr, date_last_reply, author_last_reply, topic_nbr
                    FROM `forum_board`
                    ORDER BY board_order ASC'; //WHERE is_global = '.$isGlobal.'
        try 
        {
            $res = $this->database->mysql->query($query);
            if($res)
            {
                while($data=$res->fetch(PDO::FETCH_ASSOC))
                {
                    $boardsList['id_board'][] = htmlspecialchars(trim($data['id_board']));
                    $boardsList['board_name'][] = htmlspecialchars(trim($data['board_name']));
                    $boardsList['board_descr'][] = htmlspecialchars(trim($data['board_descr']));
                    $boardsList['date_last_reply'][] = htmlspecialchars(trim($data['date_last_reply']));
                    $boardsList['author_last_reply'][] = htmlspecialchars(trim($data['author_last_reply']));
                    $boardsList['topic_nbr'][] = htmlspecialchars(trim($data['topic_nbr']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Create a key/value array to link the html ID to the DB ID (for client sanitization)
        // $session_Id_Array_BoardList = [];
        // if(isset($boardsList['id_board']))
        // {
        //     $i=0;
        //     foreach ($boardsList['id_board'] as $tableIdKey)
        //     {
        //         $session_Id_Array_BoardList[$i] = $tableIdKey;
        //         $i++;
        //     }
        //     $_SESSION['session_Id_Array_BoardList'] = $session_Id_Array_BoardList;
        // }

        $boardsListHTML = $this->formatForumBoardList($boardsList);

        return $boardsListHTML;
            
    }

    public function formatForumBoardList($list)
    {
        // Initialize $n even if no result returned
        if(!isset($list['id_board']))
            $n=0;
        else
            $n = sizeof($list['id_board']);

        $listHTML = '';
        for ($i=0; $i<$n; $i++)
        {
            $id = $list['id_board'][$i];
            $link = 'index.php?page=forum&isGlobal=1&board=' . $id;
            $title = $list['board_name'][$i];
            $descr = $list['board_descr'][$i];
            $date = $this->parseDateToDisplay($list['date_last_reply'][$i]);
            $author = $list['author_last_reply'][$i];
            $nbr = $list['topic_nbr'][$i];
        
            $listHTML .= 
            '<div class="board-wrapper standard-board-color">
                <div class="board-icon-folder"></div>
                <div class="board-subject-wrapper">
                    <a class="board-title" href="'.$link.'" data-id="'.$id.'">'.$title.'</a><br/>
                    <span class="board-description">'.$descr.'</span>
                </div>
                <div class="board-answer-details">
                    <span class="board-date-last-answer">'.$date.'</span><br/>
                    par <span class="board-author-last-answer"><b>'.$author.'</b></span>
                </div>
                <div class="board-topic-number">
                    '.$nbr.' topics
                </div>
            </div>
            <div class="clear-float"></div>';
        
           // $listHTML .= '</p></li>';
        }

        return $listHTML;
    }

    public function countNewMessages($id_city)
    {
        // Amel
    }

    // Add the "Add a message" module.
    public function formatNewMessageModule()
    {
        $messageModuleHTML = '';

        $messageModuleHTML .=
        '<div id="new-message-wrapper">
            <div id="new-message-format">
                <div id="format-text">
                    <div id="format-text-bold" title="Gras"></div>
                    <div id="format-text-underline" title="Souligné"></div>
                    <div id="format-text-quote" title="Citation"></div>
                </div>
                <div id="format-emotes">
                    <div id="emote-thumb" class="forum-emote emote-thumb" title="J\'aime"></div>
                    <div id="emote-heart" class="forum-emote emote-heart" title="Coeur"></div>
                    <div id="emote-laugh" class="forum-emote emote-laugh" title="Rire"></div>
                    <div id="emote-smile" class="forum-emote emote-smile" title="Sourire"></div>
                    <div id="emote-sad" class="forum-emote emote-sad" title="Triste"></div>
                    <div id="emote-angry" class="forum-emote emote-angry" title="Colère"></div>
                    <div id="emote-fear" class="forum-emote emote-fear" title="Peur"></div>
                    <div id="emote-surprised" class="forum-emote emote-surprised" title="Choc"></div>
                    <div id="emote-pls" class="forum-emote emote-pls" title="PLS"></div>
                    <div id="emote-vomit" class="forum-emote emote-vomit" title="Vomir"></div>
                    <div id="emote-danger" class="forum-emote emote-danger" title="Danger"></div>

                    <div class="forum-emote"></div>

                    <div id="emote-wood" class="forum-emote icon-wood" title="Bois"></div>
                    <div id="emote-metal" class="forum-emote icon-metal" title="Métal"></div>
                    <div id="emote-orange" class="forum-emote icon-orange" title="Fruit"></div>
                    <div id="emote-honey" class="forum-emote icon-honey" title="Miel"></div>
                    <div id="emote-cake" class="forum-emote icon-cake" title="Gâteau"></div>
                    <div id="emote-mask" class="forum-emote icon-mask" title="Masque"></div>
                    <div id="emote-helmet" class="forum-emote icon-helmet" title="Casque"></div>
                    <div id="emote-shield" class="forum-emote icon-shield" title="Bouclier"></div>
                    <div id="emote-upper-armor" class="forum-emote icon-upper-armor" title="Armure haute"></div>
                    <div id="emote-lower-armor" class="forum-emote icon-lower-armor" title="Armure basse"></div>
                    <div id="emote-spear" class="forum-emote icon-spear" title="Lance"></div>

                    <div class="forum-emote"></div>

                    <div id="emote-foe" class="forum-emote icon-foe" title="Monstre"></div>
                    <div id="emote-tree" class="forum-emote icon-tree" title="Arbre"></div>
                    <div id="emote-hive" class="forum-emote icon-hive" title="Ruche"></div>
                    <div id="emote-tree-fruit" class="forum-emote icon-tree-fruit" title="Arbre fruitier"></div>
                    <div id="emote-dj" class="forum-emote icon-dj" title="Donjon"></div>
                </div>
                <div id="new-message-validate">
                    Valider
                </div>
                <div id="edit-message-validate" style="display:none;">
                    Editer
                </div>
            </div>
            <div class="clear-float"></div>
            <div class="new-message-text">
                <textarea id="new-message-textarea" maxlength="' . Constants::$forum_max_message_length . '"></textarea>
            </div>
            <input type="hidden" id="id-message-to-edit" value="">
        </div>

        <div class="clear-float"></div>';

        return $messageModuleHTML;
    }

    #endregion 

    #region Page liste des topics du forum GLOBAL

    public function getPagerLastPageOfTopics($idBoard)
    {
        $totalTopicNbr = 0;

        // Get the total number of messages in the topic
        $query = 'SELECT COUNT(id_topic) as topicNbr
                    FROM forum_topic
                    WHERE id_board = :idBoard';

        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':idBoard', $idBoard); 
            $res = $sth->execute();
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $totalTopicNbr = htmlspecialchars(trim($data['topicNbr']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Compute the pager accordingly
        return ceil($totalTopicNbr / $this->nbrTopicsPerPage); // Ex : 4/3 = 1.33 => on veut la page 2.
    }

    // Get the list of boards for the GLOBAL forum. Id_city is not used.
    public function getForumGlobalTopicList($idBoard, int $pager)
    {
        //$idBoardBDD = '';
        $err = '';
        $topicsList = [];
        $topicsListHTML = '';
        $offset = ($pager-1) * $this->nbrTopicsPerPage;
        $isNextPage = false;
        $rowCount = 0;

        #region Check the ID board

        // If the array does not exist, the user changed parameters in the URL
        // if(!isset($_SESSION['session_Id_Array_BoardList'])) 
        // {
        //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
        //     // (Logging)
        //     $err = 'BoardIdNoMatch : Session inexistante.' ;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('BoardIdNoMatch');
        // }

        // If the idBoard is not numeric, the user also changed parameters in the URL
        // if (!is_numeric($idBoard))
        // {
        //     $err = 'BoardIdNoMatch : IdBoard not numeric.' ;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('BoardIdNoMatch');
        // }

        // If the array does not contains the asked value, the user also changed parameters in the URL
        // if($idBoard > sizeof($_SESSION['session_Id_Array_BoardList']))
        // {
        //     $err = 'BoardIdNoMatch : IdBoard not contained in Id array.' ;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('BoardIdNoMatch');
        // }

        // If the id board is negative, the user also changed parameters in the URL
        // if($idBoard < 0)
        // {
        //     $err = 'BoardIdNoMatch : IdBoard < 0.' ;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('BoardIdNoMatch');
        // }

        // $idBoardBDD = $_SESSION['session_Id_Array_BoardList'][$idBoard];

        #endregion

        // Avoid pager problem
        if($pager <= 0) {$pager = 1;}

        #region Get topic list

        $query = 'SELECT id_topic, topic_title, topic_author, is_pinned, date_last_reply, author_last_reply, reply_nbr  
                    FROM forum_topic 
                    WHERE id_board = :idBoard
                    ORDER BY is_pinned DESC, date_last_reply DESC
                    LIMIT :offset , :nbrTopicsPerPage';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
           
            // Bind values 1 by 1, because the PDO::PARAM_INT is needed when using offsets with prepared statements, or else they are seen as strings.
            // $res = $sth->execute(array(':idCity' => $this->idCity, ':idBoard' => $idBoardBDD, ':nbrTopicsPerPage' => (int) $nbrTopicsPerPage, ':offset' => (int) $offset));
            // $sth->bindValue(':idCity', $this->idCity);
            //$sth->bindValue(':idBoard', $idBoardBDD); 
            $sth->bindValue(':idBoard', $idBoard); 
            $sth->bindValue(':nbrTopicsPerPage', (int) $this->nbrTopicsPerPage, PDO::PARAM_INT); 
            $sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT); 
            $res = $sth->execute();

            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $topicsList['id_topic'][] = htmlspecialchars(trim($data['id_topic']));
                    $topicsList['topic_title'][] = htmlspecialchars(trim($data['topic_title']));
                    $topicsList['topic_author'][] = htmlspecialchars(trim($data['topic_author']));
                    $topicsList['is_pinned'][] = htmlspecialchars(trim($data['is_pinned']));
                    $topicsList['date_last_reply'][] = htmlspecialchars(trim($data['date_last_reply']));
                    $topicsList['author_last_reply'][] = htmlspecialchars(trim($data['author_last_reply']));
                    $topicsList['reply_nbr'][] = htmlspecialchars(trim($data['reply_nbr']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Create a key/value array to link the html ID to the DB ID (for client sanitization)
        // $session_Id_Array_TopicList = [];
        // if(isset($topicsList['id_topic']))
        // {
        //     $i=0;
        //     foreach ($topicsList['id_topic'] as $tableIdKey)
        //     {
        //         $session_Id_Array_TopicList[$i] = $tableIdKey;
        //         $i++;
        //     }
        //     $_SESSION['session_Id_Array_TopicList'] = $session_Id_Array_TopicList;
        // }

        #endregion

        #region Pagination

        $query = 'SELECT id_topic 
                    FROM forum_topic 
                    WHERE id_board = :idBoard';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

            $sth->bindValue(':idBoard', $idBoard); 
            $res = $sth->execute();

            if($res)
            {
                $rowCount = $sth->rowCount();
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Defines whether there is a next page
        $isNextPage = $rowCount > ($this->nbrTopicsPerPage * ($offset + 1)) ? true : false;

        #endregion

        // Add pagination before the list
        $topicsListHTML .= $this->formatPagination($pager, $isNextPage);

        $topicsListHTML .= $this->formatForumTopicList($topicsList, $idBoard);

        // Add pagination after the list
        $topicsListHTML .= $this->formatPagination($pager, $isNextPage);

        return $topicsListHTML;
            
    }

    /**
     * Get the list of messages for the last page of the topic, then format the pager accordingly
     */
    public function getForumLastPageTopicList($idBoard)
    {
        $err = '';
        $topicsList = [];
        $topicsListHTML = '';
        $isNextPage = false;

        $pager = $this->getPagerLastPageOfTopics($idBoard);

        $offset = ($pager-1) * $this->nbrTopicsPerPage;

        #region Get topic list

        $query = 'SELECT id_topic, topic_title, topic_author, is_pinned, date_last_reply, author_last_reply, reply_nbr  
                    FROM forum_topic 
                    WHERE id_board = :idBoard
                    ORDER BY is_pinned DESC, date_last_reply DESC
                    LIMIT :offset , :nbrTopicsPerPage';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':idBoard', $idBoard); 
            $sth->bindValue(':nbrTopicsPerPage', (int) $this->nbrTopicsPerPage, PDO::PARAM_INT); 
            $sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT); 
            $res = $sth->execute();

            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $topicsList['id_topic'][] = htmlspecialchars(trim($data['id_topic']));
                    $topicsList['topic_title'][] = htmlspecialchars(trim($data['topic_title']));
                    $topicsList['topic_author'][] = htmlspecialchars(trim($data['topic_author']));
                    $topicsList['is_pinned'][] = htmlspecialchars(trim($data['is_pinned']));
                    $topicsList['date_last_reply'][] = htmlspecialchars(trim($data['date_last_reply']));
                    $topicsList['author_last_reply'][] = htmlspecialchars(trim($data['author_last_reply']));
                    $topicsList['reply_nbr'][] = htmlspecialchars(trim($data['reply_nbr']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        #endregion

        $isNextPage = false;

        // Add pagination before the list
        $topicsListHTML .= $this->formatPagination($pager, $isNextPage);

        $topicsListHTML .= $this->formatForumTopicList($topicsList, $idBoard);

        // Add pagination after the list
        $topicsListHTML .= $this->formatPagination($pager, $isNextPage);

        return $topicsListHTML;

    }

    #endregion

    #region Liste des topics du forum LOCAL

    // Get the list of topics from the id_city
    public function getForumLocalTopicList(int $pager)
    {
        $err = '';
        $topicsList = [];
        $topicsListHTML = '';
        $offset = ($pager-1) * $this->nbrTopicsPerPage;
        $isNextPage = false;
        $rowCount = 0;

        // Avoid pager problem
        if($pager <= 0) {$pager = 1;}

        #region Get topic list

        $query = 'SELECT id_topic, topic_title, topic_author, is_pinned, date_last_reply, author_last_reply, reply_nbr  
                    FROM forum_topic 
                    WHERE id_city = :idCity
                    ORDER BY is_pinned DESC, date_last_reply DESC
                    LIMIT :offset , :nbrTopicsPerPage';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
           
            // Bind values 1 by 1, because the PDO::PARAM_INT is needed when using offsets with prepared statements, or else they are seen as strings.
            // $res = $sth->execute(array(':idCity' => $this->idCity, ':idBoard' => $idBoardBDD, ':nbrTopicsPerPage' => (int) $nbrTopicsPerPage, ':offset' => (int) $offset));
            $sth->bindValue(':idCity', $this->idCity);
            $sth->bindValue(':nbrTopicsPerPage', (int) $this->nbrTopicsPerPage, PDO::PARAM_INT); 
            $sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT); 
            $res = $sth->execute();

            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $topicsList['id_topic'][] = htmlspecialchars(trim($data['id_topic']));
                    $topicsList['topic_title'][] = htmlspecialchars(trim($data['topic_title']));
                    $topicsList['topic_author'][] = htmlspecialchars(trim($data['topic_author']));
                    $topicsList['is_pinned'][] = htmlspecialchars(trim($data['is_pinned']));
                    $topicsList['date_last_reply'][] = htmlspecialchars(trim($data['date_last_reply']));
                    $topicsList['author_last_reply'][] = htmlspecialchars(trim($data['author_last_reply']));
                    $topicsList['reply_nbr'][] = htmlspecialchars(trim($data['reply_nbr']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Create a key/value array to link the html ID to the DB ID (for client sanitization)
        // $session_Id_Array_TopicList = [];
        // if(isset($topicsList['id_topic']))
        // {
        //     $i=0;
        //     foreach ($topicsList['id_topic'] as $tableIdKey)
        //     {
        //         $session_Id_Array_TopicList[$i] = $tableIdKey;
        //         $i++;
        //     }
        //     $_SESSION['session_Id_Array_TopicList'] = $session_Id_Array_TopicList;
        // }

        #endregion

        #region Pagination

        // Same query, but w/out the offset to get ALL affected results
        $query = 'SELECT id_topic
                    FROM forum_topic 
                    WHERE id_city = :idCity';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));

            $sth->bindValue(':idCity', $this->idCity);
            $res = $sth->execute();

            if($res)
            {
                $rowCount = $sth->rowCount();
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Defines whether there is a next page
        $isNextPage = $rowCount > ($this->nbrTopicsPerPage * ($offset + 1)) ? true : false;

        #endregion

        // Add pagination before the list
        $topicsListHTML .= $this->formatPagination($pager, $isNextPage);

        $topicsListHTML .= $this->formatForumTopicList($topicsList);

        // Add pagination after the list
        $topicsListHTML .= $this->formatPagination($pager, $isNextPage);

        return $topicsListHTML;
            
    }

    #endregion

    public function formatForumTopicList($list, $idBoard = null)
    {
        #region Variable initialization

        $isPinned = false;
        $pinClass = '';

        #endregion
        
        // Initialize $n even if no result returned
        if(!isset($list['topic_title']))
        $n=0;
        else
        $n = sizeof($list['topic_title']);
    
        
        #region Format Topic List
        $listHTML = '';


        $listHTML .= '<div class="topic-list">';
        
        for ($i=0; $i<$n; $i++)
        {
            $isPinned = $list['is_pinned'][$i] == '1' ? true : false;
            $pinClass = $isPinned == true ? "topic-pinned" : "topic-unpinned";
            $title = $list['topic_title'][$i];

            // Si forum global
            if($idBoard != null)
            {
                $link = 'index.php?page=forum&board='.$idBoard.'&topic='.$list['id_topic'][$i];
                $linkToLastMsg = 'index.php?page=forum&board='.$idBoard.'&topic='.$list['id_topic'][$i].'&pager=last_page';
            }
            // Si forum local
            else
            {
                $link = 'index.php?page=forum&topic='.$list['id_topic'][$i];
                $linkToLastMsg = 'index.php?page=forum&topic='.$list['id_topic'][$i].'&pager=last_page';
            }
            
            $author = $list['topic_author'][$i];
            $date = $this->parseDateToDisplay($list['date_last_reply'][$i]);
            $replyAuthor = $list['author_last_reply'][$i];
            $msgNbr = $list['reply_nbr'][$i];
        

            $listHTML .= '<div class="topic-wrapper '.$pinClass.'">';

            // Pin icon
            if($isPinned)
            {
                $listHTML .= '<div class="topic-icon-pin" title="Sujet épinglé"></div>';
            }
            else
            {
                $listHTML .= '<div class="topic-icon-message"></div>';
            }

            $listHTML .=  
            '<div class="topic-subject-wrapper">
                    <a href="'.$link.'" class="topic-title">'.$title.'</a> <br/>
                    Créé par <b>'.$author.'</b>
                </div>
                <div class=topic-answer-details>
                    '.$date.' (<a href='.$linkToLastMsg.'>dernier</a>)<br/>
                    par <b>'.$replyAuthor.'</b>
                </div>
                <div class="topic-answer-number">
                    '.$msgNbr.' message(s)
                </div>
            </div>
            <div class="clear-float"></div>'; 

        }

        $listHTML .= '</div>';

        #endregion
    
        return $listHTML;
    }

    /**
     * Affiche la pagination avec les pages disponibles si elles existent (rien ne s'affiche si page 1 uniquement)
     */
    public function formatPagination($currentPage, bool $isNextPage)
    {
        $paginationHTML = '';

        $paginationHTML .= 
        '<ul class="pagination">';

        // First page
        if($currentPage > 1)
        {
            $paginationHTML .= 
            '<li class="page-topic"><a class="page-link" id="pager-first-page" href="#">Début</a></li>';
        }

            // $paginationHTML .= 
            // '<li class="page-topic">
            //     <a class="page-link" href="#" id="pager-prev-page" aria-label="Previous">
            //         <span aria-hidden="true">&laquo;</span>
            //     </a>
            // </li>';

        // Previous page 5 if exists
        if($currentPage > 5)
        {  
            $paginationHTML .= '<li class="page-topic"><a class="page-link" id="" data-id="'.($currentPage - 5).'" href="#">'.($currentPage - 5).'</a></li>';
        }

        // Previous page 4 if exists
        if($currentPage > 4)
        {  
            $paginationHTML .= '<li class="page-topic"><a class="page-link" id="" data-id="'.($currentPage - 4).'" href="#">'.($currentPage - 4).'</a></li>';
        }

        // Previous page 3 if exists
        if($currentPage > 3)
        {  
            $paginationHTML .= '<li class="page-topic"><a class="page-link" id="" data-id="'.($currentPage - 3).'" href="#">'.($currentPage - 3).'</a></li>';
        }

        // Previous page 2 if exists
        if($currentPage > 2)
        {  
            $paginationHTML .= '<li class="page-topic"><a class="page-link" id="" data-id="'.($currentPage - 2).'" href="#">'.($currentPage - 2).'</a></li>';
        }

        // Previous page if exists
        if($currentPage > 1)
        {  
            $paginationHTML .= '<li class="page-topic"><a class="page-link" id="" data-id="'.($currentPage - 1).'" href="#">'.($currentPage - 1).'</a></li>';
        }

        // Current page if other pages exist
        if($currentPage > 1 || $isNextPage)
        {
            $paginationHTML .= '<li class="page-topic"><a class="page-link current-page" id="" data-id="'.$currentPage.'" href="#">'.$currentPage.'</a></li>';
        }
           
        // Next pages if exist
        if($isNextPage)
        {
            $paginationHTML .= 
            '<li class="page-topic"><a class="page-link" id="" data-id="'.($currentPage + 1).'" href="#">'.($currentPage + 1).'</a></li>';

            // Next page
            // $paginationHTML .= 
            // '<li class="page-topic">
            //     <a class="page-link" href="#" id="pager-next-page" aria-label="Next">
            //         <span aria-hidden="true">&raquo;</span>
            //     </a>
            // </li>';

            // Last page if at least 1 next page
            $paginationHTML .= 
            '<li class="page-topic"><a class="page-link" id="pager-last-page" href="#">Fin</a></li>';
        }

        $paginationHTML .= '</ul>';

        return $paginationHTML;

    }

    
    public function addNewTopic($title, $message, $idBoard, $date, $hasSurvey, $authorizeMultipleChoices, $surveyTitle, $arraySurveyAnswers)
    {
        $err = '';
        $date = "";
        $idSurvey = 0;


        // begin transaction

        $date = $this->createDateCarbonFormat(); // ??  (pas sur, peut être maivauis merge)

        #region Check the ID board
        // if(!isset($_SESSION['session_Id_Array_BoardList'])) 
        // {
        //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
        //     // (Logging)
        //     $err = 'BoardIdNoMatch : Session inexistante.' ;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('BoardIdNoMatch');
        // }

        // try
        // {
        //     $idBoardBDD = $_SESSION['session_Id_Array_BoardList'][$idBoard];
        // }
        // catch(Exception $e)
        // {
        //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
        //     // (Logging)
        //     $err = 'BoardIdNoMatch : ID inexistant dans le tableau des ID. ID donné : '.$idBoard;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('BoardIdNoMatch');
        // }
        #endregion

        // Adds a "(sondage)" text to the title 
        if ($hasSurvey == "1")
        {
            $title = '(Sondage) ' . $title;
        }

        // add a new topic and get the last insert id
        $query = 'INSERT INTO `forum_topic` (id_board, id_city, topic_title, topic_author, author_last_reply, date_last_reply, is_pinned, reply_nbr)  
                    VALUE(:idBoard, :idCity, :topicTitle, :topicAuthor, :authorLastReply, :dateLastReply, :isPinned, :replyNbr)';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':idBoard' => $idBoard, 
                                ':idCity' => $this->idCity, 
                                ':topicTitle' => $title, 
                                ':topicAuthor' => $this->playerName,
                                ':authorLastReply' => $this->playerName,
                                ':dateLastReply' => $date,
                                ':isPinned' => 0,
                                ':replyNbr' => 1
                            ));

            // Get the last inserted topic id
            $idTopic = $this->database->mysql->lastInsertId();
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // if there is a survey, add it

        if($hasSurvey)
            $idSurvey = $this->addNewSurvey($surveyTitle, $authorizeMultipleChoices, $arraySurveyAnswers);

        // add the 1st message
        $this->addNewMessage($idTopic, $message, $this->idPlayer, $idSurvey);


        // Add 1 topic to the topic count of the board (if global forum only)
        if ($idBoard != 0)
        {
            $query = 'UPDATE `forum_board` 
            SET topic_nbr = topic_nbr + 1
            WHERE id_board = :idBoard';
            try 
            {
                $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
                $sth->execute(array(':idBoard' => $idBoard));
            } 
            catch (PDOException $e)
            {
                $this->database->mysql->rollBack();

                // Display a friendly error message to the user.
                $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

                // (Logging)
                $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
                $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
            }
        }
 
    }

    #endregion

    #region Liste des messages

    public function getPagerLastPageOfMessages($idTopic)
    {
        $totalMessagesNbr = 0;

        // Get the total number of messages in the topic
        $query = 'SELECT COUNT(id_message) as msgNbr
                    FROM forum_message
                    WHERE id_topic = :idTopic';

        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':idTopic', $idTopic); 
            $res = $sth->execute();
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $totalMessagesNbr = $data['msgNbr'];
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Compute the pager accordingly
        return ceil($totalMessagesNbr / $this->nbrMessagesPerPage); // Ex : 4/3 = 1.33 => on veut la page 2.
    }

    /**
     * Get the list of messages for the last page of the topic, then format the pager accordingly
     */
    public function getForumLastPageMessageList($idTopic)
    {
        $err = '';
        $messagesList = [];
        $messagesListHTML = '';
        $isNextPage = false;

        $pager = $this->getPagerLastPageOfMessages($idTopic);

        $offset = ($pager-1) * $this->nbrMessagesPerPage;

        #region Get messages list

        $query = 'SELECT id_message, message_author, message_content, is_edited, message_date, id_survey, is_deleted  
                    FROM forum_message
                    WHERE id_topic = :idTopic
                    ORDER BY message_date ASC
                    LIMIT :offset , :nbrMessagesPerPage';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            //$res = $sth->execute(array(':idTopic' => $idTopicBDD));
            $sth->bindValue(':idTopic', $idTopic); 
            $sth->bindValue(':nbrMessagesPerPage', (int) $this->nbrMessagesPerPage, PDO::PARAM_INT); 
            $sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT); 
            $res = $sth->execute();
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $messagesList['id_message'][] = htmlspecialchars(trim($data['id_message']));
                    $messagesList['message_author'][] = htmlspecialchars(trim($data['message_author']));
                    $messagesList['message_content'][] = htmlspecialchars(trim($data['message_content']));
                    $messagesList['is_edited'][] = htmlspecialchars(trim($data['is_edited']));
                    $messagesList['message_date'][] = htmlspecialchars(trim($data['message_date']));
                    $messagesList['id_survey'][] = htmlspecialchars(trim($data['id_survey']));
                    $messagesList['is_deleted'][] = htmlspecialchars(trim($data['is_deleted']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        #endregion

        // We are already on the last page
        $isNextPage = false;

        // Add pagination before the list
        $messagesListHTML .= $this->formatPagination($pager, $isNextPage);

        // Format the message list to display
        $messagesListHTML .= $this->formatForumMessageList($messagesList);

        // Add pagination after the list
        $messagesListHTML .= $this->formatPagination($pager, $isNextPage);

        return $messagesListHTML;

    }

    /**
     * Get the list of messages for a given topic at a given page.
     * If the last page is asked, the numeric value of this page is computed then returned as a reference
     */
    public function getForumMessageList($idTopic, $pager)
    {
        $err = '';
        $messagesList = [];
        $messagesListHTML = '';
        $isNextPage = false;
        $rowCount = 0;
        $offset = ($pager-1) * $this->nbrMessagesPerPage;

        #region Check the ID topic
        // if(!isset($_SESSION['session_Id_Array_TopicList'])) 
        // {
        //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
        //     // (Logging)
        //     $err = 'TopicIdNoMatch : Session inexistante.' ;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('TopicIdNoMatch');
        // }

        // try
        // {
        //     $idTopicBDD = $_SESSION['session_Id_Array_TopicList'][$idTopic];
        // }
        // catch(Exception $e)
        // {
        //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
        //     // (Logging)
        //     $err = 'TopicIdNoMatch : ID inexistant dans le tableau des ID. ID donné : '.$idTopic;
        //     $this->log->addLog($err, 'error', $this->idPlayer);

        //     throw new Exception('TopicIdNoMatch');
        // }
        #endregion

        #region Get messages list

        $query = 'SELECT id_message, message_author, message_content, is_edited, message_date, id_survey, is_deleted  
                    FROM forum_message
                    WHERE id_topic = :idTopic
                    ORDER BY message_date ASC
                    LIMIT :offset , :nbrMessagesPerPage';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            //$res = $sth->execute(array(':idTopic' => $idTopicBDD));
            $sth->bindValue(':idTopic', $idTopic); 
            $sth->bindValue(':nbrMessagesPerPage', (int) $this->nbrMessagesPerPage, PDO::PARAM_INT); 
            $sth->bindValue(':offset', (int) $offset, PDO::PARAM_INT); 
            $res = $sth->execute();
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $messagesList['id_message'][] = htmlspecialchars(trim($data['id_message']));
                    $messagesList['message_author'][] = htmlspecialchars(trim($data['message_author']));
                    $messagesList['message_content'][] = htmlspecialchars(trim($data['message_content']));
                    $messagesList['is_edited'][] = htmlspecialchars(trim($data['is_edited']));
                    $messagesList['message_date'][] = htmlspecialchars(trim($data['message_date']));
                    $messagesList['id_survey'][] = htmlspecialchars(trim($data['id_survey']));
                    $messagesList['is_deleted'][] = htmlspecialchars(trim($data['is_deleted']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Create a key/value array to link the html ID to the DB ID (for client sanitization)
        // if(isset($messagesList['id_message']))
        // {
        //     $i=0;
        //     $session_Id_Array_MessageList = [];
        //     foreach ($messagesList['id_message'] as $tableIdKey)
        //     {
        //         $session_Id_Array_MessageList[$i] = $tableIdKey;
        //         $i++;
        //     }
        //     $_SESSION['session_Id_Array_MessageList'] = $session_Id_Array_MessageList;
        // }

        #endregion
        
        #region Pagination

        $query = 'SELECT id_message  
                    FROM forum_message
                    WHERE id_topic = :idTopic';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->bindValue(':idTopic', $idTopic); 
            $res = $sth->execute();
            if($res)
            {
                $rowCount = $sth->rowCount();
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        #endregion

        // Defines whether there is a next page
        $isNextPage = $rowCount > ($this->nbrMessagesPerPage * ($offset + 1)) ? true : false;

        // Add pagination before the list
        $messagesListHTML .= $this->formatPagination($pager, $isNextPage);

        // Format the message list to display
        $messagesListHTML .= $this->formatForumMessageList($messagesList);

        // Add pagination after the list
        $messagesListHTML .= $this->formatPagination($pager, $isNextPage);

        return $messagesListHTML;
    }

    public function formatForumMessageList($messagesList)
    {
        $messagesListHTML = '';
        
        if(isset($messagesList['id_message']))
            $n = sizeof($messagesList['id_message']);
        else
            $n = 0;

        if($n == 0)
        {
            $messagesListHTML .= "<p>Erreur : Il n'y a aucun message.</p>";
            return $messagesListHTML;
        }

        for($i=0;$i<$n;$i++)
        {
            #region Initialize variables
            $id = $messagesList['id_message'][$i];
            $author = $messagesList['message_author'][$i];
            $isEdited = $messagesList['is_edited'][$i] == "1" ? "(édité)" : "";
            $isDeleted = $messagesList['is_deleted'][$i];

            // Survey
            $idSurvey = $messagesList['id_survey'][$i];  
            $isSurvey = false;
            if($idSurvey != '0')
            {
                $isSurvey = true;
            }  

            $content = $messagesList['message_content'][$i];
            $date = $messagesList['message_date'][$i];
            #endregion

            // Parse the content text decoration (bold etc)
            $content = $this->parseMessageTextDecorationToDisplay($content);
            
            // Parse the content smileys
            $content = $this->parseMessageEmotesToDisplay($content);

            // Parse the date ("Il y a X secondes")
            $date = $this->parseDateToDisplay($date);

            // Format the message before the content
            $messagesListHTML .= 
            '<div class="message-wrapper">
                <input type="hidden" value="'.$id.'">
                <div class="message-author-wrapper">
                    <div class="message-author-name">
                        '.$author.'
                    </div>
                    <div class="message-author-avatar"></div>
                </div>
                <div class="message-content-wrapper">
                    <div class="message-content-date">
                        '.$date.' <span class="message-content-edited">'.$isEdited.'</span>
                    </div>';
            
            // Is the message a survey or a text, or is deleted
            if($isDeleted)
            {
                $messagesListHTML .= 
                '<div class="message-content-deleted">
                    <i>(Ce message a été supprimé)</i>
                </div>';
            } 
            else if(!$isDeleted && $isSurvey)
            {
                $surveyHTML = $this->getSurvey($idSurvey);
                $messagesListHTML .= $surveyHTML;
                // There is a survey
                // nouvelle fonction : getSurvey(id_survey) > BDD get survey data > format selon choix unique ou multiple
            }
            else if(!$isDeleted && !$isSurvey)
            {
                // There is only text
                $messagesListHTML .=
                '<div class="message-content-text">
                    '.$content.'
                </div>';
            }

            // Format the message after the content
            if($author == $this->playerName)
            {
                // Add edition button
                $messagesListHTML .=
                '</div>
                    <div class="message-edit-icon" title="Modifier le message"></div>
                </div>';
            }
            else
            {
                // Add report button
                $messagesListHTML .=
                '</div>
                    <div class="message-report-icon" title="Signaler le message" data-toggle="modal" data-target="#myModal"></div>
                </div>';
            }
        }

        // Add the clear-float div after each message
        $messagesListHTML .= '<div class="clear-float"></div>';

        return $messagesListHTML;
        
    }

    // Transform the smiley strings etc 
    // TODO mettre en static dans classe tools
    public function parseMessageEmotesToDisplay($string)
    {
        $my_smilies = array(
            ' :ok ' => '<img class="emote-thumb-display emote-display" />',
            ' :coeur ' => '<img class="emote-heart-display emote-display" />',
            ' :rire ' => '<img class="emote-laugh-display emote-display" />',
            ' :) ' => '<img class="emote-smile-display emote-display" />',
            ' :( ' => '<img class="emote-sad-display emote-display" />',
            ' :grr ' => '<img class="emote-angry-display emote-display" />',
            ' :peur ' => '<img class="emote-fear-display emote-display" />',
            ' :danger ' => '<img class="emote-danger-display emote-display" />',
            ' :O ' => '<img class="emote-surprised-display emote-display" />',
            ' :pls ' => '<img class="emote-pls-display emote-display" />',
            ' :vomit ' => '<img class="emote-vomit-display emote-display" />',
            ' :bois ' => '<img class="icon-wood-display emote-display" />',
            ' :metal ' => '<img class="icon-metal-display emote-display" />',
            ' :miel ' => '<img class="icon-honey-display emote-display" />',
            ' :fruit ' => '<img class="icon-orange-display emote-display" />',
            ' :cake ' => '<img class="icon-cake-display emote-display" />',
            ' :mask ' => '<img class="icon-mask-display emote-display" />',
            ' :casque ' => '<img class="icon-helmet-display emote-display" />',
            ' :armh ' => '<img class="icon-upper-armor-display emote-display" />',
            ' :arml ' => '<img class="icon-lower-armor-display emote-display" />',
            ' :lance ' => '<img class="icon-spear-display emote-display" />',
            ' :ecu ' => '<img class="icon-shield-display emote-display" />',
            ' :monstre ' => '<img class="icon-foe-display emote-display" />',
            ' :arbre ' => '<img class="icon-tree-display emote-display" />',
            ' :ruche ' => '<img class="icon-hive-display emote-display" />',
            ' :arbrefruit ' => '<img class="icon-tree-fruit-display emote-display" />',
            ' :dj ' => '<img class="icon-dj-display emote-display" />'
        );
        return str_replace( array_keys($my_smilies), array_values($my_smilies), $string);
    }

    public function parseMessageTextDecorationToDisplay($string)
    {
        return htmlspecialchars_decode($string);
    }

    public function parseMessageTextDecorationToStore($string)
    {
        return htmlspecialchars($string);
    }

    
    /**
     *  Transforms a date into "Il y a X secondes"
     *  @param string $date : the date to format. Must be given with the format '2021-01-05 00:50:32' or $date->format("Y-m-d H:i:s");
     */
    public function parseDateToDisplay($date)
    {
        // Get a new timestamp with the CARBON format "Y-m-d H:i:s"
        $now = DateTime::createFromFormat('U.u', microtime(true));
        $now = $now->format("Y-m-d H:i:s");

        // Compare the date stored in DB with the timestamp
        return Carbon::parse($date)->locale('fr')->diffForHumans($now);
    }

    // Creates a new timestamp at the CARBON format to be parsed by CARBON into "Il y a X secondes"
    public function createDateCarbonFormat()
    {
        // Convert milliseconds timestamp into the same date format
        // https://stackoverflow.com/questions/17909871/getting-date-format-m-d-y-his-u-from-milliseconds
        $date = DateTime::createFromFormat('U.u', microtime(true));
        return $date->format("Y-m-d H:i:s"); // gives '2021-01-05 00:50:32'
    }

    
    #endregion

    #region Survey

    public function getSurvey($idSurvey)
    {
        #region variable initialization
        $surveyArray = [];
        $surveyHTML = '';
        $surveyChoicesHTML = '';

        $surveyQuestion = '';
        $isUniqueChoice = false;
        $isUniqueChoiceText = '';

        $playerAlreadyVoted = false;

        $err = '';
        #endregion

        #region survey Question and IsUniqueChoice

        $query = 'SELECT survey_question, is_unique_choice  
                    FROM forum_survey
                    WHERE id_survey = :idSurvey
                    LIMIT 1';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $res = $sth->execute(array(':idSurvey' => $idSurvey));
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $surveyQuestion = htmlspecialchars(trim($data['survey_question']));
                    $isUniqueChoice = $data['is_unique_choice'] == '1' ? true : false;
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur dans la requête SQL de la fonction getSurvey : '.$e->getMessage();
            $this->log->addLog($err, 'error', $this->idPlayer);
        }

        // Format survey question

        $isUniqueChoiceText = $isUniqueChoice ? '(Un seul choix possible)' : '(Choix multiples possibles)';

        $surveyHTML .=
        '<div class="survey">
            <br/>
            <div class="survey-title">
                <h4 style="font-weight:bold">
                    '.$surveyQuestion.'
                </h4> 
                <span id="is-multiple-choices-authorized">
                    '.$isUniqueChoiceText.'
                </span>
            </div>
            <br/>
            <div class="survey-content">';


        #endregion

        #region Check if player has already voted the survey

        $err = '';
        $playerAlreadyVoted = false;

        $query = 'SELECT COUNT(id_survey_choice) as voteNbr
                    FROM forum_survey_player
                    WHERE id_survey = :idSurvey AND id_player = :idPlayer';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $res = $sth->execute(array(':idSurvey' => $idSurvey, 'idPlayer' => $this->idPlayer));
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $playerAlreadyVoted = $data['voteNbr'] > 0 ? true : false;
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

       // if(sizeof($choicesAlreadyVotedArray) > 0)
      //  {
       //     $playerAlreadyVoted = true;

            // AMEL : use the choicesAlreadyVotedArray to highlight the selected answers on the displayed survey.
            // > remove the LIMIT 1 in the query.
            // > send the array to the formatSurveyChoices method.
            // > loop through it and highlight corresponding IDs
       // }

        #endregion

        #region survey choices

        // Get the survey choices
        $surveyArray = $this->getSurveyChoices($idSurvey);

        // Format the survey choices
        $surveyChoicesHTML = $this->formatSurveyChoices($surveyArray, $isUniqueChoice, $playerAlreadyVoted);

        // Adds the choices HTML to the survey
        $surveyHTML .= $surveyChoicesHTML;

        #endregion

        #region Survey Validation button

        // Display the "Validate" btn only if the player hasn't voted yet
        if(!$playerAlreadyVoted)
        {
            $surveyHTML .= 
            '<div>
                <button class="btn btn-info btn-sm" id="btn-validate-survey-choice">Répondre</button>
            </div>';
        }

        #endregion

        #region End survey

        // Format the end of the survey DIVS
        $surveyHTML .= 
            '</div>
            </div>';

        #endregion

        return $surveyHTML;
    }

    // Get the list of available choices for a given survey
    public function getSurveyChoices($idSurvey)
    {
        $err = '';
        $SurveyChoicesList = [];

        $query = 'SELECT id_survey_choice, choice_content, vote_nbr  
                    FROM forum_survey_choice
                    WHERE id_survey = :idSurvey
                    LIMIT ' . Constants::$forum_max_nbr_of_choices_per_survey;
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $res = $sth->execute(array(':idSurvey' => $idSurvey));
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $SurveyChoicesList['id_choice'][] = htmlspecialchars(trim($data['id_survey_choice']));
                    $SurveyChoicesList['choice_content'][] = htmlspecialchars(trim($data['choice_content']));
                    $SurveyChoicesList['vote_nbr'][] = htmlspecialchars(trim($data['vote_nbr']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Create a key/value array to link the html ID to the DB ID (for client sanitization)
        // if(isset($messagesList['id_message']))
        // {
        //     $i=0;
        //     $session_Id_Array_MessageList = [];
        //     foreach ($messagesList['id_message'] as $tableIdKey)
        //     {
        //         $session_Id_Array_MessageList[$i] = $tableIdKey;
        //         $i++;
        //     }
        //     $_SESSION['session_Id_Array_MessageList'] = $session_Id_Array_MessageList;
        // }

        return $SurveyChoicesList;
    }

    // Format the list of available choices for a survey
    public function formatSurveyChoices($surveyArray, $isUniqueChoice, $playerAlreadyVoted)
    {
        $surveyChoicesHTML = '';

        if(isset($surveyArray['id_choice']))
        {
            $n = sizeof($surveyArray['id_choice']);
        } 
        else
        {
            $n = 0;
        }

        $surveyChoicesHTML .= '<div class="survey-choices">';

        // Add "Response" text if already voted
        if ($playerAlreadyVoted)
        {
            $surveyChoicesHTML .= '<span style="font-weight:bold">Résultats : </span>';
        }

        for($i=0;$i<$n;$i++)
        {
            #region Initialize variables
            $id = $surveyArray['id_choice'][$i];
            $content = $surveyArray['choice_content'][$i];
            $voteNbr = $surveyArray['vote_nbr'][$i];
            #endregion

            // If player already voted > display only choice text and vote nbr
            if($playerAlreadyVoted)
            {
                $surveyChoicesHTML .=
                '<div>
                    '.$content.' &nbsp; <span><b>('.$voteNbr.')</b></span>
                </div>';
            }

            // If player did not vote already > display buttons to vote and no vote nbr
            else
            {
                // Si choix unique > radiobutton
                if($isUniqueChoice)
                {
                    $surveyChoicesHTML .=
                    '<div>
                        <input type="radio" class="input-survey-choice" name="choice" value="'.$id.'">
                        <label for="survey-choice-'.$id.'">'.$content.'</label>
                    </div>';
                }

                // Si choix multiple > Checkbox
                else
                {
                    $surveyChoicesHTML .=
                    '<div>
                        <input type="checkbox" class="input-survey-choice" name="choice" value="'.$id.'">
                        <label for="survey-choice-'.$id.'">'.$content.'</label>
                    </div>';
                }
            }

        }

        $surveyChoicesHTML .= '</div>';

        return $surveyChoicesHTML;
    }

    #endregion

    #region Edition
    
    public function editMessage($message, $idMessage)
    {
        #region Id check
        //Tools::compareIdSessionArray($this->log, $idMessage, $_SESSION['session_Id_Array_MessageList']);
        //$idMessageEdit = $_SESSION['session_Id_Array_MessageList'][$idMessage];
        #endregion

        $query = 'UPDATE `forum_message`
                    SET message_content = :messageContent, 
                        is_edited =  1
                    WHERE id_message = :idMessage';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':messageContent' => $message,
                                ':idMessage' => $idMessage
                               ));
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }
    }

    #endregion

    /**
     *  Adds a new message to an existing topic ($idSurvey = 0) or the first message of a new topic ($idSurvey != 0)
     *  @param int $idTopic : the ID of the topic. If this is the 1st message of a new topic, this ID is the LastInsertID
     *  @param string $author : the username of the author of the message
     *  @param int $idSurvey : the ID of the survey contained by the message, if exists.
     */
    public function addNewMessage($idTopic, $message, $author = '', $idSurvey = 0)
    {
        //$this->database->mysql->beginTransaction();

        if($author == '')
        {
            $author = $this->playerName;
        }

        // Get the id of the last existing message
        // FOR UPDATE (lock) is needed, as we don't want someone else to add a new message in the meantime. 2 msg would have the same ID in topic.
        // $new_id_message_in_topic = 1;
        // $query = 'SELECT message_id_in_topic 
        //         FROM `message`
        //         WHERE id_topic = :idTopic AND id_board = :idBoard AND id_city = :idCity
        //         ORDER BY id_topic DESC LIMIT 1
        //         FOR UPDATE';
        // try 
        // {
        //     $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        //     $res = $sth->execute(array(':idCity' => $this->$idCity, 
        //                                ':idBoard' => $id_board,
        //                                ':idTopic' => $id_topic));
        //     if($res)
        //     {
        //         while($data=$res->fetch(PDO::FETCH_ASSOC))
        //         {
        //             $new_id_message_in_topic = $data['message_id_in_topic'] + 1;
        //         }
        //     }
        // } 
        // catch (PDOException $e)
        // {
        //     // Display a friendly error message to the user.
        //     $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
        //     // (Logging)
        //     $err = 'Erreur dans la requête SQL de la fonction addNewTopic : '.$e->getMessage();
        //     $this->log->addLog($err, 'error', $this->idPlayer);
        // }

        // Format the message to store in DB
        $message = $this->parseMessageTextDecorationToStore($message);

        // Create a new timestamp for the message (must be at CARBON format to allow date parsing to "Il y a X secondes...")
        $date = "";
        $date = $this->createDateCarbonFormat();

        // Adds the message
        $query = 'INSERT INTO `forum_message` (id_topic, message_author, id_author, message_content, message_date, id_survey)  
        VALUE (:idTopic, :messageAuthor, :idAuthor, :messageContent, :messageDate, :idSurvey)';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':idTopic' => $idTopic,  
                                ':messageAuthor' => $author, 
                                ':idAuthor' => $this->idPlayer,
                                ':messageContent' => $message, 
                                ':messageDate' => $date, 
                                ':idSurvey' => $idSurvey
                            ));
        } 
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();

            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Add 1 message to the message count of the topic
        $query = 'UPDATE `forum_topic` 
                  SET reply_nbr = reply_nbr + 1
                  WHERE id_topic = :idTopic';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':idTopic' => $idTopic));
        } 
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();

            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

    }

    #region Survey

    public function getIdSurveyFromIdTopic($idTopic)
    {
        $err = '';
        $idSurvey = 0;

        $query = 'SELECT id_survey FROM `forum_message`
                  WHERE  id_topic = :idTopic
                  LIMIT 1';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $res = $sth->execute(array(':idTopic' => $idTopic));
            if($res)
            {
                while($data=$sth->fetch(PDO::FETCH_ASSOC))
                {
                    $idSurvey = htmlspecialchars(trim($data['id_survey']));
                }
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        return $idSurvey;
    }

    // Adds a new survey and return the last insert ID survey
    public function addNewSurvey($surveyTitle, $authorizeMultipleChoices, $arrayAnswers)
    {
        //$this->database->mysql->beginTransaction();

        $newIdSurvey = 0;
        $err = '';

        #region Create a new survey

        $query = 'INSERT INTO `forum_survey` (survey_question, is_unique_choice)
                VALUE (:question, :is_unique_choice)';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $res = $sth->execute(array(':question' => $surveyTitle, ':is_unique_choice' => $authorizeMultipleChoices));
            if($res)
            {
                $newIdSurvey = $this->database->mysql->lastInsertId();
            }
            
        } 
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();

            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // rollback if $newIdSurvey == 0 ?

        #endregion

        #region Create new survey choices

        $query = 'INSERT INTO `forum_survey_choice` (id_survey, choice_content, vote_nbr)  
        VALUE (:idSurvey, :surveyChoice, :votesNbr)';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            foreach ($arrayAnswers as $text)
            {
                $sth->execute(array(':idSurvey' => $newIdSurvey, 
                                    ':surveyChoice' => $text, 
                                    ':votesNbr' => 0
                                ));
            }
        } 
        catch (PDOException $e)
        {
            $this->database->mysql->rollBack();

            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        #endregion

        //$this->database->mysql->commit();

        return $newIdSurvey;
    }

    /**
     * Add votes to a survey
     */
    public function voteToSurvey($idSurvey, $arrayIdChoices)
    {
        $err = '';

        // Add the votes for the selected choices
        $query = 'UPDATE `forum_survey_choice`
                  SET vote_nbr = vote_nbr + 1
                  WHERE id_survey_choice = :idSurveyChoice';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            foreach ($arrayIdChoices as $choice)
            {
                $sth->execute(array(':idSurveyChoice' => $choice));
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        // Add the votes of the player
        $query = 'INSERT INTO `forum_survey_player` (id_player, id_survey, id_survey_choice)
        VALUE (:idPlayer, :idSurvey, :idChoice)';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            foreach ($arrayIdChoices as $choice)
            {
                $sth->execute(array(':idPlayer' => $this->idPlayer, ':idSurvey' => $idSurvey, ':idChoice' => $choice));
            }
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }
    }

    #endregion

    #region Report message

    // When a player signals a message
    public function reportMessage($idMessage, $detailText)
    {
        #region Id check
        //Tools::compareIdSessionArray($this->log, $idMessage, $_SESSION['session_Id_Array_MessageList']);
        //$idMessageReported = $_SESSION['session_Id_Array_MessageList'][$idMessage];
        #endregion

        #region Insert the reported message into the report table

        $query = 'INSERT INTO `forum_report` (id_message, id_reporter, report_details)
        VALUE (:idMessage, :idReporter, :detailText)';
        try 
        {
            $sth = $this->database->mysql->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute(array(':idMessage' => $idMessage, ':idReporter' => $this->idPlayer, ':detailText' => $detailText));
        } 
        catch (PDOException $e)
        {
            // Display a friendly error message to the user.
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";

            // (Logging)
            $err = 'Erreur SQL fichier ' .$e->getFile(). ' ligne ' . $e->getLine() . ' : '.$e->getMessage();         
            $this->log->addLog($err, 'error', $_SERVER['REMOTE_ADDR']);
        }

        #endregion
    
    }

    #endregion
  
}