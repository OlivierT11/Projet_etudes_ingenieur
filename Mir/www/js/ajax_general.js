


/******************* */
// Quitter une ville */
/******************* */

// Leave city from the account page
if(document.getElementById("btn-leave-city")){
  document.getElementById("btn-leave-city").addEventListener("click", function() {

    if (confirm("Voulez-vous vraiment quitter la Citadelle ?")) {

      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

      $.ajax({
        type: "POST",
        url: "core/ajax/ajax_city.php",
        data: {action: 'leaveCity'}, 
        success: function (response)
        {
          if(response != '')
          {
            alert(response);
          }
          else
          {
            window.location.replace("index.php?page=home");
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      },
        error: function (response){
          alert(response);
        }
      }); 
    }

  });
}


/*********** */
// Chatboxes */
/*********** */

// Hide / Display chatboxes on click
if(document.getElementById("left-cb-title")){

  // Check the state of the chatboxes (hidden/displayed) on each page. Work for both chatboxes.
  $.ajax({
    type: "POST",
    url: "core/ajax/ajax_chatboxes.php",
    data: {action: 'check-cb'}, 
    success: function (response) {
      //response = '25px' or '280px'
      document.getElementById('chatboxes-wrapper').style.height=response;
    }
  });

  // Toggle the state in PHP session var on clic to keep in memory for the next pages.
  document.getElementById("left-cb-title").addEventListener("click", function() {

    if(document.getElementById('chatboxes-wrapper').style.height == '25px') {
      document.getElementById('chatboxes-wrapper').style.height='280px';
      var hidden = 0;
    }
    else {
      //happens first
      document.getElementById('chatboxes-wrapper').style.height='25px'; 
      var hidden = 1;
    }

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    //keep the new value in session for next pages
    $.ajax({
      type: "POST",
      url: "core/ajax/ajax_chatboxes.php",
      data: {action: 'hide-cb', hidden:hidden}, 
      success: function (response) {
        //console.log(response);
      },
      complete: function() {
        me.data('requestRunning', false);
      }
    });

  }); //end onclic

}
if(document.getElementById("right-cb-title")){
  document.getElementById("right-cb-title").addEventListener("click", function() {

    if(document.getElementById('chatboxes-wrapper').style.height == '25px') {
      document.getElementById('chatboxes-wrapper').style.height='280px';
      var hidden = 0;
    }
    else {
      //happens first
      document.getElementById('chatboxes-wrapper').style.height='25px'; 
      var hidden = 1;
    }

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    //keep the new value in session for next pages
    $.ajax({
      type: "POST",
      url: "core/ajax/ajax_chatboxes.php",
      data: {action: 'hide-cb', hidden:hidden}, 
      success: function (response) {
      },
      complete: function() {
        me.data('requestRunning', false);
      }
    }); 

  }); //end onclic

}