<?php
require_once("../classes/PDOManager.php");
require_once("../classes/ConnectionManager.php");

$firstfield = "Se connecter";
$firstref = "../pages/signin.php";
$secondfield = "Accueil";
$secondref = "../pages/main.php";

// Page pour l'affichage de la création de compte utilisateur.

$isnav = true;
$title = "S'inscrire";

if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    $status = "";
}

try {
    $bdd = PDOManager::getPDO();
    $request = $bdd->prepare("SELECT name FROM cities");
    $request->execute();
    $result = $request->fetchAll();
} catch (Exception $e) {
    $status = $status . "special";
}

if (isset($bdd) && isset($_COOKIE["token"])) {
    try {
        // Si déjà connecté on redirige vers la page account.php.
        ConnectionManager::connectWithToken($bdd, $_COOKIE["token"]);
        header("Location: https://monboulangerlivreur.fr/pages/account.php");
    } catch (Exception $e) {

    }
}
include("../frags/fragHeader.php");
?>
<article id="firstarticle">
    <div class="title">
        <img src="../imgs/datas.svg">
        <h3>S'inscrire</h3>
    </div>
    <div class="signform" id="toreplaceform">
        <?php if (preg_match("#special#", $status)) include("../frags/fragErrorSpecial.php") ?>
        <?php if (preg_match("#yes#", $status)) include("../frags/fragYesSignup.php") ?>
        <form method="post" action="signupaction.php">
            <?php if (preg_match("#badusername#", $status)) include("../frags/fragErrorUsername.php") ?>
            <?php if (preg_match("#usernameexists#", $status)) include("../frags/fragInfoUsername.php") ?>
            <input value="<?php if (isset($_GET["username"])) echo $_GET["username"] ?>" type="text" name="username"
                   placeholder="Nom d'utilisateur"><br>
            <?php if (preg_match("#badpasswd#", $status)) include("../frags/fragErrorPasswd.php") ?>
            <?php if (preg_match("#diffpasswd#", $status)) include("../frags/fragInfoDiff.php") ?>
            <input type="password" name="passwd1" placeholder="Mot de passe"><br>
            <input type="password" name="passwd2" placeholder="Répétez le mot de passe"><br>
            <?php if (preg_match("#badmail#", $status)) include("../frags/fragErrorMail.php") ?>
            <?php if (preg_match("#mailexists#", $status)) include("../frags/fragInfoMail.php") ?>
            <input value="<?php if (isset($_GET["mail"])) echo $_GET["mail"] ?>" type="email" name="mail"
                   placeholder="Adresse e-mail"><br>
            <?php if (preg_match("#phoneexists#", $status)) include("../frags/fragInfoPhone.php") ?>
            <?php if (preg_match("#badphone#", $status)) include("../frags/fragErrorPhone.php") ?>
            <input value="<?php if (isset($_GET["phone"])) echo $_GET["phone"] ?>" type="tel" name="phone"
                   placeholder="Numéro de téléphone"><br>
            <input value="<?php if (isset($_GET["address"])) echo $_GET["address"] ?>" placeholder="N° et nom de rue"
                   type="text" name="address">
            <?php if (preg_match("#badcity#", $status)) include("../frags/fragErrorCity.php") ?>
            <select class="cityselect" name="city">
                <option value="none">Sélectionnez votre village de résidence</option>
                <?php
                if (isset($result)) {
                    foreach ($result as $value) {
                        $option = $value['name'];
                        echo "<option value='$option'>$option</option>";
                    }
                }
                ?>
            </select><br>
            <input id="submit" type="submit" value="Inscription">
        </form>
        <a class="bottomlink" href="signin.php">Déjà inscrit ? Connectez-vous !</a>
    </div>
</article>
<?php include("../frags/fragFooter.php") ?>
