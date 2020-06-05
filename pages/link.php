<?php
include("../classes/LinkManager.php");
include("../classes/PDOManager.php");
include("../classes/ConnectionManager.php");
include("../classes/ValueManager.php");
include("../classes/WeekDay.php");

$isadmin = false;
$amount = "not";

try {
    $bdd = PDOManager::getPDO();
    if (isset($_GET["token"])) {
        $linkeduser = LinkManager::checkLink($bdd, $_GET["token"]);
        if (isset($_COOKIE["token"])) {
            try {
                $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
                if (PDOManager::checkAdmin($username)) {
                    $isadmin = true;
                }
            } catch (Exception $e) {

            }
        }
    } else {
        header("Location: http://localhost/mbl/pages/main.php");
        exit();
    }
} catch (MBLException $e) {
    if ($e->getMessage() == "notlinked") {
        try {
            $token = $_GET["token"];
            if (isset($_COOKIE["token"])) {
                $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
                LinkManager::makeLink($bdd, $_GET["token"], $username);
                header("Refresh:0");
                exit();
            } else {
                header("Location: http://localhost/mbl/pages/signin.php?backtrace=$token");
                exit();
            }
        } catch (MBLException $e) {
            header("Location: http://localhost/mbl/pages/signin.php?backtrace=$token");
            exit();
        } catch (Exception $e) {
            header("Refresh:0");
            exit();
        }
    } else if ($e->getMessage() == "badtoken") {
        header("Location: http://localhost/mbl/pages/main.php");
        exit();
    }
}

if ($isadmin) {
    try {
        $request = $bdd->prepare("SELECT * FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $linkeduser
        ));
        $result = $request->fetchAll();
        $command = json_decode($result[0]["command"]);
        $products = array();
        foreach ($command as $key => $product) {
            $products[$key] = $product[WeekDay::getDay()];
        }
        $content = array(
            "title" => "Paiement commande",
            "content" => $products
        );
        ValueManager::editValue($bdd, -$result[0][WeekDay::getDay() . "value"], $linkeduser, $content, "");
        $amount = "yes";
    } catch (MBLException $e) {
        if ($e->getMessage() == "special") {
            header("Refresh:0");
            exit();
        }
    } catch (Exception $e) {
        header("Refresh:0");
        exit();
    }
}

if(!$isadmin) {
    echo "Ce sac appartient à $linkeduser";
} else {
    if($amount == "yes") {
        echo "Bien payé";
    } else {
        echo "Pas bien payé";
    }
}