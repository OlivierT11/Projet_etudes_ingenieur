/*

Example of http.get request to php server

//get player data
  http.get({
      host: '127.0.0.1',
      port: '8080',
      path: '/mir/test-http-nodejs.php?test=test'
  }, function(response) {
      // Continuously update stream with data
      var body = '';
      response.on('data', function(d) {
          body += d;
      });
      response.on('end', function() {

          // Set player info
          var playerInfo = JSON.parse(body);
          //console.log(playerInfo);
          var id_city = parseInt(playerInfo.id_city);
          var id_player = parseInt(playerInfo.id_player);
          //var area = playerInfo.player_area;
          var x = parseInt(playerInfo.player_pos_x);
          var y = parseInt(playerInfo.player_pos_y);
          var minx = x -2;
          var maxx = x +2;
          var miny = y -2;
          var maxy = y +2;
       });
  }).on('error', function(err) {
    // handle errors with the request itself
    console.error('Error with the request:', err.message);
    
  });

*/

//TODO function getRoomnameOutside()
//comme getroominside mais avec local uniquement, appelée depuis nsp local

//TODO dans global : si area != inside or camp, message = "vous êtes trop loin".

var app = require('express')(),
    server = require('http').createServer(app),
    io = require('socket.io').listen(server),
    ent = require('ent'), // Permet de bloquer les caractères HTML (sécurité équivalente à htmlentities en PHP)
    mysql = require('mysql');
    //http = require('http'),
    //fs = require('fs');

token='';

var con = mysql.createConnection({
  host: "127.0.0.1",
  user: "root",
  password: "",
  database: "demo",
  charset: 'UTF8_GENERAL_CI'
});
con.connect(function(err) {
  if (err) {
      console.log('Error connecting to Db' + err.stack);
      return;
  }
});

// Routing to client side script
app.get('/', function (req, res) {
  res.sendfile(__dirname + '/template-chat.html'); //global CB
  
  token = req.query.param; //globa as no 'var'
  //app.set('token', req.query.param); //other way to do it

  //console.log(req.query.param);
});
app.get('/localnsp', function (req, res) {
  res.sendfile(__dirname + '/template-chat-local.html'); //local CB
  token = req.query.param;
});



/*************************************************************** */
/* Connects the user to the correct room in the GENERAL chatbox. */
/*************************************************************** */
// The player can discuss with all the other players in the city.
// Amel : limit the scope to certain areas, like biome center, dj or dark area.
// amel 2 : add a global chatbox for the game.
// amel 3 : add /? ; /me etc.
// amel 4 : click on username to display it.
function connectToCorrectRoomGeneral(socket, playerInfo, player_muted_list) {
  
  // Set player info from player obj
  var id_city = playerInfo.id_city;

  //build the room name accordingly
  var roomname = '';
  roomname += id_city;
  roomname += 'general';

  //Create or join the room
  socket.join(roomname); 
  
  //keep the room name is session variable, as it can"t be returned as the http request is asynchronous.
  //it will be used in event onmessage, so the asynchronous request has all the time to end.
  socket.roomname=roomname; 
  console.log('the person has been placed in the room'+roomname);

  //Build the query for msg
  // The player receives all the messages from its city
  var query = 'SELECT id_player, msg_content, msg_date, player_area, name, x, y FROM chatbox_msg WHERE id_city='+id_city+' AND is_general=1 ORDER BY id_msg ASC LIMIT 50';
  con.query(query, function (err, result) {
    if (err) throw err;

    var n = result.length;
    for (var i = 0; i < n; i++) {

        //translate the area to display alongside message
        var area = '';
        switch(result[i].player_area){
          case 'inside':
            area = 'En ville';
            break;
          case 'camp':
            area = 'Camp';
            break;
          case 'outside':
            area = 'Dehors';
            break;
          case 'dj':
            area = 'Caverne';
            break;
          case 'abyss':
            area='Abysse';
            break;
          case 'hell':
            area = 'Enfer';
            break;
          case 'dj2':
            area= 'Fond de la caverne';
            break;
          default:
            break;
        }

        // Do not emit the message if the player is mute.
        if(!player_muted_list.includes(result[i].id_player)){

          //build the messages
          var message = '';
          result[i].msg_content = ent.decode(result[i].msg_content);
          message = message + result[i].msg_date + ' - <strong class="clickable-name">'+result[i].name+'</strong> ('+result[i].x+','+result[i].y+') '+area+' : ' + result[i].msg_content;
          //emit the messages to the right roomname
          io.to(roomname).emit('message', {message: message});

        }

    }

    // Count the number of users connected to that room, and emit the message 1st.
    var room = io.sockets.adapter.rooms[roomname];
    var playerNbr = room.length;
    //console.log(playerNbr);
    var messageNbr = '';
    messageNbr = messageNbr + '<em><strong>' + playerNbr + ' joueur(s) connecté(s).</strong></em>';
    //console.log(messageNbr);
    io.to(roomname).emit('user_nbr', {message: messageNbr});

  });
     
}

