/*

AJAX code relative to item usage (bank, inventory, forge, armory etc).
The item displayng inside inventories is made in model/ctrl

*/


/*************************** */
/* Select craft mat on click */
/*************************** */

/************************ */
/* Give all items to city */
/************************ */

if(document.getElementById("give-all-to-city")){
  document.getElementById("give-all-to-city").addEventListener("click", function(event) {
 
    if(confirm("Donner tous vos objets à la ville ? Vous gardez les objets équipés.")){

      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

      $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {action: 'give-all-to-city'}, 
        success: function (response) {
          if(response == "error"){
            alert("Une erreur est survenue, merci de recharger la page.");
          } else {
            location.reload();
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      }
      });
      
    }
  
  });
}


/************************************* */
/* SHIFT + CLICK (INVENT, CITY & EQUIP)*/
/************************************* */

/* KEPT THERE FOR DEMO. SELECTING WORKS. just add ui-state-default and non-draggable.
// See doc https://jqueryui.com/selectable/#display-grid
// This solution is abandonned because of collision with draggable. Both use the mousedown event so a selecting item cannot be dragged and vice-versa.
$( function() {
  // Use the metaKey flag to prevent multi selection with drag while allowing ctrl+click  https://stackoverflow.com/questions/5517900/disabling-ctrl-click-on-jquery-ui-selectable 
  $( "#invent-player" ).selectable({
    // Prevent selecting non wood items and empty items (jquery standard)
    filter: ".item-wood", // div:not(".non-draggable")
    selecting: function(event, ui) { 
      // Prevent the selection of more than 4 resources
      console.log(event); //
      event.target; // #invent-player.ui-selectable (the container)
      console.log(ui);// <div id="0" class="item item-lower ui-state…ui-selectee ui-selected" draggable="true" ondragstart="drag(event)">
    },
    selected: function(event, ui){
      // Add the selected elements to the craft containers
      var elem = ui.selected;
      var container1 = document.getElementById('craft-mat-1');
      var container2 = document.getElementById('craft-mat-2');
      var container3 = document.getElementById('craft-mat-3');
      var container4 = document.getElementById('craft-mat-4');
      
    }
  });
  
} );
*/

/******* */
/* NOTES */
/******* */

// Every item in inventories is bound to 3 events :

// left-click : select the item (clic on a craft mat to put it in the craft-mat wrappers)
// right-clic : consume (txt added in tooltip)
// mousedown : drag

/************************ */
/*SELECT CRAFT MAT ON CLIC*/
/************************ */

// FORGE
// select on clic is only allowed in forge
if(document.getElementById('wrapper-craft-mats')){

  if(document.getElementsByClassName("item-wood")){

    var className = document.getElementsByClassName("item-wood");
    var n = className.length;

    allowCraftOnClick(className);

  } 

}

// KITCHEN
if(document.getElementById('wrapper-cook-mats')){

  if(document.getElementsByClassName("item-orange")){

    var className = document.getElementsByClassName("item-orange");
    var n = className.length;

    allowCraftOnClick(className);
       
  } 

  if(document.getElementsByClassName("item-honey")){

    var className = document.getElementsByClassName("item-honey");
    var n = className.length;

    allowCraftOnClick(className);

  } 

  if(document.getElementsByClassName("item-baked_orange")){

    var className = document.getElementsByClassName("item-baked_orange");
    var n = className.length;

    allowCraftOnClick(className);

  } 

  if(document.getElementsByClassName("item-baked_honey")){

    var className = document.getElementsByClassName("item-baked_honey");
    var n = className.length;

    allowCraftOnClick(className);

  }

}

