<!DOCTYPE html>
<html lang="fr-CA">
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="css/custom.css">
    <title>UR!</title>
</head>
<body>
<div class="menu_principal">
    <div id="new_game" data-toggle="collapse" data-target="#new_game_options">
        Nouvelle partie
    </div>
    <div id="new_game_options" class="collapse">
        <form method="GET">
            <label for="nb_jetons">Longueur de la partie: </label><select name="nb_jetons" id="nb_jetons">
                <option value="3">Courte</option>
                <option value="5">Moyenne</option>
                <option value="7">Longue</option>
            </select>
            <input type="hidden" value="new" name="action">
            <button>Go!</button>
        </form>
    </div>

    <div id="load_game" data-toggle="collapse" data-target="#load_game_options">
        Rejoindre une partie
    </div>
    <div id="load_game_options" class="collapse">
        <form method="GET">
            <label for="game_id_picker">Game ID: </label><input name="game_id" id="game_id_picker">
            <input type="hidden" value="join" name="action">
            <button>Go!</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
</body>
</html>
