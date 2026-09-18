<?php

// Sort the stats array and keeps the indexes (usort transforms the indexes from atk_stat etc to 0,1,2...)
uasort($_SESSION['player_stat'], function($a, $b) {
    return $b['player_stat_lvl'] <=> $a['player_stat_lvl'];
});

// Get 1st and 2nd elements
$i = 1;
foreach($_SESSION['player_stat'] as $statName => $values)
{
    if($i == 1)
        $firstElemName = $statName;
    else if($i == 2)
        $secondElemName = $statName;
    else
        break;

    $i++;
}

// Check if the 1st stat is Master (lvl1 >= 2x lvl2)
$firstLvl = '';
$secondLvl = '';
$firstLvl = $_SESSION['player_stat'][$firstElemName]['player_stat_lvl'];
$secondLvl = $_SESSION['player_stat'][$secondElemName]['player_stat_lvl'];
$firstStatIsMaster = false;
if($firstLvl >= 2 * $secondLvl)
{
    $_SESSION['master_stat'] = $_SESSION['player_stat'][$firstElemName]['stat_name'];
    $_SESSION['is_master'] = 1;
    $firstStatIsMaster = true;
}
else
{
    $_SESSION['master_stat'] = '';
    $_SESSION['is_master'] = 0;
}

// Keep the lvl of the highest stat, even if not master
$bestStatLvL = $_SESSION['player_stat'][$firstElemName]['player_stat_lvl'];

// Also keep it in session, for ajax calls
$_SESSION['best_stat_lvl'] = (int)$_SESSION['player_stat'][$firstElemName]['player_stat_lvl'];

$getStatBoxListHTML = formatStatBox($firstStatIsMaster, $bestStatLvL);

/**
 * 
 * Format the stat box in HTML format
 * @param bool firstStatIsMaster If the first stat is master, displays the "Mastery" sprite
 * @param int $bestStatLvL The lvl of the player's highest stat, even if not master. Used to compute the maximum reachable lvl in every stat without losing the Mastery.
 * 
 */
function formatStatBox(bool $firstStatIsMaster, int $bestStatLvL)
{
    $statBoxHTML = '';
    $statBoxHTML .= '<p style="text-align:center;"><strong>Compétences</strong></p>';

    foreach ($_SESSION['player_stat'] as $stat => $params) 
    { 
        $xp = $params['player_stat_xp'];
        $name = $params['stat_name'];
        $lvl = (int)$params['player_stat_lvl'];

        //compute percentage difference between current lvl and max possible lvl for each stat https://www.omnicalculator.com/math/percentage-difference#how-to-find-the-percentage-difference 
        // Maximum = x2 !!! unwanted, but OK.
        $a = $_SESSION['max_possible_lvl'];
        $b = $lvl;
        $diff = abs($b - $a);
        $avg = ($a + $b)/2;
        $percent = $diff / $avg;

        $percent = round($percent * 100);

        

        // stores it in session to use in updatePlayerStat functions
        $_SESSION['xpGainPercent'][$stat] = $percent;

        if($firstStatIsMaster)
        {
            $statBoxHTML.='<li><p>';
            $statBoxHTML.= '<strong>'.L($name).' : '.$lvl.'</strong> (XP : '.$xp.'%) <span id="mastery-logo-stat-box" title="Maitrise. Ajoute un bonus de 20% à cette stat. Arrive quand son niveau est deux fois plus grand que la stat 2."></span>';
            $statBoxHTML.= '   <span title="Augmente votre vitesse de gain d\'xp. Plus le niveau de la stat est éloigné du niveau maximum, plus le bonus est grand. Cela vous permet de rattraper vos alliés si vous arrivez dans une ville en retard."><small>(+'.$percent.'%)</small></span>';
            $statBoxHTML.= '</p></li>';

            $firstStatIsMaster = false;
        }
        else
        {
            $statBoxHTML.='<li><p>';
            $statBoxHTML.= '<strong>'.L($name).' : '.$lvl.'</strong> (XP : '.$xp.'%) ';

            // Display mastery lvl distance indicator as %
            $statBoxHTML .= '<span title="Le niveau max que vous pouvez atteindre sans perdre la Maitrise de votre stat principale. C\'est la moitié de votre stat la plus élevée."><small>('.floor(($bestStatLvL/2)).')</small></span>';
                
            // Display maximum stat lvl distance indicator as %
            $statBoxHTML.= '<span title="Augmente votre vitesse de gain d\'xp. Plus le niveau de la stat est éloigné du niveau maximum atteignable (5 fois le jour en cours), plus le bonus est grand. Cela vous permet de rattraper vos alliés si vous arrivez dans une ville en retard."><small>(+'.$percent.'%)</small></span>';
            $statBoxHTML.= '</p></li>';
        }
    }
    return $statBoxHTML;
}
