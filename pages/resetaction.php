<?php
include("../classes/PDOManager.php");
include("../classes/AccountManager.php");

try {
    $bdd = PDOManager::getPDO();
    AccountManager::setPasswdReset($bdd, $_POST["username"]);
    header("Location: http://localhost/mbl/pages/reset.php?status=yes");
} catch (MBLException $e) {
    $status = $e->getMessage();
    header("Location: http://localhost/mbl/pages/reset.php?status=$status");
} catch (Exception $e) {
    header("Location: http://localhost/mbl/pages/reset.php?status=special");
}