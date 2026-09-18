
//**************** */
// USE CONSUMABLES */
//**************** */

if(document.getElementsByClassName("btn-vote-bld")){

    var className = document.getElementsByClassName("btn-vote-bld");
    var n = className.length;
  
    for (var i = 0; i < n; i++) {
      className[i].addEventListener("click", function(ev) {
        var idBldVoted = ev.target.id; 

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

          $.ajax({
              type: "POST",
              url: "core/ajax/ajax_vote.php",
              data: {vote: "build",
                id: idBldVoted}, 
              success: function (response) {
                  if(response != ''){
                      alert(response);
                  } else { 
                    //adds a vote to the new element
                    //document.getElementById(idBldVoted).parentNode.nextSibling.innerHTML = (response + " votes &nbsp;");
                    //remove a vote to the previous element
                    location.reload();
                  }
              },
              complete: function() {
                me.data('requestRunning', false);
            }
          });
      });
    }
  }