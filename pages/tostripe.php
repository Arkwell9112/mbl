<?php
require("/var/www/mbl/vendor/autoload.php");
try {
    \Stripe\Stripe::setApiKey('sk_test_51Grjv6HQXmOPYXA5HCeZONczTbtuCTNFJBpqC3wvjb9ksXEpuv0hXoIXLD6sSXYZHUNZ4wiSLknC8mvH6b925Fje00WEBL44ua');

    $session = \Stripe\Checkout\Session::create(array(
        "payment_method_types" => array("card"),
        "line_items" => array(array(
            "price" => "price_1Grl9nHQXmOPYXA5IZXUNAky",
            "quantity" => 1
        )),
        "mode" => "payment",
        "success_url" => "https://monboulangerlivreur.fr/pages/pay.php?status=yes",
        "cancel_url" => "https://monboulangerlivreur.fr/pages/pay.php?status=no"
    ));
} catch (Exception $e) {
    $e->getMessage();
    ob_flush();
}
$title = "Redirection vers la page de paiement";
$toadd = "<script src=\"https://js.stripe.com/v3/\"></script>";
$toadd .= "<script src='../scripts/stripemanaging.js'></script>";
$toadd .= "<link rel='stylesheet' href='../styles/link.css'>";
include("../frags/fragHeader.php");
?>
<input type="hidden" id="sessionid" value="<?php echo $session ?>">
<img id="validator" src="../imgs/safe.svg">
<p>
    Vous allez être redirigé vers la page de paiement sécurisée dans quelques instants.
</p>
<?php include("../frags/fragFooter.php"); ?>
