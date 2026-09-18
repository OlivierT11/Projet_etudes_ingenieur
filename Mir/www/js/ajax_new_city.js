window.onload = function() {

    // *******************
    // *** Map display ***
    // *******************

      var x="", y="", r="", g="", b="", a="";
      var c = document.getElementById("myCanvas");
      var ctx = c.getContext("2d");
      var img = document.getElementById("image");
      ctx.drawImage(img, 0, 0);
      var h=img.height;
      var w=img.width;
      var imageData = ctx.getImageData(0, 0, w, h);
      var data = imageData.data;
      for (var i = 0, n=data.length; i<n; i += 4) {
            //https://stackoverflow.com/questions/5606552/how-to-get-pixel-coordinates-of-a-pixel-inside-an-html5-canvas?noredirect=1&lq=1
            //concatenation js : "+=" ; php : ".="
            x+=( (i / 4) % w ).toString();
            y+=( Math.floor(((i / 4)-x.slice(-1)) / w) ).toString();
            r+=(data[i]).toString();
            g+=(data[i+1]).toString();
            b+=(data[i+2]).toString();
            a+=(data[i+3]).toString();
            x+=',';
            y+=',';
            r+=',';
            g+=',';
            b+=',';
            a+=',';
      }
      
    // ***********
    // *** Maze display ***
    // ***********
    //https://github.com/dstromberg2/maze-generator
    
    //set maze size
    var maxeSizeX = 20, mazeSizeY = 20;
    var xMaze = "", yMaze="", up="", right="", down="", left="";
    var isUp=false, isRight=false, isDown=false, isLeft=false;
   
    var disp = newMaze(maxeSizeX,mazeSizeY);
    
    for (var i = 0; i < disp.length; i++) {
        $('#maze > tbody').append("<tr>");
        for (var j = 0; j < disp[i].length; j++) {
            yMaze+=i+','; //build the ajax query for DB;
            xMaze+=j+','; 
            var selector = i+"-"+j;
            $('#maze > tbody').append("<td id='"+selector+"'>&nbsp;</td>");

            //ajoute une bordure pleine
            if (disp[i][j][0] == 0) { 
                $('#'+selector).css('border-top', '2px solid black');
                up+="1,"; //build the ajax query for DB;
                isUp=true;
            }
            if (disp[i][j][1] == 0) { 
                $('#'+selector).css('border-right', '2px solid black'); 
                right+="1,";
                isRight=true;
                
            }
            if (disp[i][j][2] == 0) { 
                $('#'+selector).css('border-bottom', '2px solid black');
                down+="1,";
                isDown=true;
                
            }
            if (disp[i][j][3] == 0) { 
                $('#'+selector).css('border-left', '2px solid black'); 
                left+="1,";
                isLeft=true;
            }

            //ajoute une bordure vide
            if(isUp==false) {up+="0,";}
            if(isRight==false) {right+="0,";}
            if(isDown==false) {down+="0,";}
            if(isLeft==false) {left+="0,";}

            isUp=false;
            isRight=false;
            isDown=false;
            isLeft=false;
        }
        $('#maze > tbody').append("</tr>");
    }


    // *********************
    // *** Abyss display ***
    // *********************

    var xAbyss="", yAbyss="", rAbyss="", gAbyss="", bAbyss="", aAbyss="";
    var c = document.getElementById("myCanvasAbyss");
    var ctx = c.getContext("2d");
    var img = document.getElementById("imageAbyss");
    ctx.drawImage(img, 0, 0);
    var h=img.height;
    var w=img.width;
    var imageData = ctx.getImageData(0, 0, w, h);
    var data = imageData.data;
    for (var i = 0, n=data.length; i<n; i += 4) {
          xAbyss+=( (i / 4) % w ).toString();
          yAbyss+=( Math.floor(((i / 4)-xAbyss.slice(-1)) / w) ).toString();
          rAbyss+=(data[i]).toString();
          gAbyss+=(data[i+1]).toString();
          bAbyss+=(data[i+2]).toString();
          aAbyss+=(data[i+3]).toString();
          xAbyss+=',';
          yAbyss+=',';
          rAbyss+=',';
          gAbyss+=',';
          bAbyss+=',';
          aAbyss+=',';
    }
    
    // create a new city if player join and no place left in existing cities
    // sends all the parameters defined above for city creation
    if(document.getElementById("enter-new-city")){
        document.getElementById("enter-new-city").addEventListener("click", function(event) {

            var me = $(this);
            // Prevents multi clic
            if ( me.data('requestRunning') ) {
                return;
            }
            me.data('requestRunning', true);

            $.ajax({
                type: "POST",
                url: "core/ajax/ajax_general.php",
                data: {action: 'joinCity',
                xMap: x, yMap: y, rMap: r, gMap: g, bMap: b, aMap: a, //map
                xMaze: xMaze, yMaze: yMaze, up: up, right: right, down: down, left: left, //dj
                xAbyss: xAbyss, yAbyss: yAbyss, rAbyss: rAbyss, gAbyss: gAbyss, bAbyss: bAbyss, aAbyss: aAbyss //abyss
                }, 
                success: function (response)
                {
                    if(response != '')
                    {
                        alert(response);
                    }
                    else
                    {
                        window.location.replace("index.php?page=intro");
                    }
                },
                complete: function() {
                    me.data('requestRunning', false);
                },
                error: function (response)
                {
                    if(response != '')
                    {
                        alert(response);
                    }
                }
            });
        });
    }

}
    
function newMaze(x, y) {

    // Establish variables and starting grid
    var totalCells = x*y;
    var cells = new Array();
    var unvis = new Array();
    for (var i = 0; i < y; i++) {
        cells[i] = new Array();
        unvis[i] = new Array();
        for (var j = 0; j < x; j++) {
            cells[i][j] = [0,0,0,0];
            unvis[i][j] = true;
        }
    }
    
    // Set a random position to start from
    var currentCell = [Math.floor(Math.random()*y), Math.floor(Math.random()*x)];
    var path = [currentCell];
    unvis[currentCell[0]][currentCell[1]] = false;
    var visited = 1;
    
    // Loop through all available cell positions
    while (visited < totalCells) {
        // Determine neighboring cells
        var pot = [[currentCell[0]-1, currentCell[1], 0, 2],
                [currentCell[0], currentCell[1]+1, 1, 3],
                [currentCell[0]+1, currentCell[1], 2, 0],
                [currentCell[0], currentCell[1]-1, 3, 1]];
        var neighbors = new Array();
        
        // Determine if each neighboring cell is in game grid, and whether it has already been checked
        for (var l = 0; l < 4; l++) {
            if (pot[l][0] > -1 && pot[l][0] < y && pot[l][1] > -1 && pot[l][1] < x && unvis[pot[l][0]][pot[l][1]]) { neighbors.push(pot[l]); }
        }
        
        // If at least one active neighboring cell has been found
        if (neighbors.length) {
            // Choose one of the neighbors at random
            next = neighbors[Math.floor(Math.random()*neighbors.length)];
            
            // Remove the wall between the current cell and the chosen neighboring cell
            cells[currentCell[0]][currentCell[1]][next[2]] = 1;
            cells[next[0]][next[1]][next[3]] = 1;
            
            // Mark the neighbor as visited, and set it as the current cell
            unvis[next[0]][next[1]] = false;
            visited++;
            currentCell = [next[0], next[1]];
            path.push(currentCell);
        }
        // Otherwise go back up a step and keep going
        else {
            currentCell = path.pop();
        }
    }
    return cells;
}
