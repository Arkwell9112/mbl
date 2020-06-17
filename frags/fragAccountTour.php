<?php
require_once("../classes/WeekDay.php");
require_once("../classes/VallManager.php");

// Fragment pour l'affichage de la page tournée du compte administrateur.


// Fonction pour faire avancer de 1 le client courant de la tournée.
function pp(PDO $bdd)
{
    $request = $bdd->prepare("LOCK TABLES global WRITE");
    $request->execute();
    $request = $bdd->prepare("SELECT value FROM global WHERE label='currentcustomer'");
    $request->execute();
    $result = $request->fetchAll();
    $currentcustomer = $result[0]["value"];
    $currentcustomer++;
    $request = $bdd->prepare("UPDATE global SET value=:value WHERE label='currentcustomer'");
    $request->execute(array(
        "value" => $currentcustomer
    ));
    $request = $bdd->prepare("UNLOCK TABLES");
    $request->execute();
}

if (!isset($_POST["action"])) {
    $action = "";
} else {
    $action = $_POST["action"];
}

// On récupère toutes les informations pour l'affichage de la tournée.
try {
    $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
    $request->execute(array(
        "label" => "currentcustomer"
    ));
    $result = $request->fetchAll();
    $currentuser = $result[0]["value"];
    $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
    $request->execute(array(
        "label" => "maxcustomer"
    ));
    $result = $request->fetchAll();
    $maxcustomer = $result[0]["value"];
    $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
    $request->execute(array(
        "label" => "inittour"
    ));
    $result = $request->fetchAll();
    $inittour = json_decode($result[0]["value"], true);
    $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
    $request->execute(array(
        "label" => "optitour"
    ));
    $result = $request->fetchAll();
    $optitour = json_decode($result[0]["value"], true);
} catch (Exception $e) {
    header("Refresh:0");
}

// On regarde si on est en train d'éxécuter une action. Si oui on regarde laquelle et on applique.
// Ici on valide la commande, on édité donc la value de l'utilisateur puis on fait avancer la tournée.
if (preg_match("#validate#", $action)) {
    $customer = $inittour[$optitour[$currentuser]];
    $command = json_decode($customer["command"], true);
    $products = array(
        "Montant" => number_format(-$customer[WeekDay::getDay() . "value"], 2) . "€"
    );
    foreach ($command as $key => $product) {
        $products[$key] = $product[WeekDay::getDay()];
    }
    $content = array(
        "title" => "Paiement commande",
        "content" => $products
    );
    try {
        $request = $bdd->prepare("SELECT delivered FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $customer["username"]
        ));
        $result = $request->fetchAll();
        if ($result[0]["delivered"] == 0) {
            VallManager::editValue($bdd, -$customer[WeekDay::getDay() . "value"], $customer["username"], $content, "", true);
        }
        $request->execute();
        pp($bdd);
        header("location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=tour");
    } catch (Exception $e) {
        header("Refresh:0");
    }
    // Ici on ne fait que passer par-dessus l'utilisateur et l'ajouter aux utilisateurs passés.
} else if (preg_match("#bypass#", $action)) {
    try {
        $request = $bdd->prepare("LOCK TABLES global WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT value FROM global WHERE label=:label");
        $request->execute(array(
            "label" => "passed"
        ));
        $passed = $request->fetchAll();
        $passed = json_decode($passed[0]["value"], true);
        $passed[] = $currentuser;
        $passed = json_encode($passed);
        $request = $bdd->prepare("UPDATE global SET value=:value WHERE label=:label");
        $request->execute(array(
            "value" => $passed,
            "label" => "passed"
        ));
        $request = $bdd->prepare("UNLOCK TABLES");
        $request->execute();
        pp($bdd);
        header("location: https://monboulangerlivreur.fr/pages/adminaccount.php?page=tour");
    } catch (Exception $e) {
        header("Refresh:0");
    }
}

// On regarde si l'on est à la fin de la tournée ou non.
if ($currentuser <= $maxcustomer) {
    $customer = $inittour[$optitour[$currentuser]];
    $name = $customer["username"];
    $address = $customer["address"] . ", " . $customer["city"];
    $customer = $inittour[$optitour[$currentuser]];
    $command = json_decode($customer["command"], true);
} else {
    $name = "N/A";
    $address = "La tournée est terminée";
    $command = array();
}
?>

<article id="oncearticle">
    <div class="title">
        <img src="../imgs/delivery.svg">
        <h3>Ma tournée</h3>
    </div>
    <div class="signform" id="toreplaceform">
        <span class="tounderline">Nom d'utilisateur :</span><?php echo " " . $name ?><br><br>
        <span class="tounderline">Adresse :</span><?php echo " " . $address ?><br><br>
        <span class="tounderline">Commande :</span><br><br>
        <?php
        // On affiche seulement les produits et quantités pour le jour courant. D'après la commande de cette personne.
        $products = array();
        foreach ($command as $key => $product) {
            $products[$key] = $product[WeekDay::getDay()];
        }
        foreach ($products as $key => $product) {
            echo $key . " : " . $product . "<br>";
        }
        if ($name != "N/A") {
            echo "<form method=\"post\" action=\"adminaccount.php?page=tour\">
            <input type=\"hidden\" name=\"action\" value=\"validate\">
            <input type=\"submit\" value=\"Valider la livraison\">
        </form>
        <form method=\"post\" action=\"adminaccount.php?page=tour\">
            <input type=\"hidden\" name=\"action\" value=\"bypass\">
            <input type=\"submit\" value=\"Passer la livraison\">
        </form>";
        }
        ?>
        <a href="adminaccount.php?page=force" class="bottomlink">Liste des clients passées</a>
    </div>
</article>