<?php

/**
 * 
 * Class containing the votes and laws logic
 * 
*/

// Carbon for formatted dates
//include(dirname(dirname(__FILE__))."/lib/Carbon/autoload.php");
//require_once 'vendor/Carbon/autoload.php';
//use Carbon\Carbon;

class Law
{
    #region Variables
    private $database;
    private $log;
    private $idCity = 0;
    private $idPlayer = 0;

    public $idLaw = 0;
    public $idLawCity = 0;

    /**
     * Id de la loi dans la ville
     */
    public $idCityLaw = 0;
    public $idLawToDisplay = 0;

    public $lawName = "";
    public $currentValue = "";
    public $lawDescr = "";

    /**
     * Indique si la loi possède une loi parente
     */
    public $isChildLaw = false;
    public $idParentLaw = 0;

    /**
     * Indique si la loi est actuellement l'objet d'un vote
     */
    public $isProposed = false;
    public $ProposedValue = "";
    public $playerPropose = "";

    /**
     * Indique si la loi est de type Tout-ou-rien (Oui / Non)
     */
    public $isTOR = false;

    /**
     * Indique si la loi est d'importance capitale dans le scénario du jeu
     */
    public $isMajor = false;
    #endregion


    public function __construct($database, $log)
    {
        // Objects
        $this->database = $database;
        $this->log = $log;

        // Variables
        $this->idCity = $_SESSION['id_city'];
        $this->idPlayer = $_SESSION['id_player'];
    }

    #region Display

    // Get the data of the laws to display
    public function getLawsList()
    {
        // -> dans mod_law.php car besoin de créer une liste d'objets LAW
    }

    // Format the laws list to display in HTML
    public function formatLawsList($lawsList) : string
    {
        $lawsListHTML = '';

        // Format the IN VOTE PROCESS laws

        // Format the IN ACTIVITY laws

        return $lawsListHTML;
    }

    #endregion

    #region Upload a new flag proposition

    // Check if the player already uploaded a flag 
    public function checkIfPlayerAlreadyUploadedFlag() : bool
    {

    }
    
    //Sanitize and prepare the uploaded image
    public function prepareUploadedFlag($uploadedFile) : array
    {
        /// Infos
        // You get the following information for each file:
        // $_FILES['field_name']['name']
        // $_FILES['field_name']['size']
        // $_FILES['field_name']['type']
        // $_FILES['field_name']['tmp_name']

        
    }

    #region Vote

    /**
     * Checks if the player already voted to this law, if yes, update the vote, if not, add the vote.
     */
    public function AddVoteToLaw()
    {
        // Check if the player already voted this particular law
        $err = '';
        $i = 0;

        $query="SELECT id_player
                FROM law_player_vote lpv
                ...
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
    }

    #endregion

    #region Propose law

    /**
     * Check if the law with the proposed value is already proposed. If not, propose the law to the vote process
     */
    public function ProposeLawToVote()
    {
        
    }

    #endregion

    // Adds the new flag to the filesystem and the database
    public function postNewFlagProposition()
    {
        // Add the flag to the filesystem

        // Update the database path to the file

        // Update the player's vote
    }

    #endregion

    #region Vote for a flag

    // Make the player vote for a flag, or change its vote
    public function playerVoteForAFlag()
    {
        // Check if the player already voted

        // Adds a new vote

        // Or update existing vote
    }

    #endregion

}