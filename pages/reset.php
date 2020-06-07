<?php
$firstfield = "Se connecter";
$firstref = "../pages/signin.php";
$secondfield = "Accueil";
$secondref = "../pages/main.php";
$isnav = true;
$title = "Réinitialiser votre mot de passe";

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
        <form method="post" action="resetaction.php">
            <?php if (preg_match("#special#", $status)) include("../frags/fragErrorSpecial.php") ?>
            <?php if (preg_match("#badusername#", $status)) include("../frags/fragErrorUserNE.php") ?>
            <?php if (preg_match("#yes#", $status)) include("../frags/fragYesReset.php") ?>
            <input name="username" type="text" placeholder="Nom d'utilisateur"><br>
            <input type="submit" id="submit" value="Réinitialiser">
        </form>
    </div>
</article>
<?php include("../frags/fragFooter.php"); ?>
