<?php
require_once("../classes/PDOManager.php");
require_once("../classes/AccountManager.php");

// Page de traitement pour la demande de réinitialisation de mot de passe.

try {
    $bdd = PDOManager::getPDO();
    AccountManager::setPasswdReset($bdd, $_POST["username"]);
    header("Location: https://monboulangerlivreur.fr/pages/reset.php?status=yes");
} catch (MBLException $e) {
    $status = $e->getMessage();
    header("Location: https://monboulangerlivreur.fr/pages/reset.php?status=$status");
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/reset.php?status=special");
}