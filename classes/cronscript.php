<?php
require_once("/var/www/mbl/classes/PDOManager.php");
require_once("/var/www/mbl/classes/ConnectionManager.php");
require_once("/var/www/mbl/classes/AccountManager.php");
require_once("/var/www/mbl/classes/PayyManager.php");

try {
    $bdd = PDOManager::getPDO();
    $request = $bdd->prepare("DELETE FROM resets WHERE creationdate<=:specialdate");
    $request->execute(array(
        "specialdate" => time() - AccountManager::resetTime
    ));
    $request = $bdd->prepare("DELETE FROM connections WHERE creationdate<=:specialdate");
    $request->execute(array(
        "specialdate" => time() - ConnectionManager::connectionTime
    ));
    try {
        $bdd->beginTransaction();
        $request = $bdd->prepare("SELECT username FROM accounts WHERE creationdate<=:specialdate AND active=0");
        $request->execute(array(
            "specialdate" => time() - AccountManager::activeTime
        ));
        $result = $request->fetchAll();
        foreach ($result as $user) {
            $request = $bdd->prepare("DELETE FROM users WHERE username=:username");
            $request->execute(array(
                "username" => $user["username"]
            ));
            $request = $bdd->prepare("DELETE FROM accounts WHERE username=:username");
            $request->execute(array(
                "username" => $user["username"]
            ));
        }
        $bdd->commit();
    } catch (Exception $e) {
        $bdd->rollBack();
    }
    $request = $bdd->prepare("UPDATE users SET delivered=0");
    $request->execute();
    $request = $bdd->prepare("SELECT sessionid FROM payments WHERE creationdate<=:specialdate");
    $request->execute(array(
        "specialdate" => time() - 24 * 3600
    ));
    $result = $request->fetchAll();
    foreach ($result as $payment) {
        PayyManager::rejectPayy($bdd, $payment["sessionid"]);
    }
} catch (Exception $e){

}