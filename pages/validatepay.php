<?php

use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

require("/var/www/mbl/vendor/autoload.php");
include("../classes/MBLException.php");
include("../classes/PayyManager.php");
include("../classes/PDOManager.php");

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
    $session = $event->data->object;
    $sessionid = $session->id;

    try {
        $bdd = PDOManager::getPDO();
        PayyManager::validatePayy($bdd, $sessionid);
    } catch (Exception $e) {
        echo $e->getMessage();
        http_response_code(400);
    }
}

http_response_code(200);