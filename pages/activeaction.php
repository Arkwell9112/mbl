<?php
require_once("../classes/PDOManager.php");
require_once("../classes/AccountManager.php");

// Page de traitement pour l'activation d'un compte.
// On redirige après activation avec affichage réussite. En cas d'erreur on redirige vers les erreurs.

try {
    $bdd = PDOManager::getPDO();
    AccountManager::activeAccount($bdd, $_GET["token"]);
    header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=yesactive");
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=badtoken");
}