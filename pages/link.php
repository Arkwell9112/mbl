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
        header("Location: https://monboulangerlivreur.fr/pages/main.php");
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
                header("Location: https://monboulangerlivreur.fr/pages/signin.php?backtrace=$token");
                exit();
            }
        } catch (MBLException $e) {
            header("Location: https://monboulangerlivreur.fr/pages/signin.php?backtrace=$token");
            exit();
        } catch (Exception $e) {
            header("Refresh:0");
            exit();
        }
    } else if ($e->getMessage() == "badtoken") {
        header("Location: https://monboulangerlivreur.fr/pages/main.php");
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
        $products = array(
            "Montant" => -$result[0][WeekDay::getDay() . "value"]
        );
        foreach ($command as $key => $product) {
            $products[$key] = $product[WeekDay::getDay()];
        }
        $content = array(
            "title" => "Paiement commande",
            "content" => $products
        );
        if ($result[0]["delivered"] == 0) {
            ValueManager::editValue($bdd, -$result[0][WeekDay::getDay() . "value"], $linkeduser, $content, "", true);
            $amount = "yes";
        } else {
            $amount = "yes";
        }
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="icon" href="../imgs/bread.png">
    <title>Sac à pain</title>
    <link rel="stylesheet" href="../styles/link.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1">
</head>
<body>
<?php
if (!$isadmin || $amount == "yes") {
    echo "<img src='../imgs/tick.svg'>";
} else {
    echo "<img src='../imgs/cross.svg'>";
}
?>
<p>
    <?php
    if (!$isadmin) {
        echo "Ce sac à pain appartient à $linkeduser.";
    } else {
        if ($amount == "yes") {
            echo "La commande a bien été payée par $linkeduser.<br><br>";
        } else {
            echo "La commande n'a pas pu être payée par $linkeduser à cause d'un manque de fonds.<br><br>";
        }
        echo "Commande: <br><br>";
        foreach ($command as $key => $product) {
            $quantity = $product[WeekDay::getDay()];
            echo "$key: $quantity<br>";
        }
    }
    ?>
</p>
</body>
</html>