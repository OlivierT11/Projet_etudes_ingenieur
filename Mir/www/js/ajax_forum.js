// Events with AJAX calls on the forum page

// Pagination of the topic and message list
if(document.getElementsByClassName("page-link")){

    var className = document.getElementsByClassName("page-link");
    var n = className.length;
  
    for (var i = 0; i < n; i++) {
        className[i].addEventListener("click", function(ev) {

            ev.preventDefault(); // the <a> is not a link anymore

            // Get the current pager

            // Use the followinf two lines to get the encoded url params
            // var url = document.URL;
            // var params = new URLSearchParams(url);

            // Get unencoded url params
            var url = new URL(document.URL);
            var params = url.searchParams;
         //   var pager = params.get('pager');

            var pager = ev.target.getAttribute("data-id");

            if(pager === null) {pager = 1;}

            // Get the new pager
            var id = ev.target.getAttribute('id');
            switch (id)
            {
                // If no ID, take the clicked page number as the new pager
                case '':
                    break;
                case 'pager-first-page':
                    pager = 1;
                    break
                case 'pager-prev-page':
                    pager -= 1;
                    break;
                case 'pager-next-page':
                    pager += 1;
                    break;
                case 'pager-last-page':
                    pager="last_page";
                    break;
                default:
                    pager = 1;
            }

            // Si erreur pager
            if(pager < 1)
            {
                alert("Une erreur s'est produite, merci de réessayer.");
            }
            
            // Reload the page w/ the new pager
            params.set('pager', pager);
            params.toString();

            const newUrl = window.location.origin + window.location.pathname + '?' + params;

            // The difference between replace() method and assign(), is that replace() removes the URL of the current
            // document from the document history, meaning that it is not possible to use the "back" button to navigate back to the original document.
            // Use the assign() method if you want to load a new document, and the option to navigate back to the original document.
            window.location.assign(newUrl);

            // var page = ev.target.innerHTML;

            // // Get the ID board from current URL, if global forum
            // var url = document.URL;
            // var params = new URLSearchParams(url);
            // var isGlobal = params.get('isGlobal');

            // var idBoard = "";
            // if(isGlobal !== null)
            // {
            //     idBoard = params.get('board');
            // }

            // var me = $(this);
            // // Prevents multi clic
            // if ( me.data('requestRunning') ) {
            //     return;
            // }
            // me.data('requestRunning', true);
            
            // $.ajax({
            //     type: "POST",
            //     url: "ajax_forum.php",
            //     data: {
            //         forumAction:'GetTopicListPager', 
            //         idBoard:idBoard,
            //         page:page
            //     },
            //     success: function (response) {
            //         window.location.reload();
            //     },
            // complete: function() {
            //     me.data('requestRunning', false);
            // }
            // });

        });
    }
}

// Click validate topic creation
if(document.getElementById("btn-validate-survey-choice")){
    document.getElementById("btn-validate-survey-choice").addEventListener("click", function(event) {

        // Get the id of the curent topic
        var url = document.URL;
        var params = new URLSearchParams(url);
        var idTopic = params.get('topic'); 

        // Create an array of choices
        var ArrayAnswers = document.getElementsByClassName("input-survey-choice");
        
        var n = ArrayAnswers.length;

        if(n <= 0)
        {
            alert('Le sondage doit avoir au moins deux choix.');
            return;
        }

        var topicArraySurveyAnswers = [];

        // Only keep the selected choices
        for (var i = 0; i < n; i++)
        {
            if (ArrayAnswers[i].checked)
            {
                var idChoiceSelected = ArrayAnswers[i].getAttribute("value");
                
                topicArraySurveyAnswers.push(idChoiceSelected);
            }
        }

        // create an array of survey answers
        topicArraySurveyAnswers = JSON.stringify(topicArraySurveyAnswers);

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_forum.php",
            data: {
                forumAction:'voteToSurvey',
                idTopic:idTopic,
                arraySurveyIdChoices:topicArraySurveyAnswers
            },
            success: function () 
            {
                window.location.reload();
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });

    });
}

// Click previz topic 
if(document.getElementById("btn-new-topic-preview")){
    document.getElementById("btn-new-topic-preview").addEventListener("click", function(event) {

        // Just send all the form data to the preview page
        document.getElementById("new-topic-form").submit();
    });
}

