<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");

// Page pour le traitement de la connexion à l'espace client. La partie utilisant backtrace n'est plus utilisée depuis l'archivage de la fonction link.

try {
    $bdd = PDOManager::getPDO();
    $token = ConnectionManager::connectWithPasswd($bdd, $_POST["username"], $_POST["passwd"]);
    setcookie("token", $token, time() + ConnectionManager::connectionTime, "/", "monboulangerlivreur.fr", true, true);
    if (isset($_POST["backtrace"])) {
        $backtrace = $_POST["backtrace"];
        header("Location: https://monboulangerlivreur.fr/pages/link.php?token=$backtrace");
        exit();
    }
    header("Location: https://monboulangerlivreur.fr/pages/account.php");
} catch (MBLException $e) {
    $status = $e->getMessage();
    if (isset($_POST["backtrace"])) {
        $backtrace = $_POST["backtrace"];
        header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=$status&backtrace=$backtrace");
        exit();
    }
    header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=$status");
} catch (Exception $e) {
    if (isset($_POST["backtrace"])) {
        $backtrace = $_POST["backtrace"];
        header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=special&backtrace=$backtrace");
        exit();
    }
    header("Location: https://monboulangerlivreur.fr/pages/signin.php?status=special");
}