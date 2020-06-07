<?php
include("../classes/PDOManager.php");
include("../classes/AccountManager.php");

try {
    $bdd = PDOManager::getPDO();
    AccountManager::activeAccount($bdd, $_GET["token"]);
    header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=yesactive");
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/signin.php.status=badtoken");
}