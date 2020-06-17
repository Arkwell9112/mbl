<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");

// Page d'affichage du compte administrateur.

$title = "Accés administrateur";
$firstfield = "Se déconnecter";
$firstref = "accountaction.php?action=disconnect";
$secondfield = "Accueil";
$secondref = "main.php";
$isnav = true;

if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    $status = "";
}

if (isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = "clients";
}

// On utilise différents scripts en fonction de la page à afficher.
if (preg_match("#clients#", $page)) {
    $toadd = "<script src='../scripts/clienteditor.js'></script>";
} else if (preg_match("#cities#", $page)) {
    $toadd = "<script src='../scripts/cityeditor.js'></script>";
}

$style = "text-decoration: underline black";

try {
    // On vérifie la connexion et si l'utilisateur est bien administrateur.
    $bdd = PDOManager::getPDO();
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    if (!PDOManager::checkAdmin($username)) {
        header("Location: https://monboulangerlivreur.fr/pages/account.php");
        exit();
    }
    $request = $bdd->prepare("SELECT * FROM users");
    $request->execute();
    $result = $request->fetchAll();
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/signin.php");
    exit();
}
include("../frags/fragHeader.php");
// On affiche la barre de navigation propre au compte administrateur.
?>
    <div id="secondnav">
        <div id="innernav1" class="innernav"><a style="<?php if (preg_match("#clients#", $page)) echo $style ?>"
                                                href="adminaccount.php">Clients</a></div>
        <div id="innernav2" class="innernav"><a style="<?php if (preg_match("#commands#", $page)) echo $style ?>"
                                                href="adminaccount.php?page=commands">Commandes</a></div>
        <div id="innernav3" class="innernav"><a style="<?php if (preg_match("#cities#", $page)) echo $style ?>"
                                                href="adminaccount.php?page=cities">Produits & Villages</a></div>
        <div id="innernav4" class="innernav"><a style="<?php if (preg_match("#tour#", $page)) echo $style ?>"
                                                href="adminaccount.php?page=tour">Ma tournée</a></div>
    </div>
<?php

// En fonction de la page à afficher on insére différents fragments.
if (preg_match("#clients#", $page)) {
    include("../frags/fragAccountClients.php");
} else if (preg_match("#cities#", $page)) {
    include("../frags/fragAccountCities.php");
} else if (preg_match("#commands#", $page)) {
    include("../frags/fragAccountCommands.php");
} else if (preg_match("#tour#", $page)) {
    include("../frags/fragAccountTour.php");
} else if (preg_match("#force#", $page)) {
    include("../frags/fragAccountTourForce.php");
}
include("../frags/fragFooter.php");
?>