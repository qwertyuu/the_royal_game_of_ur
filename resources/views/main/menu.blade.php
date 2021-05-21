<!DOCTYPE html>
<html lang="fr-CA" style="height: 100%">
<head>
    <link rel="stylesheet" href="css/custom.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <title>UR!</title>
</head>
<body style="height: 100%" class="menu">
<div style="height: 100%">
    <div class="title">Le jeu royal d'Ur</div>
    <div class="d-flex justify-content-center menu_new_game">
        <div class="flex-row d-flex align-items-center ">
            <div class="d-flex flex-column justify-content-center create_game">
                <form method="GET" class="form-group">
                    <label for="nb_jetons">Nouvelle partie
                        <select name="nb_jetons" id="nb_jetons" class="form-control">
                            <option value="3">Courte</option>
                            <option value="5">Moyenne</option>
                            <option value="7">Longue</option>
                        </select>
                    </label>
                    <input type="hidden" value="new" name="action">
                    <button class="btn btn-primary">Go!</button>
                    <a href="https://www.youtube.com/watch?v=Y9Tz_wvDKRI" style="display: block" target="_blank">Comment jouer?</a>
                </form>
            </div>
            <div class="d-flex justify-content-center join_game">
                <form method="GET" class="form-group">
                    <label for="game_id_picker">Rejoindre une partie<input name="game_id" id="game_id_picker" class="form-control"></label>
                    <input type="hidden" value="join" name="action">
                    <button class="btn btn-primary">Go!</button>
                    <span style="color: white; display: block">a</span>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</body>
</html>
