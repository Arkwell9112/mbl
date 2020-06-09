<?php

use Stripe\Checkout\Session;
use Stripe\Stripe;

require("/var/www/mbl/vendor/autoload.php");
require("../classes/PDOManager.php");
require("../classes/ConnectionManager.php");
require("../classes/PayyManager.php");

if (isset($_GET["value"])) {
    if ($_GET["value"] == 20) {
        $amount = 20;
        $price = "price_1GrnTNHQXmOPYXA5okAzbWEV";
    } else if ($_GET["value"] == 40) {
        $amount = 20;
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
    $username = ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
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
?>
    <input type="hidden" id="sessionid" value="<?php echo $sessionid ?>">
    <img id="validator" src="../imgs/safe.svg">
    <p>
        Vous allez être redirigé vers la page de paiement sécurisée dans quelques instants.
    </p>
<?php include("../frags/fragFooter.php"); ?>