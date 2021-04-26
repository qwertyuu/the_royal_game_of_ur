# The Royal Game of Ur - Multiplayer

Now fully playable at https://ur.whnet.ca !

This is a recreation of the 4,500 year old "Royal Game of Ur" in PHP, jQuery (Ajax) and CSS

Meant to be playable online by theorically as many people as a server can handle

# Installation for development

1. Using php 7.4 or 8, issue the command `composer install`
1. Copy the file `.env.example` to `.env` and change the corresponding values (db)
1. run `php artisan migrate`
1. Configure the `/public` directory with your web server of choice (nginx, apache)

# Usage

First player creates a game by clicking "Nouvelle partie" and communicates its game ID to another player

Second player inputs the game ID by clicking "Rejoindre une partie" and presses "Go!"

First player hits "Refresh"

Play!

# TODO

- [x] Have a public dice that player roll with an action (click) instead of an automatic dice that is private like right now
- [x] Fix bug where game gets stuck if only one token is left for each player on the last square and no winning dice is thrown (might be fixed by point above)
- [ ] Make game beautiful