function allowCraftOnClick(className){

  for (var i = 0; i < n; i++) {
    className[i].addEventListener("click", function(ev) {

      //get the content of the clicked element
      var elem = ev.target;

      // set a border to show that the element is selected
      var elemId = elem.id;
      document.getElementById(elemId).style.border="2px solid gold";
      
      //get the containers
      var container1 = document.getElementById('craft-mat-1');
      var container2 = document.getElementById('craft-mat-2');
      var container3 = document.getElementById('craft-mat-3');
      var container4 = document.getElementById('craft-mat-4');

      // create the new div that will hold the item in the container
      var newDiv = document.createElement('DIV');
      newDiv.setAttribute('id', elem.id);
      newDiv.setAttribute('class', elem.className);
      // The new item cannot be dragged. It is not a "real" item, but rather an image to show what is going to be used for crafting.
      //newDiv.setAttribute('draggable', 'true');
      //newDiv.setAttribute('ondragstart', 'drag(event)');
      newDiv.innerHTML = elem.innerHTML;
      
      // if the item is already in a craft container, then remove the item. Also unselect the item in invent.
      var itemIsRemoved = 0;
      if(container1.innerHTML != '' && elemId==container1.firstChild.id){

        container1.innerHTML = '';
        var inventElem = document.getElementById(elemId); // the item is removed, so the next item with this ID is now the one in inventory.
        inventElem.style.border='1px solid black'; //unselect the item.
        itemIsRemoved = 1;

      } else if (container2.innerHTML != '' && elemId==container2.firstChild.id){

          container2.innerHTML = '';
          var inventElem = document.getElementById(elemId); // the item is removed, so the next item with this ID is now the one in inventory.
          inventElem.style.border='1px solid black'; //unselect the item.
          itemIsRemoved = 1;

      } else if (container3.innerHTML != '' && elemId==container3.firstChild.id){
          
          container3.innerHTML = '';
          var inventElem = document.getElementById(elemId); // the item is removed, so the next item with this ID is now the one in inventory.
          inventElem.style.border='1px solid black'; //unselect the item.
          itemIsRemoved  = 1;

      } else if (container4.innerHTML != '' && elemId==container4.firstChild.id){
          
          container4.innerHTML = '';
          var inventElem = document.getElementById(elemId); // the item is removed, so the next item with this ID is now the one in inventory.
          inventElem.style.border='1px solid black'; //unselect the item.
          itemIsRemoved = 1;
      }

      // Place the element in the 1st empty container if none has been removed
      if(itemIsRemoved == 0){

        if(container1.innerHTML == ''){
          isNewItem = 1;
          container1.appendChild(newDiv);
        }
        else if(container2.innerHTML == ''){
          isNewItem = 1;
          container2.appendChild(newDiv);
        }
        else if(container3.innerHTML == ''){
          container3.appendChild(newDiv);
          isNewItem = 1;
        }
        else if(container4.innerHTML == ''){
          container4.appendChild(newDiv);
          isNewItem = 1;
        }

      }

      // TODO : remove the element from inventory when added to the craft box, because otherwise there will be 2 divs with same ID on page.
      // remove only if problems.
      
    });
  }
} 



/********************************** */
/*DRAG & DROP (INVENT, CITY & EQUIP)*/
/********************************** */

var draggedElemClassName;

function allowDrop(ev) {
  ev.preventDefault();
}

function drag(ev) {

  ev.dataTransfer.setData("text", ev.target.id);

  //Pour reconnaitre la classe de l'item droppé, sachant que ev.target.className est la classe du container quand on drop et de l'item quand on drag (cible de l'évent).
  draggedElemClassName = ev.target.className; 

  // Get the id of the container the item is dragged from.
  idPositionDraggedFrom = ev.target.parentNode.id;
 
  //Masque la tooltip on drag pour éviter le drop sur la tooltip.
  ev.target.lastChild.style.display='none';
  
  // Change the source element's background color to signify drag has started
  /*if (document.getElementById("invent-player") !== null) {
      document.getElementById("invent-player").style.border = "2px solid chartreuse";
      document.getElementById("invent-city").style.border = "2px solid chartreuse";
  }*/
 // test element exists : https://stackoverflow.com/questions/15666163/document-getelementbyid-will-return-null-if-element-is-not-defined 
}

