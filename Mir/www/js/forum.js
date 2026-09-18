/// UI ///

// Make sure the survey box is hidden if the box in unchecked at the start of the page (sometimes the box is already checked, like when we log-in on the page)
// (topic creation page only)
if(document.getElementById("topicHasSurvey"))
{
    if (document.getElementById("topicHasSurvey").checked)
    {
        document.getElementById("hidden-survey-wrapper").style.display = "block";
    }
    else
    {
        document.getElementById("hidden-survey-wrapper").style.display = "none";
    }
}


/////// EDITION ///////

// Edit a message
if(document.getElementsByClassName("message-edit-icon"))
{
    var className = document.getElementsByClassName("message-edit-icon");
    var n = className.length;
  
    for (var i = 0; i < n; i++)
    {
        className[i].addEventListener("click", function(ev)
        {
            // Find the text of the message to edit
            var messageContentToEdit = ev.target.previousElementSibling.children[1].innerText; //Or innerHTML if spaces needed.

            // Adds the text to edit to the new-message wrapper (remove the previous content if exists)
            document.getElementById('new-message-textarea').value = messageContentToEdit; // "Value" instead of innerhtml because textarea is of type "input"

            // Creates a hidden field w/ the message ID
            var idField = ev.target.parentElement.firstElementChild.getAttribute("value");
            
           // if(document.getElementById("id-message-to-edit"))
           // {
                document.getElementById("id-message-to-edit").setAttribute("value", idField);
          //  }
          //  else
           // {
                //var idFieldInput = document.createElement("input");
                //idFieldInput.setAttribute("type", "hidden");
                //idFieldInput.setAttribute("id", "id-message-to-edit");
                //idFieldInput.setAttribute("value", idField);

                //document.getElementById("new-topic-wrapper").appendChild(idFieldInput);
           // }
            


            // Changes the "Post" button to a "Edit" button
            document.getElementById("new-message-validate").style = "display:none";
            document.getElementById("edit-message-validate").style = "display:block";

            // Add a button to cancel the edition ONLY IF there is not already a button
            if(!document.getElementById("btn-cancel-edition"))
            {
                var divEdition = document.createElement("div");
                divEdition.setAttribute("id", "btn-cancel-edition");
                divEdition.innerHTML = "Annuler";

                // Add the event to cancel the edition to the cancel element
                divEdition.onclick = function() {cancelEdition()};

                document.getElementById("new-message-format").insertBefore(divEdition, document.getElementById("edit-message-validate"));
            }
            
            //TOTEST
            // Gives the focus to the textarea
            document.getElementById('new-message-textarea').focus();

            function cancelEdition()
            {
                // Remove the text inside the edit textarea
                document.getElementById('new-message-textarea').value = "";

                // Change the edit button to a validate button
                document.getElementById("edit-message-validate").style = "display:none";
                document.getElementById("new-message-validate").style = "display:block";

                // Remove the cancel button
                document.getElementById('btn-cancel-edition').remove();

                // Reset the hidden ID message
                document.getElementById("id-message-to-edit").setAttribute("value", "");
            }

        });
    }
}


// Confirm the edition

// Cancel the edition


// TODO, prompt "voulez vous valider la modification ?" + ajax call editMessage

////// SURVEY //////

// Toggle the survey section
if(document.getElementById("topicHasSurvey"))
{
    document.getElementById("topicHasSurvey").addEventListener("click", function(event)
    {
        // Display the survey section
        if(event.target.checked)
            document.getElementById("hidden-survey-wrapper").style.display = "block";
        else
            document.getElementById("hidden-survey-wrapper").style.display = "none";
    });
}



