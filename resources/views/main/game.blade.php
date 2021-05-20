<!DOCTYPE html>
<html lang="fr-CA">
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
          integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <link rel="stylesheet" href="css/custom.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UR!</title>
</head>
<body class="player_{{$joueur}}">
    <input type="hidden" id="game_id" value="{{ $game_id }}">
    <input type="hidden" id="player" value="{{ $joueur  }}">
    <input type="hidden" id="last_move_id" value="0">
    <div class="game">
        <div class="player-ui align-center @if ($joueur === 1) current-player @else other-player @endif">
            @if ($joueur === 1)
                <div id="your_turn"></div>
            @endif
        </div>
        <div class="player-ui align-top content">
            <div id="jeu">
                <div class="game-padding">
                    <div class="game-cell-row">
                        <div class="game-cell" data-position="0"></div>
                        <div class="game-cell" data-position="1"></div>
                        <div class="game-cell" data-position="2"></div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell" data-position="3"></div>
                        <div class="game-cell" data-position="4"></div>
                        <div class="game-cell" data-position="5"></div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell" data-position="6"></div>
                        <div class="game-cell" data-position="7"></div>
                        <div class="game-cell" data-position="8"></div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell" data-position="9"></div>
                        <div class="game-cell" data-position="10"></div>
                        <div class="game-cell" data-position="11"></div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell fleche">@if ($joueur === 1 && false)^@endif</div>
                        <div class="game-cell" data-position="12"></div>
                        <div class="game-cell fleche">@if ($joueur === 2 && false)^@endif</div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell out" @if ($joueur === 1)id="fin" data-position="-2"@endif>@if ($joueur === 1)<@endif</div>
                        <div class="game-cell" data-position="13"></div>
                        <div class="game-cell out" @if ($joueur === 2)id="fin" data-position="-2"@endif>@if ($joueur === 2)>@endif</div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell" data-position="14"></div>
                        <div class="game-cell" data-position="15"></div>
                        <div class="game-cell" data-position="16"></div>
                    </div>
                    <div class="game-cell-row">
                        <div class="game-cell" data-position="17"></div>
                        <div class="game-cell" data-position="18"></div>
                        <div class="game-cell" data-position="19"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="player-ui align-center @if ($joueur === 2) current-player @else other-player @endif">
            @if ($joueur === 2)
                <div id="your_turn"></div>
            @endif
        </div>
    </div>
    <div class="count_1"></div>
    <div class="count_2"></div>
    <div class="container-fluid col-sm-2">
        <div class="row_fluid">
            <a href="http://{{ $host . $uri_sans_get }}" class="btn btn-default">
                Quitter
            </a>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
            integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
            crossorigin="anonymous"></script>
    <script src="js/jeu.js"></script>
</body>
</html>
