<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");
require_once("../classes/VallManager.php");
require_once("../classes/CommandManager.php");

try {
    $bdd = PDOManager::getPDO();
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    if (!PDOManager::checkAdmin($username)) {
        header("Location: https://monboulangerlivreur.fr/pages/account.php");
        exit();
    }
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
    }
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php");
    exit();
}