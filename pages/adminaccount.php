<?php
include("../classes/PDOManager.php");
include("../classes/ConnectionManager.php");

$title = "Accés administrateur";
$firstfield = "Se déconnecter";
$firstref = "accountaction.php?action=disconnect";
$secondfield = "Accueil";
$secondref = "main.php";
$isnav = true;

if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    $status = "";
}

if (isset($_GET["page"])) {
    $page = $_GET["page"];
} else {
    $page = "clients";
}

if (preg_match("#clients#", $page)) {
    $toadd = "<script src='../scripts/clienteditor.js'></script>";
} else if (preg_match("#cities#", $page)) {
    $toadd = "<script src='../scripts/cityeditor.js'></script>";
}

$style = "text-decoration: underline black";

try {
    $bdd = PDOManager::getPDO();
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    if (!PDOManager::checkAdmin($username)) {
        header("Location: http://localhost/mbl/pages/account.php");
        exit();
    }
    $request = $bdd->prepare("SELECT * FROM users");
    $request->execute();
    $result = $request->fetchAll();
} catch (Exception $e) {
    header("Location: http://localhost/mbl/pages/signin.php");
    exit();
}
include("../frags/fragHeader.php");
?>
    <div id="secondnav">
        <div id="innernav1" class="innernav"><a style="<?php if (preg_match("#clients#", $page)) echo $style ?>"
                                                href="adminaccount.php">Clients</a></div>
        <div id="innernav2" class="innernav"><a style="<?php if (preg_match("#commands#", $page)) echo $style ?>"
                                                href="adminaccount.php?page=commands">Commandes</a></div>
        <div id="innernav3" class="innernav"><a style="<?php if (preg_match("#cities#", $page)) echo $style ?>"
                                                href="adminaccount.php?page=cities">Produits & Villages</a></div>
    </div>
<?php
if (preg_match("#clients#", $page)) {
    include("../frags/fragAccountClients.php");
} else if (preg_match("#cities#", $page)) {
    include("../frags/fragAccountCities.php");
} else if (preg_match("#commands#", $page)) {
    include("../frags/fragAccountCommands.php");
}
include("../frags/fragFooter.php");
?>