/************************************************************* */
/* Connects the user to the correct room in the LOCAL chatbox. */
/************************************************************* */
// Connects the user to the correct room in the local chatbox.
// If the player is inside, it can discuss with all the players also inside, and inside camps.
// If the player is outside, it can discuss with people around it over 9 areas.
function connectToCorrectRoomLocal(socket, playerInfo, player_muted_list) {
  
  // Set player info from player obj
  //pas de parseInt car pas de string car pas de requête HTTP
  var id_player = playerInfo.id_player;
  var id_city = playerInfo.id_city;
  var area = playerInfo.area;
  var x = playerInfo.x;
  var y = playerInfo.y;
  var minx = x -2;
  var maxx = x +2;
  var miny = y -2;
  var maxy = y +2;

  //build the room name accordingly
  var roomname = '';
  roomname += id_city;
  switch(area){
    case 'inside':
      roomname += 'i';
      roomname += (x + '' + y);
      areaText = 'En ville';
      break;
    case 'camp':
      roomname += 'c';
      roomname += (x + '' + y);
      areaText = 'Camp';
      break;
    case 'outside':
      roomname += 'o';
      roomname += (x + '' + y);
      areaText = 'Dehors';
      break;
    case 'dj':
      roomname += 'd';
      roomname += (x + '' + y);
      areaText = 'Caverne';
      break;
    case 'abyss':
      roomname += 'a';
      roomname += (x + '' + y);
      areaText='Abysse';
      break;
    case 'hell':
      roomname += 'h';
      roomname += (x + '' + y);
      areaText = 'Enfer';
      break;
    case 'dj2':
      roomname += 'd2';
      roomname += (x + '' + y);
      areaText= 'Fond de la caverne';
      break;
    default:
  }


  //Create or join the room
  socket.join(roomname); 
  
  //keep the room name is session variable, as it can"t be returned as the http request is asynchronous.
  //it will be used in event onmessage, so the asynchronous request has all the time to end.
  socket.roomname=roomname; 
  console.log('the person has been placed in the room'+roomname);

  //Build the query for msg
  //if the player is outside the city or a camp, get all msgs within 2 areas from the player (25 areas in total)
  //if the player is inside a city, get all msgs from the city
  //if the player is inside a camp, get all msgs from the camp
  if(area == 'inside'){
    var query = 'SELECT id_player, msg_content, msg_date, player_area, name, x, y FROM chatbox_msg WHERE id_city='+id_city+' AND player_area="inside" AND is_general=0 ORDER BY id_msg ASC LIMIT 50';
  }
  else if (area == 'camp'){
    var query = 'SELECT id_player, msg_content, msg_date, player_area, name, x, y FROM chatbox_msg WHERE id_city='+id_city+' AND player_area="camp" AND is_general=0 ORDER BY id_msg ASC LIMIT 50';
  }
  else if(area == 'outside'){
    var query = 'SELECT cm.id_player, cm.msg_content, cm.msg_date, m.x, m.y, cm.name, cm.x AS player_x, cm.y AS player_y ' + 
                'FROM chatbox_msg cm '+
                  'INNER JOIN map m ON (cm.x = m.x AND cm.y = m.y) ' +
                'WHERE cm.id_city='+id_city+' AND cm.player_area="outside" ' +
                'AND (m.x BETWEEN '+minx+' AND '+maxx+') AND (m.y BETWEEN '+miny+' AND '+maxy+') AND is_general=0 ' +
                'ORDER BY cm.id_msg ASC LIMIT 50'; //ASC goes w/ prepend()
  }
  else if(area == 'dj'){
    var query = 'SELECT cm.id_player, cm.msg_content, cm.msg_date, dj.dj_x, dj.dj_y, cm.name, cm.x AS player_x, cm.y AS player_y ' + 
                'FROM chatbox_msg cm '+
                  'INNER JOIN dj ON (cm.x = dj.dj_x AND cm.y = dj.dj_y) ' +
                'WHERE cm.id_city='+id_city+' AND cm.player_area="dj" ' +
                'AND (dj.dj_x BETWEEN '+minx+' AND '+maxx+') AND (dj.dj_y BETWEEN '+miny+' AND '+maxy+') AND is_general=0 ' +
                'ORDER BY cm.id_msg ASC LIMIT 50';
  }
  else if(area == 'dj2'){
    var query = 'SELECT cm.id_player, cm.msg_content, cm.msg_date, dj2.dj2_x, dj2.dj2_y, cm.name, cm.x AS player_x, cm.y AS player_y ' + 
                'FROM chatbox_msg cm '+
                  'INNER JOIN dj2 ON (cm.x = dj2.dj2_x AND cm.y = dj2.dj2_y) ' +
                'WHERE cm.id_city='+id_city+' AND cm.player_area="dj2" ' +
                'AND (dj2.dj2_x BETWEEN '+minx+' AND '+maxx+') AND (dj2.dj2_y BETWEEN '+miny+' AND '+maxy+') AND is_general=0 ' +
                'ORDER BY cm.id_msg ASC LIMIT 50';
  }
  else if(area == 'abyss'){
    var query = 'SELECT cm.id_player, cm.msg_content, cm.msg_date, a.abyss_x, a.abyss_y, cm.name, cm.x AS player_x, cm.y AS player_y ' + 
                'FROM chatbox_msg cm '+
                  'INNER JOIN abyss a ON (cm.x = a.abyss_x AND cm.y = a.abyss_y) ' +
                'WHERE cm.id_city='+id_city+' AND cm.player_area="abyss" ' +
                'AND (a.abyss_x BETWEEN '+minx+' AND '+maxx+') AND (a.abyss_y BETWEEN '+miny+' AND '+maxy+') AND is_general=0 ' +
                'ORDER BY cm.id_msg ASC LIMIT 50';
  }
  else if(area == 'hell'){
    var query = 'SELECT cm.id_player, cm.msg_content, cm.msg_date, h.hell_x, h.hell_y, cm.name, cm.x AS player_x, cm.y AS player_y ' + 
                'FROM chatbox_msg cm '+
                  'INNER JOIN hell h ON (cm.x = h.hell_x AND cm.y = h.hell_y) ' +
                'WHERE cm.id_city='+id_city+' AND cm.player_area="hell" ' +
                'AND (h.hell_x BETWEEN '+minx+' AND '+maxx+') AND (h.hell_y BETWEEN '+miny+' AND '+maxy+') AND is_general=0 ' +
                'ORDER BY cm.id_msg ASC LIMIT 50';
  }
  con.query(query, function (err, result) {
    if (err) throw err;
    //console.log(roomname); 
    //console.log(result.length); 

    var n = result.length;
    for (var i = 0; i < n; i++) {

        //build the messages
        var message = '';

        // Do not emit the message if the player is mute.
        if(!player_muted_list.includes(result[i].id_player)){
          
          //if the X and Y pos of the msg are 2 areas far from the player, display "Vous entendez (nom) parler loin de vous."
          if(result[i].x == minx || result[i].x==maxx || result[i].y == miny || result[i].y == maxy){
              message = result[i].msg_date + ' - Vous entendez <strong class="clickable-name">'+result[i].name+'</strong> ('+result[i].x+','+result[i].y+') parler, mais êtes trop loin pour entendre';
            }
          else {
            result[i].msg_content = ent.decode(result[i].msg_content);
            message = message + result[i].msg_date + ' - <strong class="clickable-name">'+result[i].name+'</strong> ('+result[i].x+','+result[i].y+') '+areaText+' : ' + result[i].msg_content;
          
          }
          
          //emit the messages to the right roomname
          // REMEMBER that io is default namespace. For custom namespace, "of" must be set. By default io = io.of('')
          io.of('/localnsp').to(roomname).emit('message', {message: message});

      }

    }

    // Count the number of users connected to that room, and emit the message 1st.
    /*
    var room = io.of('/localnsp').sockets.adapter.rooms[roomname];
    var playerNbr = room.length;
    console.log(playerNbr);
    var messageNbr = '';
    messageNbr = messageNbr + '<em><strong>' + playerNbr + ' joueur(s) connecté(s).</strong></em>';
    console.log(messageNbr);
    io.of('/localnsp').to(roomname).emit('user_nbr', {message: messageNbr});
    */


  });

     
}

