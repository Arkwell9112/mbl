<?php
include("../classes/PDOManager.php");
include("../classes/AccountManager.php");

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