// Click validate topic creation
if(document.getElementById("btn-new-topic-validate")){
    document.getElementById("btn-new-topic-validate").addEventListener("click", function(event) {

        // Get the ID board, if global forum
        var url = document.URL;
        var params = new URLSearchParams(url);
        var idBoard = params.get('board'); // null if no param 
        var idTopic = params.get('topic'); 
        var isGlobal = params.get('isGlobal'); 
        var topicHasSurvey = "0";
        var authorizeMultipleChoices = "0";
        
        var message = document.getElementById("new-topic-textarea").value;
        if(message === '')
        {
            alert('Merci d\'ajouter un message !');
            return;
        }

        var title = document.getElementById("inputTitle").value;
        if(title === '')
        {
            alert('Merci d\'écrire un titre !');
            return;
        }

        // Get survey data
        if(document.getElementById("topicHasSurvey").checked)
        {
            var topicHasSurvey = "1";
            var topicSurveyTitle = document.getElementById("surveyTitle").value;
            if(topicSurveyTitle === '')
            {
                alert('Merci d\'ajouter un titre au sondage !');
                return;
            }

            var authorizeMultipleChoices = document.getElementById("multipleChoicesEnabled").checked ? "1" : "0";
            var ArrayAnswers = document.getElementsByClassName("topic-survey-choice");
            
            var n = ArrayAnswers.length;

            if(n < 2)
            {
                alert('Le sondage doit avoir au moins deux choix.');
                return;
            }

            var topicArraySurveyAnswers = [];

            for (var i = 0; i < n; i++)
            {
                var nextChoiceContent = ArrayAnswers[i].lastElementChild.value;

                if(nextChoiceContent === '')
                {
                    alert('L\'un des choix est vide !');
                    return;
                }
                topicArraySurveyAnswers.push(nextChoiceContent);
            }
            // create an array of survey answers
            topicArraySurveyAnswers = JSON.stringify(topicArraySurveyAnswers);
        }

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_forum.php",
            data: {
                forumAction:'addTopic', 
                message:message,
                title:title,
                idBoard:idBoard,
                topicHasSurvey:topicHasSurvey,
                topicSurveyTitle:topicSurveyTitle,
                authorizeMultipleChoices:authorizeMultipleChoices,
                topicArraySurveyAnswers:topicArraySurveyAnswers
            },
            success: function (response) 
            {
                if(response === '')
                {
                    // Go to the newly created topic
                    if(isGlobal == "1")
                        window.location.replace("index.php?page=forum&isGlobal=1&board=" + idBoard);
                    else
                        window.location.replace("index.php?page=forum&isGlobal=0");
                }
                else
                {
                    window.location.reload();
                }
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });

    });
}


// Click validate new message
if(document.getElementById("new-message-validate")){
    document.getElementById("new-message-validate").addEventListener("click", function(event) {
  
        var url = document.URL;
        var params = new URLSearchParams(url);
        var idTopic = params.get('topic');
       
        var message = document.getElementById("new-message-textarea").value; 

        if(message.length == 0)
        {
            alert("Le message ne peut pas être vide.");
        }
        else
        {
            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_forum.php",
                data: {
                    forumAction:'addMessage', 
                    messageToAdd:message,
                    idTopic:idTopic
                },
                success: function (response)
                {
                    window.location.reload();
                },
                complete: function() {
                    me.data('requestRunning', false);
                }
            });
        }

    });
}


////// EDIT ///////
// Click validate new message
if(document.getElementById("edit-message-validate")){
    document.getElementById("edit-message-validate").addEventListener("click", function(event) {
  
        var message = document.getElementById("new-message-textarea").value; 
        var idMessage = document.getElementById("id-message-to-edit").value; 

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_forum.php",
            data: {
                forumAction:'editMessage', 
                messageToEdit:message,
                idMessage:idMessage
            },
            success: function (response)
            {
                window.location.reload();
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });
    
    });
}


////// REPORT //////
// Send the ID of the message to report
if(document.getElementById("btn-validate-report"))
{
    document.getElementById("btn-validate-report").addEventListener("click", function(event)
    {
        // Get the ID of the message to report. It is the ID on the modal.
        var idMessage = document.getElementById("myModal").getAttribute('data-id-message');
        var detailText = document.getElementById("report-message-textarea").value;

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_forum.php",
            data: {
                forumAction:'reportMessage',
                idMessage: idMessage,
                detailText: detailText
            },
            success: function (response) 
            {
                var elem = document.createElement("DIV");
                elem.setAttribute("class", "alert alert-success");
                elem.setAttribute("role", "alert");
                elem.setAttribute("style", "text-align:center");
                elem.innerHTML = "Le message a été signalé. Merci !"

                document.getElementById("modal-header").appendChild(elem);
            },
            complete: function() {
                me.data('requestRunning', false);
            },
            error: function(response)
             {
            //     if(response == "ErroraddReportedMessage")
            //     {
            //         window.location.reload(); // reload and display the error in session
            //     }
            }
        });
        

    });
}
