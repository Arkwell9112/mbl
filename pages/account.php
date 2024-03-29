<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");

// Page pour l'affichage d'un compte utilisateur.

$toadd = "";

if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    $status = "";
}

if (!isset($_COOKIE["token"])) {
    header("Location: https://monboulangerlivreur.fr/pages/signin.php");
}

try {
    // On vérifie que la personne est connectée. Sinon on redirige vers la connexion, si administrateur on redirige vers adminaccount.
    $bdd = PDOManager::getPDO();
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    if (PDOManager::checkAdmin($username)) {
        header("Location: https://monboulangerlivreur.fr/pages/adminaccount.php");
        exit();
    }
    // Récupèration de toutes les informations pour l'affichage comme infos perso, infos sur le village, infos sur les produits pour ajout de produit.
    // Récupèration aussi des opérations concernant l'utilisateur.
    $request = $bdd->prepare("SELECT * FROM users WHERE username=:username");
    $request->execute(array(
        "username" => $username
    ));
    $result = $request->fetchAll();
    $command = json_decode($result[0]["command"], true);
    $request = $bdd->prepare("SELECT * FROM accounts WHERE username=:username");
    $request->execute(array(
        "username" => $username
    ));
    $result2 = $request->fetchAll();
    $request = $bdd->prepare("SELECT * FROM cities WHERE name=:name");
    $request->execute(array(
        "name" => $result[0]["city"]
    ));
    $result3 = $request->fetchAll();
    $request = $bdd->prepare("SELECT * FROM products");
    $request->execute();
    $result4 = $request->fetchAll();
    for ($i = 0; $i <= count($result4) - 1; $i++) {
        $result4[$i]["price"] = number_format($result4[$i]["price"], 2);
    }
    $products = json_encode($result4);
    $request = $bdd->prepare("SELECT * FROM operations WHERE username=:username");
    $request->execute(array(
        "username" => $username
    ));
    $result5 = $request->fetchAll();
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/signin.php");
    exit();
}

$toadd = $toadd . "<script src='../scripts/accounteditor.js'></script>";

$isnav = true;
$title = "Mon compte";
$firstfield = "Se déconnecter";
$firstref = "../pages/accountaction.php?action=disconnect";
$secondfield = "Accueil";
$secondref = "../pages/main.php";

include("../frags/fragHeader.php");
?>
<article id="firstarticle">
    <?php echo "<input type='hidden' name='products' id='products' value='$products'>"; ?>
    <div class="title">
        <img src="../imgs/delivery.svg">
        <h3>Mes livraisons</h3>
    </div>
    <div id="tablecontainment" class="tablecontain">
        <?php if (preg_match("#24h#", $status)) include("../frags/fragErrorProducts.php") ?>
        <table>
            <thead>
            <tr>
                <th>Produit</th>
                <th>Lundi</th>
                <th>Mardi</th>
                <th>Mercredi</th>
                <th>Jeudi</th>
                <th>Vendredi</th>
                <th>Samedi</th>
                <th>Dimanche</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php
            if (!isset($command)) {
                $command = array();
            }
            // On boucle sur la commande pour afficher lesproduits et les quantités choisies. On affiche aussi les petits boutons de modification.
            foreach ($command as $key => $value) {
                $row = "<td>$key</td>";
                for ($i = 0; $i <= 6; $i++) {
                    $value2 = $value[$i];
                    if ($result3[0][$i . "delivery"] != "0") {
                        $row = $row . "<td>$value2</td>";
                    } else {
                        $row = $row . "<td>Non livré</td>";
                    }
                }
                $row = $row . "<td><span class='editbutton editorbutton'>Modifier </span><br><br><form action='accountaction.php' method='post'><input class='deleter' type='hidden' name='action' value='delete'>
                <input class='deleter' type='hidden' name='product' value='$key'>
                <input class='editbutton' type='submit' value='Supprimer'>
                </form></td>";
                echo "<tr>" . $row . "</tr>";
            }
            ?>
            <tr>
                <td id="addcell" colspan='9'><span id='addbutton'>Ajoutez un produit</span></td>
            </tr>
            </tbody>
        </table>
    </div>
</article>
<article>
    <div class="title">
        <img src="../imgs/deliverytime.svg">
        <h3>Les horaires de livraison dans mon village</h3>
    </div>
    <div class="tablecontain">
        <table>
            <thead>
            <tr>
                <th>Lundi</th>
                <th>Mardi</th>
                <th>Mercredi</th>
                <th>Jeudi</th>
                <th>Vendredi</th>
                <th>Samedi</th>
                <th>Dimanche</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <?php
                // On affiche les informations sur le village.
                for ($i = 1; $i <= count($result3[0]) / 2 - 1; $i++) {
                    if ($result3[0][$i] == "0") {
                        echo "<td>Non livré</td>";
                    } else {
                        $day = $result3[0][$i];
                        echo "<td>$day</td>";
                    }
                }
                ?>
            </tr>
            </tbody>
        </table>
    </div>
</article>
<!-- Article pour afficher le montant restant et proposer un lien vers le rechargement. -->
<article>
    <div class="title">
        <img id="wallet" src="../imgs/money.svg">
        <h3>Mon solde</h3>
    </div>
    <p>
        Vous disposez de <span id="money"><?php echo $result[0]["value"] ?>€</span> sur votre compte
        boulanger.<br><br><br>
        Rechargez ! (cliquez sur la somme souhaitée) :<br> <a href="tostripe.php?value=20">20 €</a> <a
                href="tostripe.php?value=40">40 €</a> <a href="tostripe.php?value=60">60 €</a>
    </p>
</article>
<!-- Rappelle des données personnelles possédées par le site. -->
<article>
    <div class="title">
        <img src="../imgs/datas.svg">
        <h3>Mes données personnelles</h3>
    </div>
    <div class="toalign">
        <p class="tobealigned">
            <span class="tounderline">Nom d'utilisateur</span> : <?php echo $result[0]["username"] ?><br><br>
            <span class="tounderline">Adresse e-mail</span> : <?php echo $result2[0]["mail"] ?><br><br>
            <span class="tounderline">Téléphone</span> : <?php echo $result[0]["phone"] ?><br><br>
            <span class="tounderline">Adresse</span> : <?php echo $result[0]["address"] ?><br><br>
            <span class="tounderline">Village de résidence</span> : <?php echo $result[0]["city"] ?>
        </p>
    </div>
</article>
<article>
    <div class="title">
        <img src="../imgs/operation.svg">
        <h3>Vos opérations</h3>
    </div>
    <div>
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Contenu</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // On boucle sur les opérations qui concerne l'utilisateur et on les affichent.
            foreach ($result5 as $innerresult) {
                $id = $innerresult["id"];
                echo "<tr>";
                echo "<td>$id</td>";
                $content = $innerresult["content"];
                $content = json_decode($content, true);
                $contentstring = $content["title"] . "<br><br>";
                foreach ($content["content"] as $key => $innervalue) {
                    $contentstring = $contentstring . "$key : $innervalue<br>";
                }
                echo "<td>$contentstring</td>";
                $date = date("d/m/Y", $innerresult["creationdate"]);
                echo "<td>$date</td>";
                echo "</tr>";
            }
            if (count($result5) == 0) {
                echo "<tr><td colspan='3'>Aucune opération sur votre compte pour le moment.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</article>
<?php include("../frags/fragFooter.php") ?>
