<?php
session_start();
require_once('function.php');
require_once('configs.php');
if(isset($_GET['reset'])){
    session_destroy();
}
if(isset($_GET['game_id']) && !isset($_SESSION['en_creation'])){
    //game existe
    $statement = $bd->prepare('SELECT game_id FROM game WHERE game_id = :game_id');
    $statement->bindParam(':game_id', $_GET['game_id'], PDO::PARAM_INT);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if($result){
        $statement = $bd->prepare('UPDATE game SET en_creation=0,joueur_courant=1,en_attente=0 WHERE game_id = :game_id');
        $statement->bindParam(':game_id', $_GET['game_id'], PDO::PARAM_INT);
        $statement->execute();

        $_SESSION['game_id'] = $_GET['game_id'];
        $_SESSION['joueur'] = 2;
        $nb_jetons_partie = 5;
        $values_insert = array();
        foreach(range(0, $nb_jetons_partie - 1) as $jeton_index){
            $values_insert[] = "(:game_id, 1, -1)";
            $values_insert[] = "(:game_id, 2, -1)";
        }
        $values_implode = implode(',', $values_insert);
        $statement = $bd->prepare('INSERT INTO joueur_jeton (jeton_fk_game_id, jeton_joueur_position, jeton_position) VALUES ' . $values_implode);
        $statement->bindParam(':game_id', $_GET['game_id'], PDO::PARAM_INT);
        $statement->execute();
        $_SESSION['en_creation'] = False;

    }
    else{
        unset($_SESSION['game_id']);
        unset($_SESSION['joueur']);
        unset($_SESSION['en_creation']);
        
    }
}
elseif(empty($_SESSION['joueur'])){
    //nouvelle game
    $statement = $bd->prepare('INSERT INTO game (en_creation) VALUES (1)');
    $statement->execute();
    $lastId = $bd->lastInsertId();
    $_SESSION['game_id'] = $lastId;
    $_SESSION['en_creation'] = True;
    $_SESSION['joueur'] = 1;

}
elseif($_SESSION['en_creation'] === True){
    //on va voir si notre game a été créée
    $statement = $bd->prepare('SELECT en_creation FROM game WHERE game_id = :game_id');
    $statement->bindParam(':game_id', $_SESSION['game_id'], PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if($result && $result['en_creation'] == 0){
        $_SESSION['en_creation'] = False;
    }
}


if(isset($_SESSION['en_creation']) && $_SESSION['en_creation'] === False){
    //RENDER :D

    $j = new Joueur($_SESSION['joueur']);
    $jeu = new Jeu();
    $statement = $bd->prepare('SELECT jeton_id, jeton_position FROM joueur_jeton WHERE jeton_fk_game_id=:game_id AND jeton_joueur_position=:joueur');
    $statement->bindParam(':game_id', $_SESSION['game_id'], PDO::PARAM_INT);
    $statement->bindParam(':joueur', $_SESSION['joueur'], PDO::PARAM_INT);
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    //var_dump($result);

    ?>
    <html>
        <head>
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
            <link rel="stylesheet" href="custom.css">
            
        </head>
        <body>
            <input type="hidden" id="game_id" value="<?php echo $_SESSION['game_id']; ?>">
            <input type="hidden" id="player" value="<?php echo $_SESSION['joueur']; ?>">
            <input type="hidden" id="last_move_id" value="0">
            <div class="col-sm-5">
                <?php if($_SESSION['joueur'] == 1): ?>
                <div id="fin" data-position="-2">
                    OUT
                </div>
                <?php endif; ?>
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
                    <div class="col-sm-4 fleche"><?php if($_SESSION['joueur'] == 1): ?>^<?php endif; ?></div>
                    <div class="col-sm-4" data-position="12"></div>
                    <div class="col-sm-4 fleche"><?php if($_SESSION['joueur'] == 2): ?>^<?php endif; ?></div>
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
                <?php if($_SESSION['joueur'] == 2): ?>
                <div id="fin" data-position="-2">
                    OUT
                </div>
                <?php endif; ?>
            </div>
            <div style="clear:both;"></div>
            <div class="count_1 player_<?php echo $_SESSION['joueur']; ?>">

            </div>
            <div class="count_2 player_<?php echo $_SESSION['joueur'] == 1 ? 2 : 1; ?>">

            </div>
            <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
            <script src="jeu.js"></script>
        </body>
    </html>

    <?php
    
}
elseif(isset($_SESSION['en_creation'])){
    echo 'TROUVE TOÉ KEKUN POUR JOUER LOL<br>';
    echo '<input type="text" value="?game_id=' . $_SESSION['game_id'] . '">';
}
else{
    echo '?????';
}
//echo '<pre>';
//var_dump($j, $jeu);
//echo '</pre>';


?>