function identifyUser(token){
  con.connect(function(err) {
    if (err) throw err;
    var query = "SELECT p.id_player, p.id_city, p.player_name, p.id_dj, p.id_abyss, p.player_pos_x, p.player_pos_y, p.player_area "+
                 "FROM player p "+
                    "INNER JOIN account a ON a.id_player = p.id_player "+
                 "WHERE a.acc_token = '"+token+"' "+
                 "LIMIT 1";
    con.query(query, function (err, result, fields) {
      if (err) throw err;

      //build the player array as global var
      var playerInfo = {
        id_player: result.id_player,
        id_city: result.id_city,
        name: result.player_name,
        id_dj: result.id_dj,
        id_abyss:result.id_abyss,
        x: result.player_pos_x,
        y: result.player_pos_y,
        area: result.player_area
      };
      //console.log(playerInfo.id_player);
      return playerInfo;
        
    });
  });
}


//This is the main nsp / global chatbox. io.sockets.on() is equivalent to io.of('').on()
io.sockets.on('connection', function (socket) {

  socket.on('getMyUserData', function() {
    //console.log('con');
    var query = "SELECT p.id_player, p.id_city, p.player_name, p.id_dj, p.id_abyss, p.player_pos_x, p.player_pos_y, p.player_area "+
                 "FROM player p "+
                    "INNER JOIN account a ON a.id_player = p.id_player "+
                 "WHERE a.acc_token = '"+token+"' "+
                 "LIMIT 1";
    con.query(query, function (err, result) {
      if (err) throw err;

      //console.log(result[0].id_player);

      //build the player array as global var
      playerInfo = {
        id_player: result[0].id_player,
        id_city: result[0].id_city,
        name: result[0].player_name,
        id_dj: result[0].id_dj,
        id_abyss:result[0].id_abyss,
        x: result[0].player_pos_x,
        y: result[0].player_pos_y,
        area: result[0].player_area
      };
      
      //get the list of muted players
      var query = "SELECT id_player_muted FROM player_mute "+
                 "WHERE id_player = "+playerInfo.id_player;
      con.query(query, function (err, result) {
        if (err) throw err;

        var n = result.length;

        // Global
        player_muted_list = [];

        for(var i=0; i<n; i++){
          player_muted_list.push(result[i].id_player_muted);
        }
          
        connectToCorrectRoomGeneral(socket, playerInfo, player_muted_list);
      });
        
      
    });

  });
    

    // Dès qu'on nous donne un pseudo, on le stocke en variable de session et on informe les autres personnes
    socket.on('nouveau_client', function(pseudo) {
        pseudo = ent.encode(pseudo);
        socket.pseudo = pseudo;
        socket.broadcast.emit('nouveau_client', pseudo);

        //variable de session ?
        // https://github.com/xpepermint/socket.io-express-session 
    });

    // Dès qu'on reçoit un message, on le place en BDD
    socket.on('message', function (message) {
        message = ent.encode(message);
        //socket.broadcast.emit('message', {pseudo: socket.pseudo, message: message});

        var date = new Date();
        var hours = date.getHours();
        var minutes = date.getMinutes();
        if(minutes.length == 1) //pb
          minutes = '0'+minutes; 
        
        var query = "INSERT INTO chatbox_msg (msg_content, msg_date, id_player, id_city, player_area, is_general, name, x, y) " +
                        "VALUES ('"+message+"', '"+hours+":"+minutes+"', "+playerInfo.id_player+", "+playerInfo.id_city+", '"+playerInfo.area+"', 1, '"+playerInfo.name+"', "+playerInfo.x+", "+playerInfo.y+")";
        con.query(query, function (err) {
          if (err) throw err;

          // Do not emit the message if the player is mute.
          if(!player_muted_list.includes(playerInfo.id_player)){

            //send new message back to display
            var newMessage = '';
            message = ent.decode(message);
            newMessage = newMessage + hours + ':' + minutes + ' - <strong class="clickable-name">'+playerInfo.name+'</strong> ('+playerInfo.x+','+playerInfo.y+') : ' + message;
          
            //emit the messages to the right roomname
            io.to(socket.roomname).emit('message', {message: newMessage});

          }

        });
        
    });

    /*
    socket.on('user_nbr', function () {
      
      var room = io.sockets.adapter.rooms[socket.roomname];
      console.log(room);
      var player_nbr = room.length;

      io.to(socket.roomname).emit('user_nbr', {data: player_nbr});

    });
    */
      

    /*
    var con = mysql.createConnection({
      host: "127.0.0.1",
      user: "root",
      password: "",
      database: "demo"
  });
*/
  /*
  con.connect(function(err) {
    if (err) throw err;
    
    
    
    console.log("Connected!");
    var query = 'SELECT msg_content, msg_date FROM chatbox_msg WHERE id_city=".$_SESSION['id_city']." ORDER BY id_msg DESC LIMIT 50';
    con.query(query, function (err, result, fields) {
      if (err) throw err;
      //console.log(result); 
      //console.log(result.length); 
      for (var i = 0; i < result.length; i++) { // 2 rows returned
           //build the messages
           var message = '';
           message = message + '<p>' + result[i].msg_date + ' | Otochiro : ' + result[i].msg_content + '</p>';
           socket.broadcast.emit('message', {pseudo: socket.pseudo, message: message});
        
      }

    });
    

  });
  */
 
});

