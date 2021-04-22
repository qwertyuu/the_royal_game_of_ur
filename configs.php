<?php

$bd = new PDO('mysql:host=localhost;dbname=ur;charset=utf8', 'ur', 'qwerty');
$bd->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
$bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

?>