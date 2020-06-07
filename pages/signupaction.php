<?php
include("../classes/AccountManager.php");
include("../classes/PDOManager.php");
try {
    AccountManager::createAccount(PDOManager::getPDO(), $_POST["username"], $_POST["passwd1"], $_POST["passwd2"], $_POST["mail"], $_POST["phone"], $_POST["city"]);
    header("Location: http://localhost/mbl/pages/signup.php?status=yes");
} catch (MBLException $e) {
    if ($e->getMessage() != "special") {
        $message = $e->getMessage();
        $username = $_POST["username"];
        $mail = $_POST["mail"];
        $phone = $_POST["phone"];
        header("Location: http://localhost/mbl/pages/signup.php?status=$message&username=$username&mail=$mail&phone=$phone");
    } else {
        header("Location: http://localhost/mbl/pages/signup.php?status=special");
    }
} catch (Exception $e) {
    header("Location: http://localhost/mbl/pages/signup.php?status=special");
}