//el : invent-player
function drop(ev, el) { //drop in invent or city
  ev.preventDefault();
  
  var data = ev.dataTransfer.getData("text"); //data = item id
  var firstElem = el.firstChild;

  //insert the dragged element at first position in the new container
  el.insertBefore(document.getElementById(data), firstElem);
  //el.appendChild(document.getElementById(data));
  
  //var area = ev.target.getAttribute("id"); //Pas bon car si on drop sur un autre item, area devient l'id de l'item.
  var area = el.id; //el = this 
  
  //console.log(idPositionDraggedFrom);

  var me = $(this);
  // Prevents multi clic
  if ( me.data('requestRunning') ) {
      return;
  }
  me.data('requestRunning', true);

  //store the dragged item in DB, and check max-invent
  $.ajax({
    type: "POST",
    url: "core/ajax/ajax_item.php",
    data: {idDraggedItem: data,
      droppedArea: area}, 
    success: function (response) {
      if(response == "error")
        alert("Une erreur est survenue, merci de recharger la page.");
      if(document.getElementById(idPositionDraggedFrom).parentNode.id == "wrapper-equip"){ //reload if unequip
         location.reload();
      }
    },
    complete: function() {
      me.data('requestRunning', false);
  }
  });

  /***** */
  // Remove an item-void item from the dropped invent, and add another in the source invent
  /***** */

  // get id of origin containr
  var idOrigin = idPositionDraggedFrom;

  // add 1 item void item to the origin container only in player invent (there is no item void in city or any equip wrappers)
  if(idOrigin == 'invent-player'){
    var itemVoid = document.createElement("DIV");
    itemVoid.className = "item item-void";
    document.getElementById(idOrigin).appendChild(itemVoid);  
  }     

  //get id of destination container
  var idDest = el.id;

  // remove 1 void from the destination, if max invent is not reached and if this is not invent-city
  if(idDest != 'invent-city'){
    if(document.getElementById(idDest).lastChild.className == 'item item-void')
      document.getElementById(idDest).removeChild(document.getElementById(idDest).lastChild);
  }

//ev.dataTransfer.clearData();  //Err: NoModificationAllowedError: Modifications are not allowed for this document
}

//**************** */
//SHIELD 
//**************** */

function dropShieldIn(ev){
  ev.preventDefault();

  // check if there is no item already equiped here
  if(ev.target.innerHTML != ''){
    alert('Un objet est déjà équipé');
    return 0;
  }

  //Empêche le drop si ce n'est pas un shield
  if(draggedElemClassName == "item item-shield"){ 
    var data = ev.dataTransfer.getData("text"); //data = item id
    ev.target.appendChild(document.getElementById(data));
    delete window.draggedElemClassName;
    var area = ev.target.getAttribute("id"); //equip-shield

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
      type: "POST",
      url: "core/ajax/ajax_item.php",
      data: {idDraggedItem: data,
        droppedArea: area}, 
      success: function (response) {
        if(response == 'error')
        {
          alert("Une erreur est survenue, merci de recharger la page.")
        }
        // If there is a response, it the the lvl of the dragged item for life_bar box
        else
        {
          document.getElementById('shield_bar_current').style.width = response +'0%';
        }
      },
      complete: function() {
        me.data('requestRunning', false);
    }
    });
  
  } else {
    alert("Ce n'est pas un bouclier !");
  }
} 
//**************** */
//UPPER 
//**************** */


function dropUpperIn(ev){
  ev.preventDefault();
  
  // check if there is no item already equiped here
  if(ev.target.innerHTML != ''){
    alert('Un objet est déjà équipé');
    return 0;
  }

  //Empêche le drop si ce n'est pas un upper
  if(draggedElemClassName == "item item-upper"){ 
    var data = ev.dataTransfer.getData("text"); //data = item id
    ev.target.appendChild(document.getElementById(data));
    delete window.draggedElemClassName;
    var area = ev.target.getAttribute("id"); //equip-upper

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {idDraggedItem: data,
          droppedArea: area}, 
        success: function (response) {
          if(response == "error")
          {
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          else
          {
            document.getElementById('upper_bar_current').style.width = response +'0%';
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      }
    });
  } else {
    alert("Ce n'est pas un haut d'armure !");
  }
} 


//******/
/*LOWER*/
//******/

