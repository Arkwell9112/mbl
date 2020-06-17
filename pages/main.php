<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");

// Page d'accueil.

$firstref = "";
$firstfield = "";
$secondref = "";
$secondfield = "";
if (isset($_COOKIE["token"])) {
    try {
        $bdd = PDOManager::getPDO();
        $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
        $firstfield = "Bonjour, " . $username;
        $firstref = "#";
        $secondfield = "Mon compte";
        $secondref = "../pages/account.php";
    } catch (Exception $e) {
        $firstfield = "S'inscrire";
        $firstref = "../pages/signup.php";
        $secondfield = "Se connecter";
        $secondref = "../pages/signin.php";
    }
} else {
    $firstfield = "S'inscrire";
    $firstref = "../pages/signup.php";
    $secondfield = "Se connecter";
    $secondref = "../pages/signin.php";
}

$isnav = true;
$title = "Accueil";
include("../frags/fragHeader.php");
?>
    <article id="firstarticle">
        <div class="title">
            <img src="../imgs/delivery.svg">
            <h3>Faites-vous livrer à domicile !</h3>
        </div>
        <ul>
            <li>Votre pain est livré directement chez vous !</li>
            <br>
            <li>Faites du bien à la planète en utilisant moins votre voiture !</li>
            <br>
            <li>Le pain arrive tout chaud directement dans votre panier !</li>
        </ul>
    </article>
    <article>
        <div class="title">
            <img src="../imgs/time.svg">
            <h3>Gagnez du temps !</h3>
        </div>
        <ul>
            <li>Plus besoin de se déplacer ! Le pain vient à vous !</li>
            <br>
            <li>Une tâche de moins à faire dans le journée c'est plus de repos à la fin !</li>
            <br>
            <li>Du pain chaud sans aucuns efforts !</li>
        </ul>
    </article>
    <article id="lastarticle">
        <div class="title">
            <img id="pay" src="../imgs/pay.svg">
            <h3>Rechargez votre compte en ligne !</h3>
        </div>
        <ul>
            <li>Remettez de l'argent sur votre compte pain sans vous déplacer !</li>
            <br>
            <li>Rechargez aussi votre solde en boutique !</li>
            <br>
            <li>Vous êtes avertis dès que votre solde s'approche de 0€ !</li>
        </ul>
    </article>
    <article id="beginbutton">
        <a href="../pages/signup.php">Commencer maintenant !</a>
    </article>
<?php include("../frags/fragFooter.php") ?>