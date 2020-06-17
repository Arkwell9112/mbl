<?php

use Stripe\Checkout\Session;
use Stripe\Stripe;

// Page de création de paiement et pour la redirection vers la page de paiement.

require("/var/www/mbl/vendor/autoload.php");
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");
require_once("../classes/PayyManager.php");

// On set les ids de prix en fonction du choix du client.

if (isset($_GET["value"])) {
    if ($_GET["value"] == 20) {
        $amount = 20;
        $price = "price_1GrnTNHQXmOPYXA5okAzbWEV";
    } else if ($_GET["value"] == 40) {
        $amount = 40;
        $price = "price_1Gs12dHQXmOPYXA5MVM4FSW3";
    } else if ($_GET["value"] == 60) {
        $amount = 60;
        $price = "price_1Gs13dHQXmOPYXA5mNsrdzh8";
    } else {
        header("Location: https://monboulangerlivreur.fr/pages/account.php");
        exit();
    }
} else {
    header("Location: https://monboulangerlivreur.fr/pages/account.php");
    exit();
}
try {
    $bdd = PDOManager::getPDO();
    // On vérifie que l'utilisateur est connecté.
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
    // On crée une session de checkout stripe.
    Stripe::setApiKey('sk_test_51Grjv6HQXmOPYXA5HCeZONczTbtuCTNFJBpqC3wvjb9ksXEpuv0hXoIXLD6sSXYZHUNZ4wiSLknC8mvH6b925Fje00WEBL44ua');
    $session = Session::create(array(
        "payment_method_types" => array("card"),
        "line_items" => array(array(
            "price" => $price,
            "quantity" => 1
        )),
        "mode" => "payment",
        "success_url" => "https://monboulangerlivreur.fr/pages/pay.php?status=yes",
        "cancel_url" => "https://monboulangerlivreur.fr/pages/pay.php?status=no"
    ));
    $sessionid = $session->id;
    // On crée le paiement dans la base de donnée.
    PayyManager::initPayy($bdd, $sessionid, $username, $amount);
} catch (Exception $e) {
    header("Location: https://monboulangerlivreur.fr/pages/account.php");
    exit();
}
$isnav = true;
$firstfield = "Redirection vers le paiement";
$firstref = "#";
$secondfield = "->";
$secondref = "#";
$title = "Redirection vers la page de paiement";
$toadd = "<script src=\"https://js.stripe.com/v3/\"></script>";
$toadd .= "<script src='../scripts/stripemanaging.js'></script>";
$toadd .= "<link rel='stylesheet' href='../styles/link.css'>";
include("../frags/fragHeader.php");

//On affiche la page et on fournit au script le sessionid.
?>
    <input type="hidden" id="sessionid" value="<?php echo $sessionid ?>">
    <img id="validator" src="../imgs/safe.svg">
    <p>
        Vous allez être redirigé vers la page de paiement sécurisée dans quelques instants.
    </p>
<?php include("../frags/fragFooter.php"); ?>