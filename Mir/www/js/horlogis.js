// Called in vi_head

// AMEL : startDate in session

function endGame(){
    ///Ajax modif DB dans ctrl_screen_end
    window.location.replace("index.php?page=defeat");
}

//Si on est sur la page vi_horlogis
if(document.getElementById("world-count")){

    var startDate=0;

    //get city start date with ajax
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_horlogis.php",
        data: {horlogis: "getStartDate"}, 
        success: function (response) {
            startDate = parseInt(response);

            //Il faut mettre l'horloge dans success car sinon, comme ajax est asynchrone, js n'attendra pas la réponse.
            var endDate = startDate + (7 * 24 * 60 * 60 * 1000); //7 jrs

            // Update the count down every 1 second
            var x = setInterval(function() {

                var currDate = new Date().getTime(); //ms, 13 digits, int
                var distance = endDate - currDate;
                
                //CODE UNIVERSEL POUR OBTENIR jours / heures / minutes / secondes à partir de date();
                var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                if (seconds < 10){
                    //résout le pb d'affichage sous 10 sec.
                    document.getElementById("world-count").innerHTML = "Temps restant " + days + " : " + hours + " : " + minutes + " : 0" + seconds;
                } else {
                    document.getElementById("world-count").innerHTML = "Temps restant " + days + " : " + hours + " : " + minutes + " : " + seconds;
                }

                // Fin
                if (distance < 0) {
                    clearInterval(x);
                    document.getElementById("world-count").innerHTML = "Fin du monde"; 
                    //endGame();
                }
            }, 1000);
        }
    });

}