//Empêche le drop si ce n'est pas un shield
function dropLowerIn(ev){
  ev.preventDefault();
  
  // check if there is no item already equiped here
  if(ev.target.innerHTML != ''){
    alert('Un objet est déjà équipé');
    return 0;
  }

  //Empêche le drop si ce n'est pas un upper
  if(draggedElemClassName == "item item-lower"){ 
    var data = ev.dataTransfer.getData("text"); //data = item id
    ev.target.appendChild(document.getElementById(data));
    delete window.draggedElemClassName;
    var area = ev.target.getAttribute("id"); //equip-lower

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {idDraggedItem: data,
          droppedArea: area}, 
        success: function (response) {
          if(response == "error")
          {
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          else 
          {
            document.getElementById('lower_bar_current').style.width = response +'0%';
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      }
    });
  } else {
    alert("Ce n'est pas une armure basse !");
  }
} 

//**************** */
//HELMET 
//**************** */

//Empêche le drop si ce n'est pas un shield
function dropHelmetIn(ev){
  ev.preventDefault();
  
  // check if there is no item already equiped here
  if(ev.target.innerHTML != ''){
    alert('Un objet est déjà équipé');
    return 0;
  }

  //Empêche le drop si ce n'est pas un upper
  if(draggedElemClassName == "item item-helmet"){ 
    var data = ev.dataTransfer.getData("text"); //data = item id
    ev.target.appendChild(document.getElementById(data));
    delete window.draggedElemClassName;
    var area = ev.target.getAttribute("id"); //equip-helmet

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {idDraggedItem: data,
          droppedArea: area}, 
        success: function (response) {
          if(response == "error")
          {
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          else 
          {
            document.getElementById('helmet_bar_current').style.width = response +'0%';
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      }
    });
  } else {
    alert("Ce n'est pas un casque !");
  }
} 
//**************** */
//MASK 
//**************** */

//Empêche le drop si ce n'est pas un mask
function dropMaskIn(ev){
  ev.preventDefault();
  
  // check if there is no item already equiped here
  if(ev.target.innerHTML != ''){
    alert('Un objet est déjà équipé');
    return 0;
  }

  //Empêche le drop si ce n'est pas un upper
  if(draggedElemClassName == "item item-mask"){ 
    var data = ev.dataTransfer.getData("text"); //data = item id
    ev.target.appendChild(document.getElementById(data));
    delete window.draggedElemClassName;
    var area = ev.target.getAttribute("id"); //equip-mask

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {idDraggedItem: data,
          droppedArea: area}, 
        success: function (response) {
          if(response == "error")
          {
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          else
          {
            document.getElementById('mask_bar_current').style.width = response +'0%';
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      }
    });
  } else {
    alert("Ce n'est pas un masque !");
  }
} 


//**************** */
// SPEAR (EQUIP) 
//**************** */

//Empêche le drop si ce n'est pas une spear
function dropSpearIn(ev){
  ev.preventDefault();
  
  // check if there is no item already equiped here
  if(ev.target.innerHTML != ''){
    alert('Un objet est déjà équipé');
    return 0;
  }

  //Empêche le drop si ce n'est pas une lance
  if(draggedElemClassName == "item item-spear"){ 
    var data = ev.dataTransfer.getData("text"); //data = item id
    ev.target.appendChild(document.getElementById(data));
    delete window.draggedElemClassName;
    var area = ev.target.getAttribute("id"); //equip-mask

    var me = $(this);
    // Prevents multi clic
    if ( me.data('requestRunning') ) {
        return;
    }
    me.data('requestRunning', true);

    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {idDraggedItem: data,
          droppedArea: area}, 
        success: function (response) {
          if(response == "error")
          {
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          else 
          {
            document.getElementById('spear_bar_current').style.width = response +'0%';
          }
        },
        complete: function() {
          me.data('requestRunning', false);
      }
    });
  } else {
    alert("Ce n'est pas une lance !");
  }
} 



//**************** */
// CRAFT & COOK
//**************** */

function dropCraftMatdIn(ev, el){
    ev.preventDefault();
    
    //vérifie que l'item déposé est bien un bois (amèl : de type BDD "ressource")
    if(draggedElemClassName == "item item-wood"){ //n'autoriser que les bois
      var data = ev.dataTransfer.getData("text"); //data = item id
      if (ev.target.innerHTML == ''){ //n'autoriser qu'un item par case
        el.appendChild(document.getElementById(data));
        delete window.draggedElemClassName;
      } else {
        alert("Il y a déjà un objet !"); //bug
      }
    } else {
      alert("Ce n'est pas un matériau !");
    }
  }

