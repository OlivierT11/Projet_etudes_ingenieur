NOTE SUR LES STATISTIQUES (LVL)

Faire un fichier stat.php nuirait aux performances, car certaines requêtes effectuées au chargement des pages devraient être refaites en ajax.

Les augmentations de lvl sont donc réparties dans les fichiers.

Pour pallier au manque de lisibilité que cela entraine, voici une liste exhaustive des emplacmeents des fonctions d'augmentation des lvl, pour toutes les statistiques : 

Collecte de bois (id 1)
Fonction : updatePlayerWoodCuttingStat() : ajax_item.php
Appel : event listener : ajax_item.js

ATK (id 2)
Fonction : updatePlayerAtkStat() : ajax_battle.php
Appel : ajax_item.php

DEF (id 3)
Fonction : updatePlayerDefStat() : ajax_battle.php
Appel : ajax_item.php

Exploration (id 4)
Fonction : updatePlayerExploStat() : ajax_outside.php
Appel : discoverArea() : ajax_outside.php

