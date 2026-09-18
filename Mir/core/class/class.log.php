<?php

/*
 * Class to add strings to a log file as log lines.
 * 
 * (Noter ici tout ce qui est loggé)
 * 
 */

class Log
{
    public function __contruct()
    {

    }

    // Compose the log message, then add it to the correct file. Also do the testing here.
    public function addLog($msg, $type, $id_user = null)
    {
        // Si pas de user id (par ex: user non connecté), utiliser l'ip (is null on localhost)
        if(!isset($id_user))
        {
            $id_user = $_SERVER["REMOTE_ADDR"] ?? '127.0.0.1';
        }

        $this->writeLog($type, $this->composeLog($msg, $id_user));
    }

    // Add the log line at the beginning of file
    public function writeLog($type, $log)
    {
        $file = '';

        // Select the correct log file to write into
        switch($type)
        {
            // For login successes or failures
            case 'login':
                $file = 'log_login.txt';
            break;
            // For thrown errors
            case 'error':
                $file = 'log_error.txt';
            break;
            // For database insertion, deletion or update
            case 'database':
                $file = 'log_database.txt';
            break;
            // For critical user actions (changing password etc)
            case 'action':
                $file = 'log_action.txt';
            break;
            // For actions that require an Admin level of priviledge
            case 'admin':
                $file = 'log_admin.txt';
            break;
            // For page actions and database actions that require a Premium level of priviledge.
            case 'premium':
                $file = 'log_premium.txt.';
            break;
            default:
                throw new Exception("Type de log incorrect.");
        }

        // Create cache if not existing
        $path = dirname(dirname(__FILE__))."/logs/".$file;
        if(!file_exists($path))
        {
            $logFile = fopen($path, "w") or die("Unable to open cache file!");
            fclose($logFile);
        }

        // Write log line to file
        try 
        {
            //$txt .= file_get_contents($file); // Do not add at the begginning. Too much work for big file.
            // using the FILE_APPEND flag to append the content to the end of the file
            // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
            file_put_contents($path, $log, FILE_APPEND | LOCK_EX);
        }
        catch(Exception $e)
        {
            throw new Exception("File handling error : ".$e->getMessage());
        }

        return true;
    }

    // Craft the log message. Variables like user ID are {enclosed} so they can be found easily using regexp.
    public function composeLog($msg, $id_user)
    {
        // Select the correct message from a list (add consistency)
        // Pas la peine ?? (cf avec l'expérience si bcp de messages différents ou pas (surtout pour les appels DB, il faut peut être logger les données). Si oui, l'envoyer en paramètre.)
        //switch($msg)
        //{
        //    // Login logs
        //    case 'errorLogin':
        //        $msg = 'Le login a échoué';
        //    break;
        //    case 'successLogin': //??
        //        $msg = 'Le login a fonctionné';
        //    break;
        //}

        $date = date("Y-m-d h:i:sa");
        $log = $date . "  User={" . $id_user . "} " . $msg . "\n";
        return $log;
    }

    public function getLog()
    {
        // Implement file searching login, w/ regexp on { }
    }

}