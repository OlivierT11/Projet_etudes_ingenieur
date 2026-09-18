<?php

class Tools
{
    public function __construct() {}

    public static function compareIdSessionArray($log, int $id, array $idArray)
    {
        // If the array does not exist, the user changed parameters in the URL
        if(!isset($idArray)) 
        {
            $_SESSION['errorMsg'] = "Une erreur a été rencontrée, merci de recommencer. Si le problème persiste, veuillez contacter un administrateur.";
            
            // (Logging)
            $err = 'IdNoMatch : Session inexistante.' ;
            $log->addLog($err, 'error', $_SESSION['id_player']);

            throw new Exception('IdNoMatch');
        }

        // If the idBoard is not numeric, the user also changed parameters in the URL
        if (!is_numeric($id))
        {
            $err = 'IdNoMatch : Id not numeric.' ;
            $log->addLog($err, 'error', $_SESSION['id_player']);

            throw new Exception('IdNoMatch');
        }

        // If the array does not contains the asked value, the user also changed parameters in the URL
        if($id > sizeof($idArray))
        {
            $err = 'IdNoMatch : Id not contained in Id array.' ;
            $log->addLog($err, 'error', $_SESSION['id_player']);

            throw new Exception('IdNoMatch');
        }

        // If the id board is negative, the user also changed parameters in the URL
        if($id < 0)
        {
            $err = 'IdNoMatch : Id < 0.' ;
            $log->addLog($err, 'error', $_SESSION['id_player']);

            throw new Exception('IdNoMatch');
        }
    }

    /**
     * Creates a session containing the array of sanitized ID to display in view.
     * This method should be called right after a database call returning the IDs
     * 
     * @param array $list The list to extract the DB ids from.
     * @param string $idName The name of the id key inside the array (ex : id_message)
     * @param string $sessionName The name to give to the IdSessionArray. It is the same as the array (ex : for $messagesList, provide MessageList)
     * 
     * @return The formatted session variable (ex : $_SESSION['session_Id_Array_MessageList'])
     */
    public static function createIdSessionArrayFromArray(array $list, string $idName, string $sessionName)
    {
        // WORKING, keep until new version checked
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

        if(isset($list[$idName]))
        {
            $i=0;
            $idSessionArray = [];

            // Create standardized session name
            $sessionName = "session_Id_Array_" . $sessionName;

            foreach ($list[$idName] as $tableIdKey)
            {
                $idSessionArray[$i] = $tableIdKey;
                $i++;
            }
            $_SESSION[$sessionName] = $idSessionArray;
        }
    }

    // Basic input validation. To use on everything that has no special chars in it (username but not password)
    // The password validation has its own function.
    public static function validateInput($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
}