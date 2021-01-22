# The Royal Game of Ur - Multiplayer
This is a recreation of the 4,500 year old "Royal Game of Ur" in PHP, jQuery (Ajax) and CSS

Meant to be playable online by theorically as many people as a server can handle

# Installation

1. Setup a database using ur.sql (it's a creation script, you should run it once on a mysql instance and voila!)
1. Create a `configs.php` file in the root that looks like this
```
<?php
$bd = new PDO('mysql:host=localhost;dbname=DB_NAME;charset=utf8', 'USERNAME', 'PASSWORD');
```
Where `DB_NAME` is the database name, `USERNAME` is the database user's name and `PASSWORD` is the db user's password