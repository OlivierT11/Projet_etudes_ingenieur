if(document.getElementsByClassName("btn-mute")){

    var className = document.getElementsByClassName("btn-mute");
    var n = className.length;
  
    for (var i = 0; i < n; i++) {
        className[i].addEventListener("click", function(ev) {

            var playerToMute = ev.target.id;

            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_player.php",
                data: {action:'mute', playerToMute:playerToMute},
                success: function (response) {
                    console.log(response);

                    location.reload();
                    //switch the button to "unmute"
                    //ev.target.className = 'btn-unmute btn btn-dark btn-sm';
                    //ev.target.innerHTML = 'Unmute';
                },
                complete: function() {
                    me.data('requestRunning', false);
                },
                error: function(response){
                    console.log(response);
                }
            });

        });
    }
}

if(document.getElementsByClassName("btn-unmute")){

    var className = document.getElementsByClassName("btn-unmute");
    var n = className.length;
  
    for (var i = 0; i < n; i++) {
        className[i].addEventListener("click", function(ev) {

            var playerToUnmute = ev.target.id;

            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_player.php",
                data: {action:'unmute', playerToUnmute:playerToUnmute},
                success: function (response) {
                    console.log(response);

                    location.reload();
                    //switch the button to "mute"
                    //ev.target.className = 'btn-mute btn btn-light btn-sm';
                    //ev.target.innerHTML = 'Mute';
                },
                complete: function() {
                    me.data('requestRunning', false);
                },
                error: function(response){
                    console.log(response);
                }
            });

        });
    }
}

/*

TODO : do not reload the page on button toggle :
if(document.getElementsByClassName("btn-unmute")){

    var className = document.getElementsByClassName("btn-unmute");
    var n = className.length;
  
    for (var i = 0; i < n; i++) {
        className[i].addEventListener("click", unMutePlayer(ev));
    }
}

function unMutePlayer(ev) {

    var playerToUnmute = ev.target.id;

            var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_player.php",
        data: {action:'unmute', playerToUnmute:playerToUnmute},
        success: function (response) {
            console.log(response);

            //switch the button to "mute"
            ev.target.className = 'btn-mute btn btn-light btn-sm';
            ev.target.innerHTML = 'Mute';
        },
                complete: function() {
            me.data('requestRunning', false);
        },
        error: function(response){
            console.log(response);
        }
    });

}

*/