//local CB
var localnsp = io //namespace for local chatboxes. A namespace has a url attached to it, while a room is only accessible server-side and can be easily created automatically.
    .of('/localnsp') //"socket" is equivalent to of('') , '' being the default empty nsp
    .on('connection', function(socket){

      socket.on('getMyUserData', function() {
        var query = "SELECT p.id_player, p.id_city, p.player_name, p.id_dj, p.id_abyss, p.player_pos_x, p.player_pos_y, p.player_area "+
                     "FROM player p "+
                        "INNER JOIN account a ON a.id_player = p.id_player "+
                     "WHERE a.acc_token = '"+token+"' "+
                     "LIMIT 1";
        con.query(query, function (err, result) {
          if (err) throw err;
    
          //console.log(result[0].id_player);
    
          //build the player array as global var
          playerInfo = {
            id_player: result[0].id_player,
            id_city: result[0].id_city,
            name: result[0].player_name,
            id_dj: result[0].id_dj,
            id_abyss:result[0].id_abyss,
            x: result[0].player_pos_x,
            y: result[0].player_pos_y,
            area: result[0].player_area
          };
          
          //get the list of muted players
          var query = "SELECT id_player_muted FROM player_mute "+
                     "WHERE id_player = "+playerInfo.id_player;
          con.query(query, function (err, result) {
            if (err) throw err;

            var n = result.length;

            // Global
            player_muted_list = [];

            for(var i=0; i<n; i++){
              player_muted_list.push(result[i].id_player_muted);
            }
              
            connectToCorrectRoomLocal(socket, playerInfo, player_muted_list);
          });

        });
    
      });

      // Dès qu'on nous donne un pseudo, on le stocke en variable de session et on informe les autres personnes
    socket.on('nouveau_client', function(pseudo) {
        pseudo = ent.encode(pseudo);
        socket.pseudo = pseudo;
        socket.broadcast.emit('nouveau_client', pseudo);

        //variable de session ?
        // https://github.com/xpepermint/socket.io-express-session 
    });

    // Dès qu'on reçoit un message, on le place en BDD
    socket.on('message', function (message) {
        message = ent.encode(message);
        //socket.broadcast.emit('message', {pseudo: socket.pseudo, message: message});

        var date = new Date();
        var hours = date.getHours();
        var minutes = date.getMinutes();
        if(minutes.length == 1) //pb
          minutes = '0'+minutes; 
        
        var query = "INSERT INTO chatbox_msg (msg_content, msg_date, id_player, id_city, player_area, is_general, name, x, y) " +
                        "VALUES ('"+message+"', '"+hours+":"+minutes+"', "+playerInfo.id_player+", "+playerInfo.id_city+", '"+playerInfo.area+"', 0, '"+playerInfo.name+"', "+playerInfo.x+", "+playerInfo.y+")";
        con.query(query, function (err) {
          if (err) throw err;

          // Do not emit the message if the player is mute.
          if(!player_muted_list.includes(playerInfo.id_player)){

            //send new message back to display
            var newMessage = '';
            message=ent.decode(message);
            newMessage = newMessage + hours + ':' + minutes + ' - <strong class="clickable-name">'+playerInfo.name+'</strong> ('+playerInfo.x+','+playerInfo.y+') : ' + message;
          
            //emit the messages to the right roomname
            io.of('/localnsp').to(socket.roomname).emit('message', {message: newMessage});
            // I can emit to other namespaces or rooms from here.

          }

        });
        
    });

});

server.listen(3381, '127.0.0.1');
