<?php
require_once("../classes/PDOManager.php");
require_once("../classes/AccountManager.php");

try {
    AccountManager::createAccount(PDOManager::getPDO(), $_POST["username"], $_POST["passwd1"], $_POST["passwd2"], $_POST["mail"], $_POST["phone"], $_POST["city"]);
    header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=yes");
} catch (MBLException $e) {
    if ($e->getMessage() != "special") {
        $message = $e->getMessage();
        $username = $_POST["username"];
        $mail = $_POST["mail"];
        $phone = $_POST["phone"];
        header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=$message&username=$username&mail=$mail&phone=$phone");
    } else {
        header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=special");
    }
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=special");
}