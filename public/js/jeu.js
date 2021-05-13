$(function () {
    $(document).on("click", "div.jouable", jouer);
    $(document).on("click", "#roll_dice", roll_dice);
    document.addEventListener('keydown', (e) => {
        if (e.code === "Space") {
            $("#roll_dice").click();
        }
    });
    $(document).on({
        mouseenter: function () {
            var jeton_id = $(this).data('jeton_id');
            var jeton_vise = $('div[data-jeton="' + jeton_id + '"]');
            jeton_vise.addClass('pointe');
        },
        mouseleave: function () {
            $('.pointe').removeClass('pointe');
        }
    }, "div.jouable");
    refresh();
});

function refresh() {
    $.ajax({
        url: 'ajax',
        method: 'POST',
        data: {
            action: 'refresh',
            game_id: $('#game_id').val(),
            player: $('#player').val(),
            last_move: $('#last_move_id').val(),
        },
        dataType: 'json',
        success: function (data) {
            if (data.gagnant !== null) {
                $('#jeu').text(data.gagnant ? 'WIN :)' : 'perdu');
                return;
            }
            $('#last_move_id').val(data.last_move_id);
            if (data.moves.length > 0) {
                for (var move in data.moves) {
                    var jquery_jeton = $('div[data-jeton="' + data.moves[move].jeton_id + '"]');
                    if (data.moves[move].new_pos < 0) {
                        jquery_jeton.remove();
                    }
                    var pos = $('div[data-position="' + data.moves[move].new_pos + '"]').position();
                    if (pos) {
                        if (jquery_jeton.length) {
                            jquery_jeton.css('left', pos.left + 'px');
                            jquery_jeton.css('top', pos.top + 'px');
                        } else {
                            $('#jeu').append('<div data-jeton="' + data.moves[move].jeton_id + '" class="jeton player_' + data.moves[move].joueur + '" style="position:absolute;left:' + pos.left + 'px;top:' + pos.top + 'px"></div>');
                        }
                    }
                }
            }
            $('.count_1').html('VOUS:<br>En attente: ' + data.count.yours.attente + '<br>' + 'Sorti: ' + data.count.yours.out);
            $('.count_2').html('<br>ADVERSAIRE:<br>En attente: ' + data.count.other.attente + '<br>' + 'Sorti: ' + data.count.other.out);
            var dice_roll = '<div id="roll_dice" class="btn btn-default">Lancer le dé</div>';
            var indicator_html = '';
            if (data.your_turn) {
                if (data.turn_state === 'play') {
                    for (var move in data.possible_moves) {
                        if (data.possible_moves.hasOwnProperty(move)) {
                            $('div[data-position="' + move + '"]').addClass('jouable').data('jeton_id', data.possible_moves[move]);
                        }
                    }
                    indicator_html = data.dice + ' - ton tour';
                } else if (data.turn_state === 'dice') {
                    indicator_html = dice_roll;
                    console.log(indicator_html);
                }
            } else {
                indicator_html = data.dice === null ? 'En attente de l\'autre joueur' : data.dice;
                setTimeout(refresh, 500);
            }
            $('#your_turn').html(indicator_html);
        },
        error: function (a, b, c) {
            console.log('erreur refresh', a, b, c);
            setTimeout(refresh, 2000);
        }
    });
}

function jouer() {
    $('.pointe').removeClass('pointe');
    $('.jouable').removeClass('jouable');
    var clic = $(this);
    var jeton_id = clic.data('jeton_id');
    var new_pos = clic.data('position');
    $.ajax({
        url: 'ajax',
        method: 'POST',
        data: {
            action: 'play',
            game_id: $('#game_id').val(),
            player: $('#player').val(),
            jeton_id: jeton_id,
            new_pos: new_pos
        },
        dataType: 'json',
        success: function (data) {
            refresh();
        },
        error: function (a, b, c) {
            console.log('erreur jouer', a, b, c);
        }
    });
}

function roll_dice() {
    $('.pointe').removeClass('pointe');
    $('.jouable').removeClass('jouable');
    $.ajax({
        url: 'ajax',
        method: 'POST',
        data: {
            action: 'roll_dice',
            game_id: $('#game_id').val(),
            player: $('#player').val(),
        },
        dataType: 'json',
        success: function (data) {
            refresh();
        },
        error: function (a, b, c) {
            console.log('erreur jouer', a, b, c);
        }
    });
}
