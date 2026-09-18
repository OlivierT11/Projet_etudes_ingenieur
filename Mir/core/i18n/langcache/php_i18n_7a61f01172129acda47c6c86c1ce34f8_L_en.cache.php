<?php class L {
const greeting = 'Hello World!';
const test = 'test in english';
const you_are_outside = 'You are outside';
const wood = 'Wood';
const wood_rare = 'Precious wood';
const orange = 'Orange';
const honey = 'Honey';
const baked_orange = 'Fruit pie';
const baked_honey = 'Reffined honey';
const cake = 'Cake';
const upper = 'Upper armor';
const lower = 'Lower armor';
const helmet = 'Helmet';
const mask = 'Mask';
const shield = 'Shield';
const spear = 'Spear';
const metal = 'Metal';
const orange_dirt = 'Planted fruit';
const orange_bush = 'Bush';
const orange_tree = 'Bush with fruits';
const hive_dirt = 'Bee colony';
const hive = 'Bee hive';
const hive_honey = 'Honey hive';
const category_somethingother = 'Something other...';
public static function __callStatic($string, $args) {
    return vsprintf(constant("self::" . $string), $args);
}
}
function L($string, $args=NULL) {
    $return = constant("L::".$string);
    return $args ? vsprintf($return,$args) : $return;
}