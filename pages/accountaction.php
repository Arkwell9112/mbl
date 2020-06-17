<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");
require_once("../classes/CommandManager.php");

// Page de traitement pour les ordres venant de account.php

try {
    $bdd = PDOManager::getPDO();
    // Vérification que la personne est bien connectée.
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    // En fonction de l'action on fait appel aux classes statiques externes.
    if (isset($_GET["action"])) {
        if ($_GET["action"] == "disconnect") {
            ConnectionManager::disconnectWithToken($bdd, $_COOKIE["token"]);
        }
    }
    if ($_POST["action"] == "add") {
        $amounts = array(0, 0, 0, 0, 0, 0, 0);
        CommandManager::editCommand($bdd, $_POST["product"], $amounts, $username);
    } else if ($_POST["action"] == "delete") {
        CommandManager::deleteProduct($bdd, $_POST["product"], $username, true);
    } else if ($_POST["action"] == "edit") {
        $amounts = array();
        for ($i = 0; $i <= 6; $i++) {
            if (isset($_POST[$i])) {
                $amounts[$i] = $_POST[$i];
            } else {
                $amounts[$i] = 0;
            }
        }
        CommandManager::editCommand($bdd, $_POST["product"], $amounts, $username);
    }
    header("Location: https://monboulangerlivreur.fr/pages/account.php");
} catch (MBLException $e) {
    $status = $e->getMessage();
    header("Location: https://monboulangerlivreur.fr/pages/account.php?status=$status");
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/account.php");
}
