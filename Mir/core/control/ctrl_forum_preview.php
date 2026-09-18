<?php

// Page title
if (isset($_POST['topicHasSurveyCheckbox']) && $_POST['topicHasSurveyCheckbox'] != '')
{
    $title = '(Sondage) ' . htmlspecialchars(trim($_POST['topicTitle']));
}
else
{
    $title = htmlspecialchars(trim($_POST['topicTitle']));
}

$message = htmlspecialchars($_POST['newTopicContent']);

// Parse text decoration and smileys
$message = $forum->parseMessageTextDecorationToDisplay($message);
$message = $forum->parseMessageEmotesToDisplay($message);

#region Mise en forme du survey

// Choix unique ou multiple
$isUniqueChoice = true;
$isUniqueChoiceText = '(Un seul choix possible)';
if (isset($_POST['multipleChoicesEnabledCheckbox']) && $_POST['multipleChoicesEnabledCheckbox'] != '')
{
    $isUniqueChoiceText = '(Choix multiples possibles)';
    $isUniqueChoice = false;
}

// Survey
$surveyHTML = '';

// The value of an input type checkbox will only be send via POST if checked
if (isset($_POST['topicHasSurveyCheckbox']) && $_POST['topicHasSurveyCheckbox'] != '')
{
    $surveyQuestion = htmlspecialchars(trim($_POST['surveyTitle']));

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


    // Survey choices
    $surveyChoicesHTML = '<div class="survey-choices">';

    // Tester l'existence de tous les choix possibles
    $n = 0;
    if (isset($_POST['Choix1']) && $_POST['Choix1'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix2']) && $_POST['Choix2'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix3']) && $_POST['Choix3'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix4']) && $_POST['Choix4'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix5']) && $_POST['Choix5'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix6']) && $_POST['Choix6'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix7']) && $_POST['Choix7'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix8']) && $_POST['Choix8'] != '')
    {
        $n++;
    }
    if (isset($_POST['Choix9']) && $_POST['Choix9'] != '')
    {
        $n++;
    }

    for ($i = 1; $i <= $n; $i++)
    {
        $content = htmlspecialchars(trim($_POST['Choix' . $i]));

        // Si choix unique > radiobutton
        if($isUniqueChoice)
        {
            $surveyChoicesHTML .=
            '<div>
                <input type="radio" class="input-survey-choice" name="survey-choice-'.$i.'">
                <label for="survey-choice-'.$i.'">'.$content.'</label>
            </div>';
        }
        // Si choix multiple > Checkbox
        else
        {
            $surveyChoicesHTML .=
            '<div>
                <input type="checkbox" class="input-survey-choice" name="survey-choice-'.$i.'">
                <label for="survey-choice-'.$i.'">'.$content.'</label>
            </div>';
        }
    }

    $surveyChoicesHTML .= '</div>';

    // Adds the choices HTML to the survey
    $surveyHTML .= $surveyChoicesHTML;

    // Format the end of the survey DIVS
    $surveyHTML .= 
    '</div>
    </div>';
}
#endregion

$messageHTML = '';

// Message w/ survey
$messageHTML .= 
'<div class="message-wrapper">
    <div class="message-author-wrapper">
        <div class="message-author-name">
            '.$_SESSION['player_name'].'
        </div>
        <div class="message-author-avatar"></div>
    </div>
    <div class="message-content-wrapper">
        <div class="message-content-date">
            Il y a 1 seconde
        </div>';

$messageHTML .= $surveyHTML;
        
$messageHTML .= '<div class="clear-float"></div>';

$messageHTML .= '</div></div>';

// Message w/out survey
$messageHTML .= 
'<div class="message-wrapper">
    <div class="message-author-wrapper">
        <div class="message-author-name">
            '.$_SESSION['player_name'].'
        </div>
        <div class="message-author-avatar"></div>
    </div>
    <div class="message-content-wrapper">
        <div class="message-content-date">
            Il y a 1 seconde
        </div>
        <div class="message-content-text">
            '.$message.'
        </div>
    </div>
</div>
<div class="clear-float"></div>';