function dropCookMatdIn(ev, el){
    ev.preventDefault();
    
    //vérifie que l'item déposé est bien un ingrédient 
    if(draggedElemClassName == "item item-orange"
        || draggedElemClassName == "item item-honey"
        || draggedElemClassName == "item item-cake"
        || draggedElemClassName == "item item-baked_orange"
        || draggedElemClassName == "item item-baked_honey")
      { //n'autoriser que les ingrédients
        var data = ev.dataTransfer.getData("text"); //data = item id
        if (ev.target.innerHTML == ''){ //n'autoriser qu'un item par case
          el.appendChild(document.getElementById(data));
          delete window.draggedElemClassName;
      } else {
        alert("Il y a déjà un objet !"); //bug
      }
    } else {
      alert("Ce n'est pas un ingrédient !");
    }
}
  

if(document.getElementById("btn-craft")){
    document.getElementById("btn-craft").addEventListener("click", function(ev) {
    //Teste les 5 div craft-mat et le sélecteur de pièce d'amure
  
      if (
        document.getElementById("craft-mat-1").innerHTML != '' //innerHTML renvoie le contenu HTML d'un élément sous forme de string
        && document.getElementById("craft-mat-2").innerHTML != ''
        && document.getElementById("craft-mat-3").innerHTML != ''
        && document.getElementById("craft-mat-4").innerHTML != ''
        //&& document.getElementById("craft-mat-5").innerHTML != ''
        ) {
        
          var idCraftMat1 = document.getElementById("craft-mat-1").firstChild.getAttribute("id");
          var idCraftMat2 = document.getElementById("craft-mat-2").firstChild.getAttribute("id");
          var idCraftMat3 = document.getElementById("craft-mat-3").firstChild.getAttribute("id");
          var idCraftMat4 = document.getElementById("craft-mat-4").firstChild.getAttribute("id");
          //var idCraftMat5 = document.getElementById("craft-mat-5").firstChild.getAttribute("id");
  
          var itemType = document.getElementById("item-craft-selector").value;

          var me = $(this);
          // Prevents multi clic
          if ( me.data('requestRunning') ) {
              return;
          }
          me.data('requestRunning', true);
  
          $.ajax({
              type: "POST",
              url: "core/ajax/ajax_item.php",
              data: {
                idCraftMat1: idCraftMat1, 
                idCraftMat2: idCraftMat2,
                idCraftMat3: idCraftMat3,
                idCraftMat4: idCraftMat4,
                //idCraftMat5: idCraftMat5,
                itemType: itemType
              }, 
              success: function (response) {
                if(response == "error")
                {
                  alert("Une erreur est survenue, merci de recharger la page.");
                }
                location.reload();
              },
              complete: function() {
                me.data('requestRunning', false);
            },
              error: function (response) {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
          });
        
      } else {
        alert("Il manque des matériaux.");
      }
    });
  }


//**************** */
// USE CONSUMABLES */
//**************** */

// consume the item on riht-click on it
if(document.getElementsByClassName("item-orange")){

  var className = document.getElementsByClassName("item-orange");
  var n = className.length;

  for (var i = 0; i < n; i++) {
    className[i].addEventListener("contextmenu", function(ev) {
      ev.preventDefault(); // Prevents the standard right-clic menu to show.

      var conf = confirm("Manger pour regagner de l'énergie ?");
      if(conf){

        var idItemEaten = ev.target.id;

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {eat: "orange",
              idItemEaten: idItemEaten}, 
            success: function (response) {
              if(response == "error")
              {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
              else {
                //var newFoodValue = response;
                //document.getElementById("food_bar_current").style.width = newFoodValue;
                location.reload();
              }
            },
            complete: function() {
              me.data('requestRunning', false);
          }
        });

      }
      return false; // Prevents the standard right-clic menu to show.

    }, false); // Prevents the standard right-clic menu to show.
  }
}
if(document.getElementsByClassName("item-honey")){

  var className = document.getElementsByClassName("item-honey");
  var n = className.length;

  for (var i = 0; i < n; i++) {
    className[i].addEventListener("contextmenu", function(ev) {
      ev.preventDefault();

      var conf = confirm("Manger pour regagner du moral ?");
      if(conf){

        var idItemEaten = ev.target.id;

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {eat: "honey",
              idItemEaten: idItemEaten}, 
            success: function (response) {
              if(response == "error")
              {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
              else {
                //var newFoodValue = response;
                //document.getElementById("food_bar_current").style.width = newFoodValue;
                location.reload();
              }
            },
            complete: function() {
              me.data('requestRunning', false);
          }
        });

      }
      return false;
    }, false);
  }
}
if(document.getElementsByClassName("item-cake")){

  var className = document.getElementsByClassName("item-cake");
  var n = className.length;

  for (var i = 0; i < n; i++) {
    className[i].addEventListener("contextmenu", function(ev) {
      ev/preventDefault();
      var conf = confirm("Manger pour regagner beaucoup d'énergie et de moral ?");
      if(conf){

        var idItemEaten = ev.target.id;

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {eat: "cake",
              idItemEaten: idItemEaten}, 
            success: function (response) {
              if(response == "error")
              {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
              else {
                //var newFoodValue = response;
                //document.getElementById("food_bar_current").style.width = newFoodValue;
                location.reload();
              }
            },
            complete: function() {
              me.data('requestRunning', false);
          }
        });
        return false;
      }
    }, false);
  }
}
if(document.getElementsByClassName("item-baked_orange")){

  var className = document.getElementsByClassName("item-baked_orange");
  var n = className.length;

  for (var i = 0; i < n; i++) {
    className[i].addEventListener("contextmenu", function(ev) {
      ev.preventDefault();
      var conf = confirm("Manger pour regagner beaucoup d'énergie ?");
      if(conf){

        var idItemEaten = ev.target.id;

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {eat: "baked_orange",
              idItemEaten: idItemEaten}, 
            success: function (response) {
              if(response == "error")
              {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
              else {
                //var newFoodValue = response;
                //document.getElementById("food_bar_current").style.width = newFoodValue;
                location.reload();
              }
            },
            complete: function() {
              me.data('requestRunning', false);
          }
        });
        return false;
      }
    }, false);
  }
}
if(document.getElementsByClassName("item-baked_honey")){

  var className = document.getElementsByClassName("item-baked_honey");
  var n = className.length;

  for (var i = 0; i < n; i++) {
    className[i].addEventListener("contextmenu", function(ev) {
      ev.preventDefault();
      var conf = confirm("Manger pour regagner beaucoup de moral ?");
      if(conf){

        var idItemEaten = ev.target.id;

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {eat: "baked_honey",
              idItemEaten: idItemEaten}, 
            success: function (response) {
              if(response == "error")
              {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
              else {
                //var newFoodValue = response;
                //document.getElementById("food_bar_current").style.width = newFoodValue;
                location.reload();
              }
            },
            complete: function() {
              me.data('requestRunning', false);
          }
        });
        return false;
      }
    }, false);
  }
}


