<?php
include("../classes/PDOManager.php");
include("../classes/AccountManager.php");

try {
    $bdd = PDOManager::getPDO();
    AccountManager::activeAccount($bdd, $_GET["token"]);
    header("Location: http://localhost/mbl/pages/signin.php?status=yesactive");
} catch (Exception $e) {
    header("Location: http://localhost/mbl/pages/signin.php.status=badtoken");
}