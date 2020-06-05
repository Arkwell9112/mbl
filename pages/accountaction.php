<?php
include("../classes/PDOManager.php");
include("../classes/CommandManager.php");
include("../classes/ConnectionManager.php");

try {
    $bdd = PDOManager::getPDO();
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    if(isset($_GET["action"])) {
        if($_GET["action"] == "disconnect") {
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
            if(isset($_POST[$i])) {
                $amounts[$i] = $_POST[$i];
            } else {
                $amounts[$i] = 0;
            }
        }
        CommandManager::editCommand($bdd, $_POST["product"], $amounts, $username);
    }
    header("Location: http://localhost/mbl/pages/account.php");
} catch (MBLException $e) {
    $status = $e->getMessage();
    header("Location: http://localhost/mbl/pages/account.php?status=$status");
} catch (Exception $e) {
    header("Location: http://localhost/mbl/pages/account.php");
}