/****************** */
/* COLLECT FROM MAP */
/****************** */

// Adds the corresponding resource to the player's inventory and removes it from the map.
// Adds a 6 seconds timer when the user clics the harvest btn before harvesting the resource. The timer is displayed on the btn after the clic.
// The 0th second counts as a whole second, so doing a 5 sec timer looks like 6 sec.
// The clic event is deleted meanwhile, so the user cannot multiclic. The page is reloaded afterwards so the event is set back on the harvest btn.

if(document.getElementById("harvest-tree")){
  document.getElementById("harvest-tree").addEventListener("click", function cutTree(event) {

    //countdown 6s
    var time = 5 * 1000;

    // Update the count down every 1 second, until clearInterval(x) is called.
    var x = setInterval(function() {

        //supprime l'évent pour empêcher un click pendant le script, mais le token étant encore dans la boucle, il exécute le code qui suit.
        document.getElementById("harvest-tree").removeEventListener("click", cutTree);
        
        var minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((time % (1000 * 60)) / 1000);

        //résout le pb d'affichage de 1 digit sous 10 sec.
        if (seconds < 10){
            document.getElementById("harvest-tree").innerHTML = "Auto " + minutes + ":0" + seconds;
        } else {
            document.getElementById("harvest-tree").innerHTML =  "Auto " + minutes + ":" + seconds;
        }

        // Send the harvest request and reload the page to reinitialize the timer
        if (time <= 0) {

          $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {harvest: 'tree'}, 
            success: function (response) {
              if(response == "error")
              {
                alert("Une erreur est survenue, merci de recharger la page.");
              }
              clearInterval(x);
              location.reload();
            }
          });

        }

        time -= 1000;

    }, 1000); 

  });
}

