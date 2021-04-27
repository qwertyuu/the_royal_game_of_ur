<!DOCTYPE html>
<html lang="fr-CA" style="height: 100%">
<head>
    <link rel="stylesheet" href="css/custom.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <title>UR!</title>
</head>
<body style="height: 100%">
<div class="d-flex justify-content-center" style="height: 100%">
    <div class="flex-row d-flex align-items-center ">
        <div class="d-flex flex-column justify-content-center" style="padding: 20px">
            <form method="GET">
                <label for="nb_jetons">Nouvelle partie</label><br><select name="nb_jetons" id="nb_jetons">
                    <option value="3">Courte</option>
                    <option value="5">Moyenne</option>
                    <option value="7">Longue</option>
                </select>
                <input type="hidden" value="new" name="action">
                <button>Go!</button>
            </form>
        </div>
        <div class="d-flex  justify-content-center" style="padding: 20px">
            <form method="GET">
                <label for="game_id_picker">Rejoindre une partie</label><br><input name="game_id" id="game_id_picker">
                <input type="hidden" value="join" name="action">
                <button>Go!</button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js" integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf" crossorigin="anonymous"></script>
</body>
</html>
