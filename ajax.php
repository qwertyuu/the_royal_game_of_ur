<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!isset($_POST['action'])){
    exit();
}
$game_id = $_POST['game_id'];
$player = $_POST['player'];
$autre_player = $player == 1 ? 2 : 1;
require_once('function.php');
require_once('configs.php');
$joueur = new Joueur($player);
switch($_POST['action']){
    case 'refresh':
        $statement = $bd->prepare('SELECT en_attente, joueur_courant, en_creation, last_move_id FROM game WHERE game_id = :game_id');
        $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $json_retour = array();
        $json_retour['state'] = 'wait';
        if($result['en_creation']){
            $json_retour['en_cours'] = False;
        }
        else{
            if($_POST['last_move'] != $result['last_move_id']){
                $statement = $bd->prepare('SELECT move_id, move_fk_jeton_id, move_new_position, jeton_joueur_position FROM move LEFT JOIN joueur_jeton ON jeton_id = move_fk_jeton_id WHERE move_id > :last_move AND move_fk_game_id = :game_id ORDER BY move_id ASC');
                $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                $statement->bindParam(':last_move', $_POST['last_move'], PDO::PARAM_INT);
                $statement->execute();
                $result_moves = $statement->fetchAll(PDO::FETCH_ASSOC);
                foreach($result_moves as $move){
                    $json_retour['moves'][] = array(
                        'new_pos' => $move['move_new_position'],
                        'jeton_id' => $move['move_fk_jeton_id'],
                        'joueur' => $move['jeton_joueur_position']
                    );
                }
                $json_retour['last_move_id'] = $result['last_move_id'];

                $statement = $bd->prepare('SELECT SUM(jeton_position=-1) AS \'attente\', SUM(jeton_position=-2) AS \'out\', SUM(jeton_position>-1) AS \'en_jeu\', COUNT(jeton_id) AS \'total\' FROM joueur_jeton WHERE jeton_joueur_position = :player AND jeton_fk_game_id = :game_id');
                $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                $statement->bindParam(':player', $player, PDO::PARAM_INT);
                $statement->execute();
                $result_jetons = $statement->fetch(PDO::FETCH_ASSOC);
                $json_retour['count']['yours'] = $result_jetons;
                if($result_jetons['total'] == $result_jetons['out']){
                    session_start();
                    session_destroy();
                    $json_retour['gagnant'] = 'toi';
                }
                $statement = $bd->prepare('SELECT SUM(jeton_position=-1) AS \'attente\', SUM(jeton_position=-2) AS \'out\', SUM(jeton_position>-1) AS \'en_jeu\', COUNT(jeton_id) AS \'total\' FROM joueur_jeton WHERE jeton_joueur_position = :player AND jeton_fk_game_id = :game_id');
                $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                $statement->bindParam(':player', $autre_player, PDO::PARAM_INT);
                $statement->execute();
                $result_jetons = $statement->fetch(PDO::FETCH_ASSOC);
                if($result_jetons['total'] == $result_jetons['out']){
                    session_start();
                    session_destroy();
                    $json_retour['gagnant'] = 'pas toi';
                }
                $json_retour['count']['other'] = $result_jetons;
            }
            $json_retour['en_cours'] = True;
            $joueur_en_cours = $result['joueur_courant'];
            
            if($joueur_en_cours == $player){
                
                $json_retour['state'] = 'update';
                $json_retour['your_turn'] = True;
                //$json_retour['last_move_id'] = $_POST['last_move'];
                
                if($result['en_attente'] == 0){
                    $json_retour['de'] = l_de();
                    if($json_retour['de'] != 0){
                        $statement = $bd->prepare('UPDATE game SET en_attente = 1, last_de=:de WHERE game_id = :game_id');
                        $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                        $statement->bindParam(':de', $json_retour['de'], PDO::PARAM_INT);
                        $statement->execute();
                    }
                    else{
                        $statement = $bd->prepare('UPDATE game SET last_de=:de WHERE game_id = :game_id');
                        $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                        $statement->bindParam(':de', $json_retour['de'], PDO::PARAM_INT);
                        $statement->execute();
                    }
                }
                else{
                    $statement = $bd->prepare('SELECT last_de FROM game WHERE game_id = :game_id');
                    $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                    $statement->execute();
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                    $json_retour['de'] = $result['last_de'];
                }
                generate_possible_moves($bd, $game_id, $player, $joueur, $json_retour);
                if(count($json_retour['possible_moves']) == 0){
                    $autre_player = $player == 1 ? 2 : 1;
                    $statement = $bd->prepare('UPDATE game SET joueur_courant = :autre_player WHERE game_id = :game_id');
                    $statement->bindParam(':autre_player', $autre_player, PDO::PARAM_INT);
                    $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                    $statement->execute();
                    unset($json_retour['your_turn']);
                }
            }
        }
        header('Content-Type: application/json');
        echo json_encode($json_retour);
        break;

    case 'jouer':
        $jeu = new Jeu();
        $jeton_joue = $_POST['jeton_id'];
        $jeton_newpos = $_POST['new_pos'];
        //TODO: Ajouter une verif que le move est bon pour empêcher la triche
        $json_retour = array('state'=>'bad');
        
        $statement = $bd->prepare('SELECT en_attente, joueur_courant, en_creation FROM game WHERE game_id = :game_id');
        $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if($result['en_creation'] == 0 && $result['en_attente'] == 1 && $result['joueur_courant'] == $player){
            $statement = $bd->prepare('SELECT jeton_position FROM joueur_jeton WHERE jeton_id = :jeton_id');
            $statement->bindParam(':jeton_id', $jeton_joue, PDO::PARAM_INT);
            $statement->execute();
            $result_jeton = $statement->fetch(PDO::FETCH_ASSOC);
            $old_pos = $result_jeton['jeton_position'];
            
            $jeton_ennemi = ya_tu_un_jeton_a($bd, $game_id, $jeton_newpos, $autre_player, true);
            if($jeton_ennemi && $jeton_newpos >= 0){
                //lol umad
                $statement = $bd->prepare('INSERT INTO move (move_fk_jeton_id, move_fk_game_id, move_last_position, move_new_position, rosette) VALUES (:jeton_id, :game_id, :last_position, -1, 0)');
                $statement->bindParam(':jeton_id', $jeton_ennemi['jeton_id'], PDO::PARAM_INT);
                $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
                $statement->bindParam(':last_position', $jeton_newpos, PDO::PARAM_INT);
                $statement->execute();
                $lastId_move = $bd->lastInsertId();
                
                $statement = $bd->prepare('UPDATE joueur_jeton SET jeton_position=-1 WHERE jeton_id = :jeton_id');
                $statement->bindParam(':jeton_id', $jeton_ennemi['jeton_id'], PDO::PARAM_INT);
                $statement->execute();
            }

            $statement = $bd->prepare('UPDATE joueur_jeton SET jeton_position=:position WHERE jeton_id = :jeton_id');
            $statement->bindParam(':position', $jeton_newpos, PDO::PARAM_INT);
            $statement->bindParam(':jeton_id', $jeton_joue, PDO::PARAM_INT);
            $statement->execute();
            $rosette = 1;
            if(($jeton_newpos >= 0 && !$jeu->planche[$jeton_newpos]->est_rosette) || ($jeton_newpos == -2)){
                $rosette = 0;
            }
            $statement = $bd->prepare('INSERT INTO move (move_fk_jeton_id, move_fk_game_id, move_last_position, move_new_position, rosette) VALUES (:jeton_id, :game_id, :last_position, :new_position, :rosette)');
            $statement->bindParam(':jeton_id', $jeton_joue, PDO::PARAM_INT);
            $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
            $statement->bindParam(':last_position', $old_pos, PDO::PARAM_INT);
            $statement->bindParam(':new_position', $jeton_newpos, PDO::PARAM_INT);
            $statement->bindParam(':rosette', $rosette, PDO::PARAM_INT);
            $statement->execute();
            $lastId_move = $bd->lastInsertId();

            $statement = $bd->prepare('UPDATE game SET last_move_id=:move_id, joueur_courant = :player, en_attente=0 WHERE game_id = :game_id');
            $statement->bindParam(':move_id', $lastId_move, PDO::PARAM_INT);
            $statement->bindParam(':game_id', $game_id, PDO::PARAM_INT);
        	$p = $rosette == 0 ? $autre_player : $player;
            $statement->bindParam(':player', $p, PDO::PARAM_INT);
            $statement->execute();
            $json_retour['state'] = 'good';

        }
        header('Content-Type: application/json');
        echo json_encode($json_retour);
    break;


}

?>