if(document.getElementById("harvest-tree1")){
  document.getElementById("harvest-tree1").addEventListener("click", function cutTree1(event) {
    
    //countdown 5s
    var time = 5 * 1000;

    // Update the count down every 1 second, until clearInterval(x) is called.
    var x = setInterval(function() {

        //supprime l'évent pour empêcher un click pendant le script, mais le token étant encore dans la boucle, il exécute le code qui suit.
        document.getElementById("harvest-tree1").removeEventListener("click", cutTree1);
        
        var minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((time % (1000 * 60)) / 1000);

        //résout le pb d'affichage de 1 digit sous 10 sec.
        if (seconds < 10){
            document.getElementById("harvest-tree1").innerHTML = "Auto " + minutes + ":0" + seconds;
        } else {
            document.getElementById("harvest-tree1").innerHTML =  "Auto " + minutes + ":" + seconds;
        }

        // Send the harvest request and reload the page to reinitialize the timer
        if (time <= 0) {

          $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {harvest: 'tree1'}, 
            success: function (response) {
              if(response == "error"){
                alert("Une erreur est survenue, merci de recharger la page."); //"Il n'y a plus rien à couper"
                clearInterval(x);
              }
              location.reload();
            },
            error : function(response){
              console.log(response);
            }
          });

        }

        time -= 1000;

    }, 1000); 

  });
}

/*
if(document.getElementById("harvest-rock")){
  document.getElementById("harvest-rock").addEventListener("click", function(event) {
    $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {harvest: rock}, 
        success: function (response) {
          location.reload();
        }
     });
  });
}
*/

if(document.getElementById("harvest-orange")){
  document.getElementById("harvest-orange").addEventListener("click", function harvestOrange(event) {
    
    //countdown 5s
    var time = 5 * 1000;

    // Update the count down every 1 second, until clearInterval(x) is called.
    var x = setInterval(function() {

        //supprime l'évent pour empêcher un click pendant le script, mais le token étant encore dans la boucle, il exécute le code qui suit.
        document.getElementById("harvest-orange").removeEventListener("click", harvestOrange);
        
        var minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((time % (1000 * 60)) / 1000);

        //résout le pb d'affichage de 1 digit sous 10 sec.
        if (seconds < 10){
            document.getElementById("harvest-orange").innerHTML = "Auto " + minutes + ":0" + seconds;
        } else {
            document.getElementById("harvest-orange").innerHTML =  "Auto " + minutes + ":" + seconds;
        }

        // Send the harvest request and reload the page to reinitialize the timer
        if (time <= 0) {

          $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {harvest: 'orange'}, 
            success: function (response) {
              if(response == "error"){
                alert("Une erreur est survenue, merci de recharger la page."); //"Il n'y a plus rien à couper"
                clearInterval(x);
              }
              location.reload();
            }
          });

        }

        time -= 1000;

    }, 1000); 

  });
}

