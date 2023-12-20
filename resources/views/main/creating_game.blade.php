<!DOCTYPE html>
<html lang="fr-CA" style="height: 100%">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="css/custom.css">
    <title>UR!</title>
</head>
<body class="creating_game">
<div class="d-flex justify-content-center" style="height: 100%">
    <div class="d-flex">
        <div class="game_created flex-row share_game">
            <div class="flex-column">
                <div class="p-2">
                    <label for="game-share">Partage ce numéro de partie à ton ami!<input type="text" id="game-share" class="form-control" onclick="this.select();" value="{{ $game_id }}" readonly=""></label>
                </div>
            </div>
            <div class="flex-column">
                <div class="p-2">
                    <div>
                        <a href="//{{ $host . $uri_sans_get }}?action=refresh" class="btn btn-primary">
                            Refresh
                        </a>
                        <a href="//{{ $host . $uri_sans_get }}" class="btn btn-default">
                            Retour
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex">
        <div class="game_created flex-row bot_game">
            <form method="GET" class="form-group" action="//{{ $host . $uri_sans_get }}">
                <input type="hidden" name="action" value="bot_game">
                <div class="flex-column">
                    <div class="p-2">
                        <label for="game-share">Choisis ton adversaire robot
                            <select name="bot_behavior" id="nb_jetons" class="form-control">
                                <option value="neato">Neato</option>
                                <option value="lut">LUT</option>
                                <!--<option value="expecto">Expecto</option>-->
                                <option value="alas">Alas</option>
                                <option value="fire">Fire</option>
                                <option value="tunehr">Tunehr</option>
                            </select>
                        </label>
                    </div>
                </div>
                <div class="flex-column">
                    <div class="p-2">
                        <button type="submit" class="btn btn-primary">Contre un robot</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</body>
</html>
