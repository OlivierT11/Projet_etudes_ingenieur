/*


document.getElementsByTagName("BODY")[0].style.backgroundColor = '#fcdbba';

// Menu wrappers
if(document.getElementById('major-news-wrapper'))
    document.getElementById('major-news-wrapper').style.backgroundColor = '#f4e8dc';
if(document.getElementById('minor-news-wrapper'))
    document.getElementById('minor-news-wrapper').style.backgroundColor = '#f4e8dc';
if(document.getElementById('observation-wrapper'))
    document.getElementById('observation-wrapper').style.backgroundColor = '#f4e8dc';
if(document.getElementById('weather-wrapper'))
    document.getElementById('weather-wrapper').style.backgroundColor = '#f4e8dc';

// Life bars
if(document.getElementById('life_bars'))
    document.getElementById('life_bars').style.backgroundColor = '#fcbaba';
if(document.getElementById('stat-box'))
    document.getElementById('stat-box').style.backgroundColor = '#fcbaba';

// Menu button
if(document.getElementById('menubutton-left')){
    document.getElementById('menubutton-left').style.backgroundColor = '#fcbaba';
    var l = document.getElementById('menubutton-left').firstElementChild.children.length;
    for(var i = 0; i<l; i++){
        // Change la couleur des li
        document.getElementById('menubutton-left').firstElementChild.children[i].style.backgroundColor = '#f98686'; //firstElementChild ne renvoie qu'un Node, alors que firstChild peut renvoyer du texte.
        // Change la couleur des boutons dans le menu
        if(document.getElementById('menubutton-left').firstElementChild.children[i].firstElementChild)
            document.getElementById('menubutton-left').firstElementChild.children[i].firstElementChild.style.backgroundColor = '#f98686';
    }
}

// Inventories
if(document.getElementById('wrapper-invent-player-for-treasure-room'))
    document.getElementById('wrapper-invent-player-for-treasure-room').style.backgroundColor = '#f4e8dc';
if(document.getElementById('wrapper-invent-city-for-treasure-room'))
    document.getElementById('wrapper-invent-city-for-treasure-room').style.backgroundColor = '#f4e8dc';


    
*/