<?php
require_once("../classes/PDOManager.php");
require_once("../classes/AccountManager.php");

// Page de traitement pour la réinitialisation de mot de passe.

try {
    $bdd = PDOManager::getPDO();
    AccountManager::resetPasswd($bdd, $_POST["token"], $_POST["passwd"], $_POST["passwd2"]);
    header("Location: https://monboulangerlivreur.fr/pages/resetpasswd.php?status=yes&token=ok");
} catch (MBLException $e) {
    $status = $e->getMessage();
    $token = $_POST["token"];
    header("Location: https://monboulangerlivreur.fr/pages/resetpasswd.php?status=$status&token=$token");
} catch (Exception $e) {
    $token = $_POST["token"];
    header("Location: https://monboulangerlivreur.fr/pages/resetpasswd.php?status=special&token=$token");
}