if(document.getElementById("harvest-honey")){
  document.getElementById("harvest-honey").addEventListener("click", function harvestHoney(event) {
    
    //countdown 5s
    var time = 5 * 1000;

    // Update the count down every 1 second, until clearInterval(x) is called.
    var x = setInterval(function() {

        //supprime l'évent pour empêcher un click pendant le script, mais le token étant encore dans la boucle, il exécute le code qui suit.
        document.getElementById("harvest-honey").removeEventListener("click", harvestHoney);
        
        var minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((time % (1000 * 60)) / 1000);

        //résout le pb d'affichage de 1 digit sous 10 sec.
        if (seconds < 10){
            document.getElementById("harvest-honey").innerHTML = "Auto " + minutes + ":0" + seconds;
        } else {
            document.getElementById("harvest-honey").innerHTML =  "Auto " + minutes + ":" + seconds;
        }

        // Send the harvest request and reload the page to reinitialize the timer
        if (time <= 0) {

          $.ajax({
            type: "POST",
            url: "core/ajax/ajax_item.php",
            data: {harvest: 'honey'}, 
            success: function (response) {
              if(response == "error"){
                alert("Une erreur est survenue, merci de recharger la page."); //"Il n'y a plus rien à couper"
                clearInterval(x);
              }
              location.reload();
            }
          });

        }

        time -= 1000;

    }, 1000); 


  });
}

/****** */
/* FARM */
/****** */
if(document.getElementById("btn-plant-orange-farm")){
  document.getElementById("btn-plant-orange-farm").addEventListener("click", function(event) {
    
    //post the id of the 1st orange in invent, if exists
    if(document.getElementsByClassName('item-orange').length != 0){
      var className = document.getElementsByClassName('item-orange');
      var idItem = className[0].id;  

      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

      $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {plant: 'orange',
          idItem: idItem}, 
        success: function (response) {
          if(response == "error"){
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          location.reload();
        },
        complete: function() {
          me.data('requestRunning', false);
      }
     });
    } else {
      alert("Vous n'avez pas d'orange à planter !");
    } 
  });
}
if(document.getElementById("btn-plant-honey-farm")){
  document.getElementById("btn-plant-honey-farm").addEventListener("click", function(event) {

    //post the id of the 1st honey in invent, if exists
    if(document.getElementsByClassName('item-honey').length != 0){
      var className = document.getElementsByClassName('item-honey');
      var idItem = className[0].id;  

      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

      $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {plant: 'honey',
          idItem: idItem}, 
        success: function (response) {
          if(response == "error"){
            alert("Une erreur est survenue, merci de recharger la page.");
          }
          location.reload();
        },
        complete: function() {
          me.data('requestRunning', false);
      }
     });
    } else {
      alert("Vous n'avez pas d'orange à planter !");
    } 
  });
}
if(document.getElementById("btn-harvest-orange-farm")){
  document.getElementById("btn-harvest-orange-farm").addEventListener("click", function(event) {

    //get the id of the 1st oranger in farm, if exists
    if(document.getElementsByClassName('item-orange_tree').length != 0){
      var className = document.getElementsByClassName('item-orange_tree');
      var idItem = className[0].id;  

      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

      $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {harvest_farm: 'orange',
          idItem: idItem}, 
        success: function (response) {
          if(response == "error"){
            alert("Une erreur est survenue, merci de recharger la page.");
            //var nbrItemCreated = response;
            //display counter under button
          }
          location.reload();
        },
        complete: function() {
          me.data('requestRunning', false);
      }
     });
    } else {
      alert("Vous n'avez pas d'orange à récolter !");
    } 
  });
}
if(document.getElementById("btn-harvest-honey-farm")){
  document.getElementById("btn-harvest-honey-farm").addEventListener("click", function(event) {

    //get the id of the 1st Ruche à miel in farm, if exists
    if(document.getElementsByClassName('item-hive_honey').length != 0){
      var className = document.getElementsByClassName('item-hive_honey');
      var idItem = className[0].id; 
      
      var me = $(this);
      // Prevents multi clic
      if ( me.data('requestRunning') ) {
          return;
      }
      me.data('requestRunning', true);

      $.ajax({
        type: "POST",
        url: "core/ajax/ajax_item.php",
        data: {harvest_farm: 'honey',
          idItem: idItem}, 
        success: function (response) {
          if(response == "error"){
            //var nbrItemCreated = response;
            //display counter under button
          }
          location.reload();
        },
        complete: function() {
          me.data('requestRunning', false);
      }
     });
    } else {
      alert("Vous n'avez pas de miel à récolter !");
    } 
  });
}