// Add a survey answer when clicking the green plus (max 10)
if(document.getElementById("topic-add-choice")){
    document.getElementById("topic-add-choice").addEventListener("click", function(event) {
  
        // Count the already existing choices
        var choiceNbr = document.getElementById('new-topic-survey-choices').childElementCount;

        if(choiceNbr < 10)
        {
            choiceNbr++;

            // Create the new choice element
            var newChoice = document.createElement('div'); //wrapper
            newChoice.setAttribute('data-id-choice', choiceNbr);
            newChoice.setAttribute('id', "wrapper-choice-" + choiceNbr);
            newChoice.setAttribute('class', 'topic-survey-choice');

            var newChoiceLabel = document.createElement('label'); //label
            newChoiceLabel.setAttribute('for', 'Choix' + choiceNbr);
            newChoiceLabel.innerHTML = 'Choix ' + choiceNbr + '&nbsp;';

            var newChoiceImg = document.createElement('div'); //img
            newChoiceImg.setAttribute('class', 'topic-del-choice');
            newChoiceImg.addEventListener('click', function(ev) {removeChoice(ev)});

            var newChoiceInput = document.createElement('input'); //input
            newChoiceInput.setAttribute('type', 'text');
            newChoiceInput.setAttribute('name', 'Choice' + choiceNbr);
            newChoiceInput.setAttribute('id', 'Choix' + choiceNbr);
            newChoiceInput.setAttribute('class', 'form-control');

            
            // Add the choice element at the end of the choices list
            newChoice.appendChild(newChoiceLabel);
            newChoice.appendChild(newChoiceImg);
            newChoice.appendChild(newChoiceInput);
            // newChoice.appendChild(document.createElement("br"));
            document.getElementById('new-topic-survey-choices').appendChild(newChoice);
        }

    });
}

// Remove a choice for the survey when clicking on the red cross
function removeChoice(ev)
{
    // Find the ID of the choice element to delete
    var IdChoiceToRemove = ev.target.parentElement.getAttribute('data-id-choice'); 

    document.getElementById("wrapper-choice-" + IdChoiceToRemove).remove();

    // Reorganize the IDs of the remaining choices
    var everyChoice = document.getElementsByClassName("topic-survey-choice");
    var n = everyChoice.length;

    for (var i = 0; i < n; i++)
    {
        everyChoice[i].setAttribute('data-id-choice', i);
        everyChoice[i].setAttribute('id', "wrapper-choice-" + i);

        j = i+1;
        everyChoice[i].children[0].setAttribute('for', "Choix" + i);
        everyChoice[i].children[0].innerHTML = "Choix " + j + '&nbsp;';

        everyChoice[i].children[2].setAttribute('name', "Choix" + i);
        everyChoice[i].children[2].setAttribute('id', "Choix" + i);
    }
}

// Add remove events on every topic-del-choice in page
if(document.getElementsByClassName("topic-del-choice"))
{
    var className = document.getElementsByClassName("topic-del-choice");
    var n = className.length;
  
    for (var i = 0; i < n; i++)
    {
        className[i].addEventListener("click", function(ev) {removeChoice(ev)});
    }
}


//// NEW MESSAGE ////

// Add <b></b>
if(document.getElementById("format-text-bold"))
{
    document.getElementById("format-text-bold").addEventListener("click", function(event)
    {
        // Topic creation page
        if(document.getElementById("new-topic-textarea"))
        {
            document.getElementById("new-topic-textarea").value += "<b></b>";
        }

        // New message
        else if(document.getElementById("new-message-textarea"))
        {
            document.getElementById("new-message-textarea").value += "<b></b>";
        }
        
    });
}

// Add <u></u>
if(document.getElementById("format-text-underline"))
{
    document.getElementById("format-text-underline").addEventListener("click", function(event)
    {
        // Topic creation page
        if(document.getElementById("new-topic-textarea"))
        {
            document.getElementById("new-topic-textarea").value += "<u></u>";
        }

        // New message
        else if(document.getElementById("new-message-textarea"))
        {
            document.getElementById("new-message-textarea").value += "<u></u>";
        }
        
    });
}

// Add quote
if(document.getElementById("format-text-quote"))
{
    document.getElementById("format-text-quote").addEventListener("click", function(event)
    {
        // Topic creation page
        if(document.getElementById("new-topic-textarea"))
        {
            document.getElementById("new-topic-textarea").value += "<blockquote></blockquote>";
        }

        // New message
        else if(document.getElementById("new-message-textarea"))
        {
            document.getElementById("new-message-textarea").value += "<blockquote></blockquote>";
        }

    });
}

