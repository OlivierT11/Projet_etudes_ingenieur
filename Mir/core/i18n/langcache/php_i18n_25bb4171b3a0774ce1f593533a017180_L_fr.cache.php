<?php class L {
const greeting = 'Hello World!';
const test = 'Test en fr';
const you_are_outside = 'Vous êtes dehors';
const wood = 'Bois';
const wood_rare = 'Bois précieux';
const orange = 'Orange';
const honey = 'Miel';
const baked_orange = 'Tarte aux fruits';
const baked_honey = 'Miel raffiné';
const cake = 'Gâteau';
const upper = 'Armure haute';
const lower = 'Armure basse';
const helmet = 'Casque';
const mask = 'Masque';
const shield = 'Bouclier';
const spear = 'Lance';
const metal = 'Métal';
const orange_dirt = 'Fruit planté';
const orange_bush = 'Buisson';
const orange_tree = 'Buisson avec fruits';
const hive_dirt = 'Colonie d\abeilles
hive = Ruche
hive_honey = Ruche à miel';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}