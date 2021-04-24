$(function(){
    $( document ).on( "click", "div.jouable", jouer);
    $( document ).on({
        mouseenter: function () {
            var jeton_id = $(this).data('jeton_id');
            var jeton_vise = $('div[data-jeton="'+ jeton_id +'"]');
            jeton_vise.addClass('pointe');
        },
        mouseleave: function () {
            $('.pointe').removeClass('pointe');

        }
    },"div.jouable");
    refresh();
});

function refresh(){
    $.ajax({
        url: 'ajax',
        method:'POST',
        data: {action:'refresh',game_id: $('#game_id').val(), player:$('#player').val(), last_move:$('#last_move_id').val()},
        dataType:'json',
        success:function(data){
            console.log(data);
            if(data.hasOwnProperty('gagnant')){
                if(data.gagnant == 'toi')
                    $('#jeu').html('WIN :)');
                else
                    $('#jeu').html('perdu');
                return;
            }
            if(data.hasOwnProperty('last_move_id')){
                $('#last_move_id').val(data.last_move_id);
            }
            if(data.hasOwnProperty('moves') && data.moves.length > 0){
                console.log('!!!!');
                for(var move in data.moves){
                    if(data.moves.hasOwnProperty(move)){
                        //TODO: Ajouter un out
                        var jquery_jeton = $('div[data-jeton="'+ data.moves[move].jeton_id +'"]');
                        if(data.moves[move].new_pos < 0){
                            jquery_jeton.remove();
                        }
                        var pos = $('div[data-position="' + data.moves[move].new_pos + '"]').position();
                        if(pos){
                            if(jquery_jeton.length){
                                jquery_jeton.css('left', pos.left + 'px');
                                jquery_jeton.css('top', pos.top + 'px');
                            }
                            else{
                                $('#jeu').append('<div data-jeton="'+data.moves[move].jeton_id+'" class="jeton player_'+ data.moves[move].joueur +'" style="position:absolute;left:'+ pos.left +'px;top:'+ pos.top +'px"></div>');
                            }
                        }
                    }
                }
            }
            if(data.hasOwnProperty('count')){
                $('.count_1').empty();
                $('.count_1').append('VOUS:<br>En attente: ' + data.count.yours.attente + '<br>' + 'Sorti: ' + data.count.yours.out);
                $('.count_2').empty();
                $('.count_2').append('<br>ADVERSAIRE:<br>En attente: ' + data.count.other.attente + '<br>' + 'Sorti: ' + data.count.other.out);
            }
            if(data.hasOwnProperty('de')){
                $('#your_turn').remove();
                $('body').append('<div id="your_turn">' + data.de + '</div>');

            }
            if(data.hasOwnProperty('your_turn')){
                for(var move in data.possible_moves){
                    if(data.possible_moves.hasOwnProperty(move)){
                        $('div[data-position="'+ move +'"]').addClass('jouable').data('jeton_id', data.possible_moves[move]);
                    }
                }
            }
            else{
                setTimeout(refresh, 500);
            }
        },
        error:function(a,b,c){
            console.log('erreur refresh', a,b,c);
            setTimeout(refresh, 2000);
        }
    });
}

function jouer(){
    $('.pointe').removeClass('pointe');
    $('.jouable').removeClass('jouable');
    $('#your_turn').remove();
    var clic = $(this);
    var jeton_id = clic.data('jeton_id');
    var new_pos = clic.data('position');
    $.ajax({
        url: 'ajax',
        method:'POST',
        data: {action:'jouer',game_id: $('#game_id').val(), player:$('#player').val(), jeton_id:jeton_id, new_pos:new_pos},
        dataType:'json',
        success:function(data){
            refresh();
        },
        error:function(a,b,c){
            console.log('erreur jouer', a,b,c);
        }
    });
}
