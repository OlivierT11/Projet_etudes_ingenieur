// *********************************************************
// **** Change the $_SESSION['area'] var on area change ****
// *********************************************************


//City


if(document.getElementById("btn-go-inside-city")) {
    document.getElementById("btn-go-inside-city").addEventListener("click", function(event) {
        var area = "inside";
        
        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);
        
        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_change_area.php",
            data: {area: area}, 
            success: function (response) {
                if(response != ''){ //if error
                    alert(response);
                }
                window.location.replace("index.php?page=inside"); 
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });
    });
}

//go outside from city
if(document.getElementById("btn-go-outside")) {
    document.getElementById("btn-go-outside").addEventListener("click", function(event) {
        var area = "outside";

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_change_area.php",
            data: {area: area}, 
            success: function (response) {
                if(response != ''){ //if error
                    alert(response);
                }
                window.location.replace("index.php?page=outside"); 
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });
    });
}



//DJ


if(document.getElementById("btn-go-inside-dj")) {
    document.getElementById("btn-go-inside-dj").addEventListener("click", function(event) {
        var area = "dj";

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_change_area.php",
            data: {area: area}, 
            success: function (response) {
                if(response != ''){ //if error
                    alert(response);
                }
                window.location.replace("index.php?page=dj"); 
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });
    });
}
//go outside from dj
if(document.getElementById("btn-go-outside-dj")) {
    document.getElementById("btn-go-outside-dj").addEventListener("click", function(event) {
        var area = "outside-from-dj";

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_change_area.php",
            data: {area: area}, 
            success: function (response) {
                if(response != ''){
                    alert(response);
                }
                window.location.replace("index.php?page=outside"); 
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });
    });
}




//Abyss

if(document.getElementById("btn-go-inside-abyss")) {
    document.getElementById("btn-go-inside-abyss").addEventListener("click", function(event) {
    var area = "abyss-from-city";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            if(response != ''){ //if error
                alert(response);
            }
            window.location.replace("index.php?page=abyss"); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}

//go from abyss to city
if(document.getElementById("btn-go-outside-abyss")) {
    document.getElementById("btn-go-outside-abyss").addEventListener("click", function(event) {
    var area = "inside";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            if(response != ''){
                alert(response);
            }
            window.location.replace("index.php?page=inside"); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}

//hell

//go from abyss to hell
if(document.getElementById("btn-go-inside-hell")) {
    document.getElementById("btn-go-inside-hell").addEventListener("click", function(event) {
    var area = "hell";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            if(response != ''){
                alert(response);
            }
            window.location.replace("index.php?page=hell"); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}

if(document.getElementById("btn-go-outside-hell")) {
    document.getElementById("btn-go-outside-hell").addEventListener("click", function(event) {
    var area = "abyss-from-hell";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            if(response != ''){
                alert(response);
            }
            window.location.replace("index.php?page=abyss"); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}


//camp
if(document.getElementById("enter-camp")){
    document.getElementById("enter-camp").addEventListener("click", function(event){

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        //update id_camp in table player
        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_change_area.php",
            data: {area: 'enter-camp'}, 
            success: function () {
                window.location.replace("index.php?page=camp");
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });

    });
}
if(document.getElementById("btn-go-outside-camp")) {
    document.getElementById("btn-go-outside-camp").addEventListener("click", function(event) {
    var area = "leave-camp";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            area = response;
            window.location.replace("index.php?page="+response); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}

//dj2 entry
if(document.getElementById("btn-go-inside-dj2")) {
    document.getElementById("btn-go-inside-dj2").addEventListener("click", function(event) {
    var area = "dj2";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            area = response;
            window.location.replace("index.php?page=dj2"); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}
//dj2 exit to dj
if(document.getElementById("btn-go-outside-dj2")) {
    document.getElementById("btn-go-outside-dj2").addEventListener("click", function(event) {
    var area = "dj-from-dj2";

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);
    
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_change_area.php",
        data: {area: area}, 
        success: function (response) {
            area = response;
            window.location.replace("index.php?page=dj"); 
        },
        complete: function() {
            me.data('requestRunning', false);
        }
    });
    });
}


//advice screen from home page
if(document.getElementById("go-to-advice-page")) {
    document.getElementById("go-to-advice-page").addEventListener("click", function() {
        window.location.replace("index.php?page=advice"); 
    });
}
// "return" btn after home page
if(document.getElementById("go-to-home-page")) {
    document.getElementById("go-to-home-page").addEventListener("click", function() {
        window.location.replace("index.php?page=home"); 
    });
}


// home : btn to go to forum
if(document.getElementById("enter-forum")) {
    document.getElementById("enter-forum").addEventListener("click", function() {
        window.location.replace("forum/"); 
    });
}

// horloge : btn to go to city center
if(document.getElementById("link-to-city-center")) {
    document.getElementById("link-to-city-center").addEventListener("click", function() {
        window.location.replace("index.php?page=inside"); 
    });
}