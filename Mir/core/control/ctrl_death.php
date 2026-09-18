<?php


//The player is dead ! update the database

// Begins the transaction for the entire file.
$database->mysql->beginTransaction();

// Display the death reason
$deathReason = '';

// On ajoute un IF car parfois, session(death_reason) n'existe pas (je pense que c'est quand on meurt en étant hors ligne : quand on se connecte la session n'existe pas)
if(isset($_SESSION['death_reason']))
{
    switch ($_SESSION['death_reason'])
    {
        case "fightUpper":
            $deathReason = "Vous êtes mort(e), le haut du corp transpercé par la pointe gelée d'une Ombre.";
        break;
        case "fightLower":
            $deathReason = "Vous êtes mort(e), le bas du corps transpercé par la pointe gelée d'une Ombre.";
        break;
        case "fightHead":
            $deathReason = "Vous êtes mort(e), la tête transpercée par la pointe gelée d'une Ombre.";
        break;
        case "fightMask":
            $deathReason = "Vous êtes mort(e), le visage transpercé par la pointe gelée d'une Ombre.";
        break;
        case "food":
            $deathReason = "Vous êtes mort(e) de faim.";
        break;
        case "morale":
            $deathReason = "La démoralisation vous a poussé(e) à la folie. Vous avez erré sans fin, jusqu'à la mort.";
        break;
        case "wave":
            $deathReason = "Vous êtes mort(e), emporté(e) par une tempête gigantesque.";
        break;
        default:
            $deathReason = "Vous êtes mort(e)";
    }
    unset($_SESSION['death_reason']);
}
else
{
    $deathReason = "Vous êtes mort(e)";
}

// reinitilize table player : id_city, x, y, dj etc
reinitializePlayerLocation($database, $log);

// delete the player's items
reinitializePlayerItems($database, $log);

// update life bars
reinitializePlayerLifeBars($database, $log);

// update map allies on dying area
if($_SESSION['player_area'] != 'inside') // No ally counter inside.
    updateMapAllies($database, $log);

// update the sessions (after updateMapAllies)
$_SESSION['player_pos_x'] = 0;
$_SESSION['player_pos_y'] = 0;
$_SESSION['player_area'] = 'inside';

$database->mysql->commit();
