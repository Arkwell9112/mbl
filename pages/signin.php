<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");

$firstfield = "S'inscrire";
$firstref = "../pages/signup.php";
$secondfield = "Accueil";
$secondref = "../pages/main.php";

// Page pour l'affichage de la connexion à l'espace membre.

$isnav = true;
$title = "Se connecter";

if (isset($_COOKIE["token"])) {
    try {
        $bdd = PDOManager::getPDO();
        ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
        header("Location: https://monboulangerlivreur.fr/pages/account.php");
    } catch (Exception $e) {

    }
}

if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    $status = "";
}
include("../frags/fragHeader.php");
?>
<article id="firstarticle">
    <div class="title">
        <img src="../imgs/lock.svg">
        <h3>Se connecter</h3>
    </div>
    <div class="signform" id="toreplaceform">
        <form method="post" action="signinaction.php">
            <?php
            if (isset($_GET["backtrace"])) {
                $backtrace = $_GET["backtrace"];
                echo "<input type='hidden' name='backtrace' value='$backtrace'>";
            }
            ?>
            <?php if (preg_match("#badpasswd#", $status) || preg_match("#badusername#", $status)) include("../frags/fragErrorPassAndUser.php") ?>
            <?php if (preg_match("#special#", $status)) include("../frags/fragErrorSpecial.php") ?>
            <?php if (preg_match("#notactive#", $status)) include("../frags/fragErrorSigninVerification.php") ?>
            <?php if (preg_match("#yesactive#", $status)) include("../frags/fragYesVerification.php") ?>
            <?php if (preg_match("#badtoken#", $status)) include("../frags/fragErrorVerification.php") ?>
            <input name="username" type="text" placeholder="Nom d'utilisateur"><br>
            <input name="passwd" type="password" placeholder="Mot de passe"><br>
            <input id="submit" type="submit" value="Connexion">
        </form>
        <a class="bottomlink" href="reset.php">Mot de passe oublié ?</a>
    </div>
</article>
<?php include("../frags/fragFooter.php"); ?>
