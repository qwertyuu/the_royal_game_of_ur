# The Royal Game of Ur - Multiplayer

Now fully playable at https://ur.raphaelcote.com !

This is a recreation of the 4,500 year old "Royal Game of Ur" in PHP, jQuery (Ajax) and CSS

Meant to be playable online by theorically as many people as a server can handle

# Installation for development

1. Using php 7.4 or 8, issue the command `composer install`
2. Copy the file `.env.example` to `.env` and change the corresponding values (db)
3. run `php artisan migrate`
4. Configure the `/public` directory with your web server of choice (nginx, apache) or `php -S localhost:8000 -t public`

## Optional: Neato Bot

If you want to have "Neato" bot working locally, you need to set up https://github.com/qwertyuu/go-ur (or any other API that supports the same payload/contract)

Once it is done, set the `UR_NEAT_BASEURL` to the appropriate base URL. The /infer endpoint is not needed here.

# Usage

First player creates a game by clicking "Nouvelle partie" and communicates its game ID to another player

Second player inputs the game ID by clicking "Rejoindre une partie" and presses "Go!"

First player hits "Refresh"

Play!

# TODO

- [x] Have a public dice that player roll with an action (click) instead of an automatic dice that is private like right now
- [x] Fix bug where game gets stuck if only one token is left for each player on the last square and no winning dice is thrown (might be fixed by point above)
- [x] use https://github.com/qwertyuu/go-ur to host one or many AI bots
- [ ] Make game beautiful