// Add emotes
if(document.getElementById("emote-thumb")){
    document.getElementById("emote-thumb").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :ok ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :ok ";}
    });
}
if(document.getElementById("emote-heart")){
    document.getElementById("emote-heart").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :coeur ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :coeur ";}
    });
}
if(document.getElementById("emote-laugh")){
    document.getElementById("emote-laugh").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :rire ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :rire ";}
    });
}
if(document.getElementById("emote-smile")){
    document.getElementById("emote-smile").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :) ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :) ";}
    });
}
if(document.getElementById("emote-sad")){
    document.getElementById("emote-sad").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :( ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :( ";}
    });
}
if(document.getElementById("emote-angry")){
    document.getElementById("emote-angry").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :grr ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :grr ";}
    });
}
if(document.getElementById("emote-fear")){
    document.getElementById("emote-fear").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :peur ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :peur ";}
    });
}
if(document.getElementById("emote-surprised")){
    document.getElementById("emote-surprised").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :O ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :O ";}
    });
}
if(document.getElementById("emote-pls")){
    document.getElementById("emote-pls").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :pls ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :pls ";}
    });
}
if(document.getElementById("emote-vomit")){
    document.getElementById("emote-vomit").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :vomit ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :vomit ";}
    });
}
if(document.getElementById("emote-danger")){
    document.getElementById("emote-danger").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :danger ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :danger ";}
    });
}
if(document.getElementById("emote-wood")){
    document.getElementById("emote-wood").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :bois ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :bois ";}
    });
}
if(document.getElementById("emote-metal")){
    document.getElementById("emote-metal").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :metal ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :metal ";}
    });
}
if(document.getElementById("emote-orange")){
    document.getElementById("emote-orange").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :fruit ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :fruit ";}
    });
}
if(document.getElementById("emote-honey")){
    document.getElementById("emote-honey").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :miel ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :miel ";}
    });
}
if(document.getElementById("emote-cake")){
    document.getElementById("emote-cake").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :cake ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :cake ";}
    });
}
if(document.getElementById("emote-mask")){
    document.getElementById("emote-mask").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :mask ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :mask ";}
    });
}
if(document.getElementById("emote-helmet")){
    document.getElementById("emote-helmet").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :casque ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :casque ";}
    });
}
if(document.getElementById("emote-shield")){
    document.getElementById("emote-shield").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :ecu ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :ecu ";}
    });
}
if(document.getElementById("emote-upper-armor")){
    document.getElementById("emote-upper-armor").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :armh ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :armh ";}
    });
}
if(document.getElementById("emote-lower-armor")){
    document.getElementById("emote-lower-armor").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :arml ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :arml ";}
    });
}
if(document.getElementById("emote-spear")){
    document.getElementById("emote-spear").addEventListener("click", function(){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :lance ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :lance ";}
    });
}
if(document.getElementById("emote-foe")){
    document.getElementById("emote-foe").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :monstre ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :monstre ";}
    });
}
if(document.getElementById("emote-tree")){
    document.getElementById("emote-tree").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :arbre ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :arbre ";}
    });
}
if(document.getElementById("emote-hive")){
    document.getElementById("emote-hive").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :ruche ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :ruche ";}
    });
}
if(document.getElementById("emote-tree-fruit")){
    document.getElementById("emote-tree-fruit").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :arbrefruit ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :arbrefruit ";}
    });
}
if(document.getElementById("emote-dj")){
    document.getElementById("emote-dj").addEventListener("click", function(event){
        // Topic creation page
        if(document.getElementById("new-topic-textarea")){
            document.getElementById("new-topic-textarea").value += " :dj ";}
        // New message
        else if(document.getElementById("new-message-textarea")){
            document.getElementById("new-message-textarea").value += " :dj ";}
    });
}


/// REPORT ///
/*
1. si user clic sur un icon lié à une fenetre modale, remplacer l'id de la fenetre modal par l'id du message
2. dans l'ajax : prendre l'id modal comme id du message à report.
*/
if(document.getElementsByClassName("message-report-icon"))
{
    var className = document.getElementsByClassName("message-report-icon");
    var n = className.length;
  
    for (var i = 0; i < n; i++)
    {
        className[i].addEventListener("click", function(event) {

            // Get the id of the message to send
            var idMessage = event.target.parentElement.firstElementChild.getAttribute('value');
            
            // Add it to the modal
            var modal = document.getElementById("myModal");
            modal.setAttribute('data-id-message', idMessage);
            
        });
    }
}

