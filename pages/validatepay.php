<?php

use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

// Webhook endpoint pour la validation par stripe des paiements.

require("/var/www/mbl/vendor/autoload.php");
require_once("../classes/PDOManager.php");
require_once("../classes/PayyManager.php");

Stripe::setApiKey('sk_test_51Grjv6HQXmOPYXA5HCeZONczTbtuCTNFJBpqC3wvjb9ksXEpuv0hXoIXLD6sSXYZHUNZ4wiSLknC8mvH6b925Fje00WEBL44ua');

$endpoint_secret = 'whsec_nOTeDI9rIIhOkZXHLpaINQRp4fjF7ryy';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
    $event = Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch (UnexpectedValueException $e) {
    http_response_code(400);
    exit();
} catch (SignatureVerificationException $e) {
    http_response_code(400);
    exit();
}

if ($event->type == 'checkout.session.completed') {
    // Récupèration de l'objet session pour obtenir l'id.
    $session = $event->data->object;
    $sessionid = $session->id;

    try {
        // On valide le paiement pour cet ID.
        $bdd = PDOManager::getPDO();
        PayyManager::validatePayy($bdd, $sessionid);
    } catch (Exception $e) {
        // En cas d'erreur on renvoit un code d'erreur et on affiche l'erreur pour pouvoir la consulter via Stripe.
        echo $e->getMessage();
        http_response_code(400);
    }
}

http_response_code(200);