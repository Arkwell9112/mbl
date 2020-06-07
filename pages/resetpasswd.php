<?php
$firstfield = "Se connecter";
$firstref = "../pages/signin.php";
$secondfield = "Accueil";
$secondref = "../pages/main.php";
$isnav = true;
$title = "Réinitialiser votre mot de passe";

if (!isset($_GET["token"])) {
    header("Location: https://monboulangerlivreur.fr/pages/main.php");
}

$hiddenvalue = $_GET["token"];

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
            <h3>Réinitialiser votre mot de passe</h3>
        </div>
        <div class="signform" id="toreplaceform">
            <form method="post" action="resetpasswdaction.php">
                <?php if (preg_match("#special#", $status)) include("../frags/fragErrorSpecial.php") ?>
                <?php if (preg_match("#badpasswd#", $status)) include("../frags/fragErrorPasswd.php") ?>
                <?php if (preg_match("#yes#", $status)) include("../frags/fragYesResetPasswd.php") ?>
                <input type="hidden" name="token" value="<?php echo $hiddenvalue ?>">
                <input name="passwd" type="password" placeholder="Nouveau mot de passe"><br>
                <input name="passwd2" type="password" placeholder="Répétez nouveau mot de passe"><br>
                <input type="submit" id="submit" value="Réinitialiser">
            </form>
        </div>
    </article>
<?php include("../frags/fragFooter.php");