<!DOCTYPE html>
<html lang="fr-CA" style="height: 100%">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" href="css/custom.css">
    <title>UR!</title>
</head>
<body class="creating_game">
<div class="d-flex justify-content-center" style="height: 100%">
    <div class="d-flex">
        <div class="game_created flex-row">
            <div class="flex-column">
                <div class="p-2">
                    <label for="game-share">Envoyer ce lien à votre ami<input type="text" id="game-share" class="form-control" onClick="this.select();" value="{{ $host . $uri_sans_get }}?game_id={{ $game_id }}&action=join"></label>
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
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</body>
</html>
