<?php

function l_de(){
    $count = 0;
    foreach(range(0,3) as $de){
        $valeur = rand(1, 2);
        if ($valeur == 1){
            $count++;
        }
    }
    return $count;
}

function generate_possible_moves($bd, $game_id, $player, $joueur, &$json_retour){
    $statement = $bd->prepare('SELECT jeton_id, jeton_position FROM joueur_jeton WHERE jeton_fk_game_id = :game_id AND jeton_joueur_position = :player');
    $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
    $statement->bindParam(':player', $player, PDO::PARAM_INT);
    $statement->execute();
    $result_jeton = $statement->fetchAll(PDO::FETCH_ASSOC);
    $json_retour['possible_moves'] = array();
    if($json_retour['de'] != 0){
        foreach($result_jeton as $jeton){
            $position_jeton = $jeton['jeton_position'];
            $jeton_courant = $jeton['jeton_id'];
            if($position_jeton == -1){
                $position_tir = $joueur->course[$json_retour['de']-1];
                if(!ya_tu_un_jeton_a($bd,$game_id, $position_tir)){
                    $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                }
            }
            elseif($position_jeton>-1){
                $position_jeton_course = array_search($position_jeton, $joueur->course);
                $position_final_tir = $position_jeton_course + ($json_retour['de']);
                if($position_final_tir == count($joueur->course)){
                    $json_retour['possible_moves'][-2] = $jeton_courant;
                }
                elseif($position_final_tir < count($joueur->course)){
                    $position_tir = $joueur->course[$position_final_tir];
                    if($position_tir == 10){
                        if(!ya_tu_un_jeton_a($bd,$game_id, $position_tir)){
                            $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                        }
                    }
                    elseif(!ya_tu_un_jeton_a($bd, $game_id, $position_tir, $player)){
                        $json_retour['possible_moves'][$position_tir] = $jeton_courant;
                    }
                }
            }
        }
    }
}

function ya_tu_un_jeton_a($bd, $game_id, $position, $player=null, $return = false){
    $querystring = 'SELECT jeton_id FROM joueur_jeton WHERE jeton_fk_game_id = :game_id AND jeton_position =:postition';
    if($player != null){
        $querystring .= ' AND jeton_joueur_position=:player';
    }
    $statement = $bd->prepare($querystring);
    $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
    $statement->bindParam(':postition', $position, PDO::PARAM_INT);
    if($player != null){
        $statement->bindParam(':player', $player, PDO::PARAM_INT);
    }
    $statement->execute();
    $result_jeton = $statement->fetchAll(PDO::FETCH_ASSOC);
    if(count($result_jeton) > 0 && $return){
        return $result_jeton[0];
    }
    return count($result_jeton) > 0;
}

class Jeu{
    function __construct(){
        $pos_rosettes = array(0, 2, 10, 14, 16);
        $this->planche = array();
        foreach(range(0, 19) as $index){
            $this->planche[] = new Cellule(in_array($index, $pos_rosettes));
        }
    }


}

class Cellule{
    function __construct($rosette){
        $this->est_rosette = $rosette;
    }
}

class Joueur{
    function __construct($position){
        $this->joueur_nb = $position;
        if($position == 1){
            $this->course = array(9,6,3,0,1,4,7,10,12,13,15,18,17,14);
        }
        else{
            $this->course = array(11,8,5,2,1,4,7,10,12,13,15,18,19,16);
        }
    }
}



?>