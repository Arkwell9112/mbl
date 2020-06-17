<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");
require_once("../classes/VallManager.php");
require_once("../classes/CommandManager.php");
require_once("../classes/WeekDay.php");

// Page de traitement pour les ordres provenants de adminaccount.php

try {
    // Véerification connexion et administrateur.
    $bdd = PDOManager::getPDO();
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    if (!PDOManager::checkAdmin($username)) {
        header("Location: https://monboulangerlivreur.fr/pages/account.php");
        exit();
    }
    // En fonction de l'action on fait appel à des classes externes.
    if ($_POST["action"] == "editvalue") {
        $content = array(
            "title" => "Opération sur le solde",
            "content" => array(
                "Montant" => number_format($_POST["amount"], 2) . "€"
            )
        );
        VallManager::editValue($bdd, $_POST["amount"], $_POST["username"], $content, "");
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php");
    } else if ($_POST["action"] == "deleteproduct") {
        CommandManager::deleteProductFromAll($bdd, $_POST["product"]);
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=cities");
    } else if ($_POST["action"] == "editproduct") {
        CommandManager::updateValueFromAll($bdd, $_POST["product"], $_POST["price"]);
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=cities");
    } else if ($_POST["action"] == "addproduct") {
        CommandManager::addProduct($bdd, $_POST["product"]);
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=cities");
    } else if ($_POST["action"] == "addcity") {
        $days = array(0, 0, 0, 0, 0, 0, 0);
        CommandManager::deleteDayFromAll($bdd, $days, true, $_POST["city"]);
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=cities");
    } else if ($_POST["action"] == "editcity") {
        $days = array();
        for ($i = 0; $i <= 6; $i++) {
            if (isset($_POST[$i])) {
                $days[$i] = $_POST[$i];
            } else {
                $days[$i] = 0;
            }
        }
        CommandManager::deleteDayFromAll($bdd, $days, false, $_POST["city"]);
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=cities");
        // Action consistant à valider la commande d'un utilisateur.
    } else if ($_POST["action"] == "force") {
        // Récupération informations et préparation content pour modification value.
        $request = $bdd->prepare("SELECT delivered FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $_POST["username"]
        ));
        $result = $request->fetchAll();
        $command = json_decode($_POST["command"]);
        $products = array(
            "Montant" => number_format(-$_POST["amount"], 2) . "€"
        );
        foreach ($command as $key => $product) {
            $products[$key] = $product[WeekDay::getDay()];
        }
        $content = array(
            "title" => "Paiement commande",
            "content" => json_encode($products)
        );
        // Vérification que l'utilisateur n'a pas déjà été livré (validé).
        if ($result[0]["delivered"] == 0) {
            VallManager::editValue($bdd, -$_POST["amount"], $_POST["username"], $content, "", true);
        }
        // On le retire des utilisateurs passés.
        $request = $bdd->prepare("LOCK TABLES global WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
        $request->execute(array(
            "label" => "passed"
        ));
        $passed = $request->fetchAll();
        $passed = json_decode($passed[0]["value"], true);
        unset($passed[array_search($_POST["index"], $passed)]);
        $passed = json_encode($passed);
        $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
        $request->execute(array(
            "value" => $passed,
            "label" => "passed"
        ));
        $request = $bdd->prepare("UNLOCK TABLES");
        $request->execute();
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=force");
    }
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php");
    exit();
}