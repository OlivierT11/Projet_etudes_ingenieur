<?php

$database = new Database();
$log = new Log();

require dirname(dirname(__FILE__))."/class/class.forum.php";
$forum = new Forum($database, $log);
