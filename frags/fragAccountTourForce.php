<?php
require_once("../classes/PDOManager.php");
require_once("../classes/WeekDay.php");
require_once("../classes/VallManager.php");

try {
    $bdd = PDOManager::getPDO();
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php");
}

if (isset($_POST["action"])) {
    try {
        $request = $bdd->prepare("SELECT delivered FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $_POST["username"]
        ));
        $result = $request->fetchAll();
        $command = json_decode($_POST["command"]);
        $products = array(
            "Montant" => number_format(-$_POST["amount"], 2) . "€"
        );
        foreach ($command as $key => $product) {
            $products[$key] = $product[WeekDay::getDay()];
        }
        $content = array(
            "title" => "Paiement commande",
            "content" => json_encode($products)
        );
        if ($result[0]["delivered"] == 0) {
            VallManager::editValue($bdd, -$_POST["amount"], $_POST["username"], $content, "", true);
        }
        $request = $bdd->prepare("LOCK TABLES global WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
        $request->execute(array(
            "label" => "passed"
        ));
        $passed = $request->fetchAll();
        $passed = json_decode($passed[0]["value"], true);
        unset($passed[array_search($_POST["index"], $passed)]);
        $passed = json_encode($passed);
        $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
        $request->execute(array(
            "value" => $passed,
            "label" => "passed"
        ));
        $request = $bdd->prepare("UNLOCK TABLES");
        $request->execute();
    } catch (Exception $e) {
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php");
    }
}

?>

<article id="oncearticle">
    <div class="title">
        <img src="../imgs/datas.svg">
        <h3>Clients passés lors de la tournée</h3>
    </div>
    <div class="tablecontain">
        <table>
            <thead>
            <tr>
                <th>Nom d'utilisateur</th>
                <th>Adresse</th>
                <th>Village</th>
                <th>Commande</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
            $request->execute(array(
                "label" => "passed"
            ));
            $result = $request->fetchAll();
            $passed = $result[0]["value"];
            $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
            $request->execute(array(
                "label" => "inittour"
            ));
            $result = $request->fetchAll();
            $inittour = $result[0]["value"];
            $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
            $request->execute(array(
                "label" => "optitour"
            ));
            $result = $request->fetchAll();
            $optitour = $result[0]["value"];
            $optitour = json_decode($optitour, true);
            $inittour = json_decode($inittour, true);
            $passed = json_decode($passed, true);
            foreach ($passed as $pass) {
                echo "<tr>";
                $username = $inittour[$optitour[$pass]]["username"];
                $address = $inittour[$optitour[$pass]]["address"];
                $city = $inittour[$optitour[$pass]]["city"];
                $command = $inittour[$optitour[$pass]]["command"];
                $value = $inittour[$optitour[$pass]][WeekDay::getDay() . "value"];
                echo "<td>$username</td>";
                echo "<td>$address</td>";
                echo "<td>$city</td>";
                $command = json_decode($command, true);
                $commandstring = "";
                foreach ($command as $key => $product) {
                    $commandstring = $commandstring . $key . " : " . $product[WeekDay::getDay()] . "<br>";
                }
                echo "<td>$commandstring</td>";
                echo "<td><form method='post' action='adminaccount.php?page=force'>
                        <input type='hidden' name='action' value='force'>
                        <input type='hidden' name='username' value='$username'>
                        <input type='hidden' name='amount' value='$value'>
                        <input type='hidden' name='command' value='$command'>
                        <input type='hidden' name='index' value='$pass'>
                        <input type='submit' value='Forcer la validation'>
                      </form></td>";
            }
            ?>
            </tbody>
        </table>
    </div>
</article>