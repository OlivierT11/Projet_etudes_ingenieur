if(document.getElementById("btn-water-all")){
    document.getElementById("btn-water-all").addEventListener("click", function(event) {
  
      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

        $.ajax({
          type: "POST",
          url: "core/ajax/ajax_farm.php",
          data: {farm: 'water_all'},
          success: function (response) {
            if(response != ''){
              alert(response);
            }
            alert("Tout a été arrosé.");
            location.reload();
          },
          complete: function() {
            me.data('requestRunning', false);
        }
       }); 
    });
  }

  