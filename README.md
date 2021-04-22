# The Royal Game of Ur - Multiplayer

Now playable at https://ur.whnet.ca !

This is a recreation of the 4,500 year old "Royal Game of Ur" in PHP, jQuery (Ajax) and CSS

Meant to be playable online by theorically as many people as a server can handle

# Installation

1. Setup a database using ur.sql (it's a creation script, you should run it once on a mysql instance and voila!)
1. Create a `configs.php` file in the root that looks like this
```php
<?php
$bd = new PDO('mysql:host=localhost;dbname=DB_NAME;charset=utf8', 'USERNAME', 'PASSWORD');
```
Where `DB_NAME` is the database name, `USERNAME` is the database user's name and `PASSWORD` is the db user's password

# Usage

First player creates a game by clicking "Nouvelle partie" and communicates its game ID to another player

Second player inputs the game ID by clicking "Rejoindre une partie" and presses "Go!"

First player hits "Refresh"

Play!

# TODO

- [ ] Have a public dice that player roll with an action (click) instead of an automatic dice that is private like right now
- [ ] Fix bug where game gets stuck if only one token is left for each player on the last square and no winning dice is thrown (might be fixed by point above)
- [ ] Make game beautiful
