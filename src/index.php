<?php
session_start();
$availableColors = ['black', 'red', 'white', 'yellow', 'green', 'pink'];
if(!isset($_SESSION['secret_combination'], $_SESSION['player_combination'], $_SESSION['currentSecretIndex'], $_SESSION['currentScore'])){
    $_SESSION['secret_combination'] = [];
    $_SESSION['player_combination'] = [];
    $_SESSION['currentSecretIndex'] = 0; // Correspond à l'index actuel de la liste secrète. La valeur sera incrémenté au fil des tours.
    $_SESSION['currentScore'] = 0;
    $secretCombinationKeys = array_rand($availableColors, 4); // génère, de manière aléatoire, 4 clés du tableau $availableColors
    shuffle($secretCombinationKeys); // mélange le tableau des clés
    // $secretCombination est la variable qui contient la combinaison secrète que doit trouver le joueur
    $_SESSION['secret_combination'] = array_map(function ($colorKey) use ($availableColors) { // transforme les clés en valeur
        return $availableColors[$colorKey];
    }, $secretCombinationKeys);
}

$currentSecretIndex = &$_SESSION['currentSecretIndex']; // Pour plus de lisibilité et éviter de trop réutiliser les variables de session.
$secretCombination = $_SESSION['secret_combination']; // Même chose.
$validation = isset($_GET['color']) && $currentSecretIndex < count($secretCombination); // Si la super globale $color est initialisée et que le joueur n'a pas encore gagné.

if($validation){
    $color = $_GET['color'];
    $index = array_search($color, $_SESSION['secret_combination'], true); //On récupère l'index de la couleur envoyé en GET.

    //Si array_search() renvoie false donc la couleur ne fait pas partie de la liste secrète.
    if($index === false){ 
        $notFound = "$color ne fait pas partie de la combinaison"; // On affiche un message d'erreur
    } else if($secretCombination[$index] == $secretCombination[$currentSecretIndex]){ // si $index != false donc il existe dans la liste. On vérifie d'abord si la valeur associé à l'index est au bon endroit
        array_push($_SESSION['player_combination'], $secretCombination[$index]); // Si oui, on push la valeur dans la liste du joueur.
        $found = "$color est placé au bon endroit !"; // Et on affiche un message de succès.
        if($currentSecretIndex < count($secretCombination)){ //Si l'index actuel de la liste secrète est plus petit que la taille de la liste secrète alors le joueur n'a pas encore gagné.
            $_SESSION['currentSecretIndex']++; // On incrémente pour le prochain tours.
        }
    } else { // Si la valeur de l'index n'est pas au bon endroit, on affiche un message d'avertissement.
        $badPlace = "$color n'est pas au bon endroit !";
    }
    $_SESSION['currentScore']++; //Incrémentation du score à la fin de chaque tours.
}

if(!($currentSecretIndex < count($secretCombination)) && !isset($_SESSION['bestScore'])){ //Si le joueur a gagné et que la variable de session $besScore n'exsite pas, on lui affecte la valeur du score actuel.
    $_SESSION['bestScore'] = $_SESSION['currentScore'];
} else if(!($currentSecretIndex < count($secretCombination)) && $_SESSION['currentScore'] < $_SESSION['bestScore']){ //Si le joueur gagne et que la variable de session $bestScore existe déjà, on lui affecte la plus petite valeur du score actuel.
    $_SESSION['bestScore'] = $_SESSION['currentScore'];
}

if(isset($_GET['action']) && $_GET['action'] == 'rejouer'){ // Pour relancer une partie, on supprime toutes les variables de session sauf $_SESSION['bestScore']
    unset($_SESSION['secret_combination'], $_SESSION['player_combination'], $_SESSION['currentSecretIndex'], $_SESSION['currentScore']);
    header('Location:/');
}

if (true) { // si vous souhaitez voir, pour debug, ce que contient $secretCombination, remplacez false par true
    echo '<pre>';
    print_r($_SESSION);
    echo '</pre>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>😵 Mastermind</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="font/bootstrap-icons.css" rel="stylesheet">
    <link href="css/master.css" rel="stylesheet">

</head>

<body>

<div class="d-flex justify-content-center mt-5 mb-4">
    <h1>😵 Mastermind</h1>
</div>

<div class="d-flex justify-content-center mb-3">
    <div class="d-flex justify-content-around col-5 col-md-3">
        <div class="try col-5">
            <i class="bi bi-hand-index"></i>
            <?= $_SESSION['currentScore'] ?> <!-- nombre de tour de la partie en cours -->
        </div>
        <div class="try col-5">
            <i class="bi bi-hand-thumbs-up"></i>
            <?= $_SESSION['bestScore'] ?? '-' ?> <!-- meilleur nombre de tour (le plus bas) du joueur -->
        </div>
    </div>
</div>

<div class="d-flex justify-content-center">
    <!-- Dès que le joueur gagne, on enlève l'attribut href pour forcer l'arrêt de l'empilage de la variable de session `player_combination` -->
    <a <?= ($currentSecretIndex < count($secretCombination)) ? "href=\"?color=black\"" : ""?> class="box black"></a>
    <a <?= ($currentSecretIndex < count($secretCombination)) ? "href=\"?color=red\"" : ""?> class="box red"></a>
    <a <?= ($currentSecretIndex < count($secretCombination)) ? "href=\"?color=white\"" : ""?> class="box white"></a>
    <a <?= ($currentSecretIndex < count($secretCombination)) ? "href=\"?color=yellow\"" : ""?> class="box yellow"></a>
    <a <?= ($currentSecretIndex < count($secretCombination)) ? "href=\"?color=green\"" : ""?> class="box green"></a>
    <a <?= ($currentSecretIndex < count($secretCombination)) ? "href=\"?color=pink\"" : ""?> class="box pink"></a>
</div>
<!-- PARTIE AFFICHAGE -->
<div class="container">
<?php if(isset($notFound)) : ?>
    <div class="alert alert-danger"> <?= $notFound ?> </div>    
<?php elseif(isset($found)) : ?>
    <div class="alert alert-info"> <?= $found ?> </div>
<?php elseif(isset($badPlace)) : ?>
    <div class="alert alert-warning"> <?= $badPlace ?> </div>    
<?php endif; ?>
<?php if(!($currentSecretIndex < count($secretCombination))) : ?>
    <div class="alert alert-success"> <?= "Félicitation ! Vous avez trouvé toutes les couleur secretes." ?> </div>
    <a href="?action=rejouer" class="btn btn-primary text-center mb-3">Rejouer</a>   
<?php endif; ?>
<ul class="list-group">
<?php foreach ($_SESSION['player_combination'] as $value) : ?> <!-- On parcours la variable de session 'player_combination' pour afficher ses valeurs à chaque empilage -->
  <li class="list-group-item"><?= strtoupper($value) ?></li>
<?php endforeach; ?>
</ul>
</div>
<!-- Bootstrap core JavaScript -->
<script src="js/jquery.min.js"></script>
<script src="js/index.js"></script>

</body>

</html>