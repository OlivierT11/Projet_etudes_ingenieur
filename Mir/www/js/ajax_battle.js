    // aller chercher les stats à modifier dans la DB (force monstre, stats joueurs, stat équipement)
    // Calculer en backend
    // Renvoyer les nouvelles données et les afficher


//TODO
//1. supprimer complétement la barre de vie s'il n'y a aucun monstre :
    //bug
    /*
    if($foeHp=='0'){
        document.getElementById("foe-life-bar-full").style.display='none';
        document.getElementById("foe-life-bar-current").style.display='none';
    }*/

//2. Empecher la perte de PV d'iln'y a aucun monstre.

    
if(document.getElementById("btn-battle")) {
    document.getElementById("btn-battle").addEventListener("click", function startBattle(event) {
        $foeHp = '';

        var me = $(this);
        // Prevents multi clic
        if ( me.data('requestRunning') ) {
            return;
        }
        me.data('requestRunning', true);

        // Envoie une peemière requête pour vérifier qu'il y a un monstre de disponible et lancer le combat.
        // Effectue un TIC et attend 5 secondes. Effectue ensuite des TICS en boucle jusqu'à mort du monstre ou du joueur.
        // Si le joueur fuit en cours de route (change de page ou actualise la page), alors le combat se termine au TIC en cours.
        $.ajax({
            type: "POST",
            url: "core/ajax/ajax_battle.php",
            data: {action: "fight"}, 
            success: function (response) {
                //console.log(response);

                    // S'il n'y a pas d'ennemis
                    if (response == "nofoe"){
                        clearInterval(x);
                        alert("Il n'y a pas d'Ombres ici !");
                        location.reload();
                    }

                    // S'il y a une erreur
                    else if (response == "error"){
                        clearInterval(x);
                        alert("Une erreur est survenue, merci de recharger la page.");
                        location.reload();
                    }
                    
                    // Si l'ennemi est mort
                    else if (response == 'endfight'){
                        clearInterval(x);
                        //document.getElementById("btn-battle").innerHTML = "Combattre"; //?
                        //document.getElementById("fight-new").innerHTML = "L'Ombre a péri"; 
                        location.reload(); //pour supprimer le bouton "combattre" s'il n'y a plus de monstre. Tester le combat en multijoueur.
                    }

                    // si on a plus de PV
                    else if (response == 'die'){
                        clearInterval(x);
                        location.replace("index.php?page=death"); //On ne peut pas actualiser ou changer de page dans l'ajax, car ajax crée un thread à côté de la page qui n'exécute pas le code de la page lui-même.
                    } 

                    // si tous les ennemis sont occupés
                    else if (response == 'allFight'){
                        clearInterval(x);
                        alert("Tous les ennemis sont occupés avec d'autres joueurs");
                    } 
                    
                    else {

                    //supprime l'évent pour empêcher un click pendant le script, mais le token étant encore dans la boucle, il exécute le code qui suit.
                    document.getElementById("btn-battle").removeEventListener("click", startBattle);

                    // Parse the JSON data
                    var battleDataTic = JSON.parse(response);
                    //console.log(battleDataTic);

                    var foeHp = battleDataTic[1];
                    var dmgDealt = battleDataTic[2];
                    var dmgReceived = battleDataTic[3];
                    var equipmentHit = battleDataTic[4];
                    var name = battleDataTic[5];
                    var date = battleDataTic[6];
                    var armorDur = battleDataTic[7];
                    var spearDur = battleDataTic[8];

                    // Display life bar once the fight has begun
                    document.getElementById("foe-life-bar-current").style.display = 'block';
                    document.getElementById("foe-life-bar-current").style.width = foeHp + '0%';
                    document.getElementById("foe-life-bar-full").style.display = 'inline-block';
                    document.getElementById("life").style.display = 'inline-block';

                    // Ajoute une Action avec les dégats infligés
                    var newActionAtk = document.createElement('LI');
                    newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Vous infligez <span style="font-weight:bold; color:green;">' + dmgDealt + '</span> dégâts à l\'Ombre.</p>';
                    // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                    var newActionDef = document.createElement('LI');
                    newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';
                    
                    var newWrapper = document.getElementById('actions-wrapper-for-map').children[0]; //ul
                    var firstNew = document.getElementById('actions-wrapper-for-map').children[0].children[1]; //div > ul > li. Skip title node [0].
                    newWrapper.insertBefore(newActionAtk, firstNew);
                    newWrapper.insertBefore(newActionDef, firstNew);

                    // Update the equipement hit HP in real time if there is an equipment.
                    switch(equipmentHit){
                        case 'Bouclier':
                            if(document.getElementById("equip-shield").firstElementChild){
                                document.getElementById("equip-shield").firstElementChild.firstElementChild.style.width = armorDur + '0%'; // Item HP bar
                                document.getElementById("equip-shield").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur; // Tooltip dur

                                // Update the life bar in real time
                                document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                            }
                            if(armorDur <= 0){
                                document.getElementById("equip-shield").innerHTML = '';

                                // Ajoute une Action avec la destruction de l'item
                                var newActionAtk = document.createElement('LI');
                                newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                var newActionDef = document.createElement('LI');
                                newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';
                    
                            }
                            break;
                        case 'Armure haute':
                            if(document.getElementById("equip-upper").firstElementChild){
                                document.getElementById("equip-upper").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                document.getElementById("equip-upper").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                // Update the life bar in real time
                                document.getElementById('upper_bar_current').style.width = armorDur +'0%';
                            }
                            if(armorDur <= 0){
                                document.getElementById("equip-upper").innerHTML = '';

                                // Ajoute une Action avec la destruction de l'item
                                var newActionAtk = document.createElement('LI');
                                newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                var newActionDef = document.createElement('LI');
                                newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';
                            }
                            break;
                        case 'Armure basse':
                            if(document.getElementById("equip-lower").firstElementChild){
                                document.getElementById("equip-lower").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                document.getElementById("equip-lower").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                // Update the life bar in real time
                                document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                            }
                            if(armorDur <= 0){
                                document.getElementById("equip-lower").innerHTML = '';

                                // Ajoute une Action avec la destruction de l'item
                                var newActionAtk = document.createElement('LI');
                                newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                var newActionDef = document.createElement('LI');
                                newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';

                            }
                            break;
                        case 'Casque':
                            if(document.getElementById("equip-helmet").firstElementChild){
                                document.getElementById("equip-helmet").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                document.getElementById("equip-helmet").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                // Update the life bar in real time
                                document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                            }
                            if(armorDur <= 0){
                                document.getElementById("equip-helmet").innerHTML = '';

                                // Ajoute une Action avec la destruction de l'item
                                var newActionAtk = document.createElement('LI');
                                newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                var newActionDef = document.createElement('LI');
                                newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';

                            }
                            break;
                        case 'Masque':
                            if(document.getElementById("equip-mask").firstElementChild){
                                document.getElementById("equip-mask").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                document.getElementById("equip-mask").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                // Update the life bar in real time
                                document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                            }
                            if(armorDur <= 0){
                                document.getElementById("equip-mask").innerHTML = '';

                                // Ajoute une Action avec la destruction de l'item
                                var newActionAtk = document.createElement('LI');
                                newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                var newActionDef = document.createElement('LI');
                                newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';

                            }
                            break;
                    }

                    // Update the spear HP if there is a spear equiped.
                    if(document.getElementById("equip-spear").firstElementChild){
                        document.getElementById("equip-spear").firstElementChild.firstElementChild.style.width = spearDur + '0%';
                        document.getElementById("equip-spear").lastElementChild.lastElementChild.lastElementChild.innerHTML = spearDur;

                        // Update the life bar in real time
                        document.getElementById('shield_bar_current').style.width = spearDur +'0%';
                    }
                    if(spearDur <= 0){
                        document.getElementById("equip-spear").innerHTML = '';

                        // Ajoute une Action avec la destruction de l'item
                        var newActionAtk = document.createElement('LI');
                        newActionAtk.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                        // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                        var newActionDef = document.createElement('LI');
                        newActionDef.innerHTML = '<p>' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';

                    }

                    //countdown 5s before attacking again.
                    var time = 5 * 1000; //commence à 0:04

                    // Update the count down every 1 second
                    var x = setInterval(function() {
                        
                        //CODE UNIVERSEL POUR OBTENIR jours / heures / minutes / secondes à partir de date();
                        //var days = Math.floor(distance / (1000 * 60 * 60 * 24));
                        //var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                        var minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
                        var seconds = Math.floor((time % (1000 * 60)) / 1000);

                        //résout le pb d'affichage de 1 digit sous 10 sec.
                        if (seconds < 10){
                            document.getElementById("btn-battle").innerHTML = "Auto " + minutes + ":0" + seconds;
                        } else {
                            document.getElementById("btn-battle").innerHTML =  "Auto " + minutes + ":" + seconds;
                        }

                        time -= 1000;

                        if (time <= 0) {
                            time = 5 * 1000;
                            $.ajax({
                                type: "POST",
                                url: "core/ajax/ajax_battle.php",
                                data: {action: "fight"}, 
                                success: function (response) {
                                    //console.log(response);
                                    /*
                                    if (response == "nofoe"){
                                        alert("Il n'y a pas d'ennemis !");
                                        clearInterval(x);
                                        document.getElementById("btn-battle").innerHTML = "Combattre"; 
                                        ///document.getElementById("btn-battle").addEventListener("click", startBattle);
                                        ///OU reload
                                    }*/
                                    // Si l'ennemi est mort
                                    if (response == 'endfight'){
                                        clearInterval(x);
                                        //document.getElementById("btn-battle").innerHTML = "Combattre"; //?
                                        //document.getElementById("fight-new").innerHTML = "L'Ombre a péri"; 
                                        location.reload(); //pour supprimer le bouton "combattre" s'il n'y a plus de monstre. Tester le combat en multijoueur.
                                    }
                                    // S'il y a une erreur
                                    else if (response == "error"){
                                        clearInterval(x);
                                        alert("Une erreur est survenue, merci de recharger la page.");
                                        location.reload();
                                    }
                                    // si on a plus de PV
                                    else if (response == 'die'){
                                        clearInterval(x);
                                        location.replace("index.php?page=death");
                                    } 

                                    // si tous les ennemis sont occupés (normalement impossible ici, car on garde posFoeInString en session)
                                    else if (response == 'allFight'){
                                        clearInterval(x);
                                        alert("Tous les ennemis sont occupés avec d'autres joueurs");
                                    } 
                                    
                                    // Si l'ennemi n'es pas encore mort
                                    else {

                                        // Parse the JSON data
                                        var battleDataTic = JSON.parse(response);
                                        //console.log(battleDataTic);

                                        var foeHp = battleDataTic[1];
                                        var dmgDealt = battleDataTic[2];
                                        var dmgReceived = battleDataTic[3];
                                        var equipmentHit = battleDataTic[4];
                                        var name = battleDataTic[5];
                                        var date = battleDataTic[6];
                                        var armorDur = battleDataTic[7];
                                        var spearDur = battleDataTic[8];

                                        document.getElementById("foe-life-bar-current").style.width = foeHp + '0%';

                                        // Ajoute une Action avec les dégats infligés
                                        var newActionAtk = document.createElement('LI');
                                        newActionAtk.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Vous infligez <span style="font-weight:bold; color:green;">' + dmgDealt + '</span> dégâts.</p>';
                                        // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                        var newActionDef = document.createElement('LI');
                                        newActionDef.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';
                                        
                                        var newWrapper = document.getElementById('actions-wrapper-for-map').children[0]; //ul
                                        var firstNew = document.getElementById('actions-wrapper-for-map').children[0].children[1]; //div > ul > li. Skip title node [0].
                                        newWrapper.insertBefore(newActionAtk, firstNew);
                                        newWrapper.insertBefore(newActionDef, firstNew);

                                        // Update the equipement hit HP in real time if there is an equipment.
                                        switch(equipmentHit){
                                            case 'Bouclier':
                                                if(document.getElementById("equip-shield").firstElementChild){
                                                    document.getElementById("equip-shield").firstElementChild.firstElementChild.style.width = armorDur + '0%'; // Item HP bar
                                                    document.getElementById("equip-shield").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur; // Tooltip dur

                                                    // Update the life bar in real time
                                                    document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                                                }
                                                if(armorDur <= 0)
                                                {
                                                    document.getElementById("equip-shield").innerHTML = '';

                                                    // Ajoute une Action avec la destruction de l'item
                                                    var actionArmorDestroyed = document.createElement('LI');
                                                    actionArmorDestroyed.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                                 
                                                }
                                                break;
                                            case 'Armure haute':
                                                if(document.getElementById("equip-upper").firstElementChild){
                                                    document.getElementById("equip-upper").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                                    document.getElementById("equip-upper").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                                    // Update the life bar in real time
                                                    document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                                                }
                                                if(armorDur <= 0)
                                                {
                                                    document.getElementById("equip-upper").innerHTML = '';

                                                    // Ajoute une Action avec la destruction de l'item
                                                    var actionArmorDestroyed = document.createElement('LI');
                                                    actionArmorDestroyed.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                                  
                                                }
                                                break;
                                            case 'Armure basse':
                                                if(document.getElementById("equip-lower").firstElementChild){
                                                    document.getElementById("equip-lower").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                                    document.getElementById("equip-lower").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                                    // Update the life bar in real time
                                                    document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                                                }
                                                if(armorDur <= 0)
                                                {
                                                    document.getElementById("equip-lower").innerHTML = '';

                                                    // Ajoute une Action avec la destruction de l'item
                                                    var actionArmorDestroyed = document.createElement('LI');
                                                    actionArmorDestroyed.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                                   
                                                }
                                                break;
                                            case 'Casque':
                                                if(document.getElementById("equip-helmet").firstElementChild){
                                                    document.getElementById("equip-helmet").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                                    document.getElementById("equip-helmet").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                                    // Update the life bar in real time
                                                    document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                                                }
                                                if(armorDur <= 0)
                                                {
                                                    document.getElementById("equip-helmet").innerHTML = '';

                                                    // Ajoute une Action avec la destruction de l'item
                                                    var actionArmorDestroyed = document.createElement('LI');
                                                    actionArmorDestroyed.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                                }
                                                break;
                                            case 'Masque':
                                                if(document.getElementById("equip-mask").firstElementChild){
                                                    document.getElementById("equip-mask").firstElementChild.firstElementChild.style.width = armorDur + '0%';
                                                    document.getElementById("equip-mask").lastElementChild.lastElementChild.lastElementChild.innerHTML = armorDur;

                                                    // Update the life bar in real time
                                                    document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                                                }

                                                // If armor destroyed
                                                if(armorDur <= 0)
                                                {
                                                    document.getElementById("equip-mask").innerHTML = '';

                                                    // Ajoute une Action avec la destruction de l'item
                                                    var actionArmorDestroyed = document.createElement('LI');
                                                    actionArmorDestroyed.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">' + equipmentHit + '</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';
                                                }
                                                break;
                                        }

                                        // Ajoute une Action avec les dégats recçus et la pièce d'équipement attaquée
                                        var actionArmorHit = document.createElement('LI');
                                        actionArmorHit.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <strong>' + equipmentHit + '</strong> subit <span style="font-weight:bold; color:red;">' + dmgReceived + '</span> dégâts.</p>';
                                        newWrapper.appendChild(actionArmorHit);
                                        
                                        // If a piece of equipment has been destroyed
                                        if (typeof(actionArmorDestroyed) !== 'undefined')
                                        {
                                            newWrapper.appendChild(actionArmorDestroyed);
                                        }

                                        // Update the spear HP if there is a spear equiped.
                                        if(document.getElementById("equip-spear").firstElementChild){
                                            document.getElementById("equip-spear").firstElementChild.firstElementChild.style.width = spearDur + '0%';
                                            document.getElementById("equip-spear").lastElementChild.lastElementChild.lastElementChild.innerHTML = spearDur;

                                            // Update the life bar in real time
                                            document.getElementById('shield_bar_current').style.width = armorDur +'0%';
                                        }

                                        // Ajoute une Action avec les dégats recçus
                                        var spearHit = document.createElement('LI');
                                        spearHit.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <strong>Lance</strong> subit des dégâts.</p>';
                                    
                                        newWrapper.appendChild(spearHit);

                                        // If spear destroyed
                                        if(spearDur <= 0)
                                        {
                                            document.getElementById("equip-spear").innerHTML = '';

                                            // Ajoute une Action avec la destruction de l'item
                                            var spearDestroyed = document.createElement('LI');
                                            spearDestroyed.innerHTML = '<p class="added-action-ajax">' + date + ' | ' + name + ' | Votre <span style="font-weight:bold; color:green;">Lance</span> est <span style="font-weight:bold; color:green;">détruit(e)</span>.</p>';

                                            newWrapper.appendChild(spearDestroyed);
                                        }
                                    }
                                }
                            });
                        }

                    }, 1000); //ATTENTION si ajax met plus d'1 sec à répondre, setInterval fait une nouvelle itération. On peut passer à 5 sec si bug. C'est le script loading time et non le DOMLoadingTime
                }
            },
            complete: function() {
                me.data('requestRunning', false);
            }
        });
        
    });
}
   