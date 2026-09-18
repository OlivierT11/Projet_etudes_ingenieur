if(document.getElementById("build-camp")){
    document.getElementById("build-camp").addEventListener("click", function(event){
        
        if (confirm("Construire un camp ici coûte 30 bois")) { //renvoie TRUE si click sur ok, sinon false.

            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_camp.php",
                data: {
                  build: 'camp'
                }, 
                success: function (response) {
                    if(response != '')
                    {
                        alert(response); // "Il n'y a pas assez de bois"
                    }
                    else {
                        //window.location.replace("index.php?page=camp"); 
                        location.reload();
                    }
                },
                complete: function() {
                    me.data('requestRunning', false);
                }
            });

        } else {} 

    });
}
