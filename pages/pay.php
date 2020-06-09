<?php
if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    header("Location: https://monboulangerlivreur.fr/pages/account.php");
    exit();
}

if (preg_match("#yes#", $status)) {
    $src = "../imgs/tick.svg";
    $text = "Le paiement est validé vous allez être redirigé vers votre compte.";
} else {
    $src = "../imgs/cross.svg";
    $text = "Le paiement a échoué. Rien ne vous a été débité. Le paiement apparaîtra sous 48h dans vos opérations en tant que paiement échoué.";
}

$isnav = true;
$firstfield = "Redirection vers votre compte";
$firstref = "#";
$secondfield = "->";
$secondref = "#";
$toadd = "<link rel='stylesheet' href='../styles/link.css'>";
$toadd .= "<script src='../scripts/pay.js'></script>";
include("../frags/fragHeader.php");
?>
    <img id="validator" src="<?php echo $src ?>">
    <p>
        <?php echo $text ?>
    </p>
<?php include("../frags/fragFooter.php") ?>