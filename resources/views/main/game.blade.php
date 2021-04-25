<!DOCTYPE html>
<html lang="fr-CA">
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="css/custom.css">
    <title>UR!</title>
</head>
<body>
<input type="hidden" id="game_id" value="{{ $game_id }}">
<input type="hidden" id="player" value="{{ $joueur  }}">
<input type="hidden" id="last_move_id" value="0">
<div class="col-sm-5">
    @if ($joueur === 1)
        <div id="fin" data-position="-2">
            OUT
        </div>
    @endif
</div>
<div class="container-fluid col-sm-2" id="jeu">
    <div class="row_fluid">
        <div class="col-sm-4" data-position="0"></div>
        <div class="col-sm-4" data-position="1"></div>
        <div class="col-sm-4" data-position="2"></div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4" data-position="3"></div>
        <div class="col-sm-4" data-position="4"></div>
        <div class="col-sm-4" data-position="5"></div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4" data-position="6"></div>
        <div class="col-sm-4" data-position="7"></div>
        <div class="col-sm-4" data-position="8"></div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4" data-position="9"></div>
        <div class="col-sm-4" data-position="10"></div>
        <div class="col-sm-4" data-position="11"></div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4 fleche">@if ($joueur === 1)^@endif</div>
        <div class="col-sm-4" data-position="12"></div>
        <div class="col-sm-4 fleche">@if ($joueur === 2)^@endif</div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4"></div>
        <div class="col-sm-4" data-position="13"></div>
        <div class="col-sm-4"></div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4" data-position="14"></div>
        <div class="col-sm-4" data-position="15"></div>
        <div class="col-sm-4" data-position="16"></div>
    </div>
    <div class="row_fluid">
        <div class="col-sm-4" data-position="17"></div>
        <div class="col-sm-4" data-position="18"></div>
        <div class="col-sm-4" data-position="19"></div>
    </div>
</div>
<div class="col-sm-5">
    @if ($joueur === 2)
        <div id="fin" data-position="-2">
            OUT
        </div>
    @endif
</div>
<div style="clear:both;"></div>
<div class="count_1 player_{{ $joueur }}"></div>
<div class="count_2 player_{{ $joueur === 1 ? 2 : 1 }}"></div>
<div class="container-fluid col-sm-2">
    <div class="row_fluid">
        <a href="http://{{ $host . $uri_sans_get }}" class="btn btn-default">
            Quitter
        </a>
    </div>
</div>
<div id="your_turn"></div>
<script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
<script src="js/jeu.js"></script>
</body>
</html>
