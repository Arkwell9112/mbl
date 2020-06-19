<?php
require_once("/var/www/mbl/classes/PDOManager.php");
require_once("/var/www/mbl/classes/ConnectionManager.php");
require_once("/var/www/mbl/classes/AccountManager.php");
require_once("/var/www/mbl/classes/PayyManager.php");
require_once("/var/www/mbl/classes/WeekDay.php");

try {
    // Supprime les resets et connections dont le temps est dépassé.
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
        // Supprime les comptes non activé dont le temps est dépassé.
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
    // Remet les livraisons à faux.
    $request = $bdd->prepare("UPDATE users SET delivered=0");
    $request->execute();
    // Rejette les paiements qui ont été crées il y a plus de 24H.
    $request = $bdd->prepare("SELECT sessionid FROM payments WHERE creationdate<=:specialdate");
    $request->execute(array(
        "specialdate" => time() - 24 * 3600
    ));
    $result = $request->fetchAll();
    foreach ($result as $payment) {
        PayyManager::rejectPayy($bdd, $payment["sessionid"]);
    }
    // Recherche tous les utilisateurs éligibles à la livraison et réinitialise ensuite tous les paramètres de tournée.
    $specvalue = WeekDay::getDay() . "value";
    $request = "SELECT * FROM users WHERE value>=+replace+ AND +replace+!=0 ORDER BY city";
    $request = str_replace("+replace+", $specvalue, $request);
    $request = $bdd->prepare($request);
    $request->execute();
    $result = $request->fetchAll();
    $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
    $request->execute(array(
        "value" => count($result) - 1,
        "label" => "maxcustomer"
    ));
    $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
    $request->execute(array(
        "value" => "[]",
        "label" => "passed"
    ));
    $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
    $request->execute(array(
        "value" => 0,
        "label" => "currentcustomer"
    ));
    $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
    $request->execute(array(
        "value" => json_encode($result),
        "label" => "inittour"
    ));
    $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
    $request->execute(array(
        "value" => json_encode(array()),
        "label" => "optitour"
    ));
    // Calcul l'itinéraire le plus optimisé en utilisant l'API open-street par tranche de 1000 utilisateurs. (Ou moins dans certains cas évidemment).
    $i = 0;
    if (count($result) > 2) {
        $end = false;
    } else {
        $end = true;
    }
    while (!$end) {
        $begin = $i * 1000;
        if (count($result) - 1 - $begin >= 1000) {
            $finish = $begin + 999;
        } else {
            $finish = count($result) - 1;
            $end = true;
        }
        $ask = "";
        for ($j = $begin; $j <= $finish; $j++) {
            if ($j != $finish) {
                $ask = $ask . $result[$j]["geocode"] . "|";
            } else {
                $ask = $ask . $result[$j]["geocode"];
            }
        }
        $points = $finish + 1 - $i * 1000;
        $openkey = IDManager::getOpenStreetKey();
        $response = file_get_contents("https://maps.open-street.com/api/tsp/?pts=$ask&nb=$points&mode=driving&unit=s&tour=open&key=$openkey");
        $request = $bdd->prepare("SELECT value FROM global WHERE label='optitour'");
        $request->execute();
        $result2 = $request->fetchAll();
        $result2 = json_decode($result2[0]["value"], true);
        $opti = json_decode($response, true);
        // Ecrit les étapes optimisées par tranche de 1000 dans le cas d'une erreur lors de la communication avec l'API.
        foreach ($opti["OPTIMIZATION"] as $index) {
            $result2[] = $index;
        }
        $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
        $request->execute(array(
            "value" => json_encode($result2),
            "label" => "optitour"
        ));
        $i++;
    }
} catch (Exception $e) {

}