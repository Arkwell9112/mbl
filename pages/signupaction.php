<?php
require_once("../classes/PDOManager.php");
require_once("../classes/AccountManager.php");
require_once("../classes/IDManager.php");

// Page de traitement et d'affichage pour la création de compte utilisateur.

$firstfield = "Se connecter";
$firstref = "../pages/signin.php";
$secondfield = "Accueil";
$secondref = "../pages/main.php";

$isnav = true;
$title = "S'inscrire";

session_start();

if (isset($_POST["validation"])) {
    // Si le compte est en cour de création on regarde si la personne accepte l'adresse proposée.
    if ($_POST["validation"] == "yes") {
        try {
            // Si oui on crée le compte
            $bdd = PDOManager::getPDO();
            AccountManager::createAccount($bdd, $_SESSION["username"], $_SESSION["passwd1"], $_SESSION["passwd2"], $_SESSION["mail"], $_SESSION["phone"], $_SESSION["city"], $_SESSION["address"], $_SESSION["geocode"]);
            for ($i = 0; $i <= count($_SESSION); $i++) {
                unset($_SESSION[$i]);
            }
            header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=yes");
        } catch (Exception $e) {
            header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=special");
        }
    } else {
        // Sinon on détruit les données pré enregistrées et on renvoit vers la page de création de compte.
        for ($i = 0; $i <= count($_SESSION); $i++) {
            unset($_SESSION[$i]);
        }
        $username = $_SESSION["username"];
        $mail = $_SESSION["mail"];
        $phone = $_SESSION["phone"];
        $address = $_SESSION["address"];
        header("Location: https://monboulangerlivreur.fr/pages/signup.php?username=$username&mail=$mail&phone=$phone&address=$address");
    }
    // Si le compte n'est pas encore en cour de création on le met en cour de création et on affiche la validation de l'adresse.
} else {
    try {
        $bdd = PDOManager::getPDO();
        // On vérifie que tout ce qui est entré est correct.
        AccountManager::checkErrors($bdd, $_POST["username"], $_POST["passwd1"], $_POST["passwd2"], $_POST["mail"], $_POST["phone"], $_POST["city"]);
        $address = $_POST["address"] . "," . $_POST["city"] . "," . "France";
        $address = urlencode($address);
        $openkey = IDManager::getOpenStreetKey();
        // On récupère longitude et latitude
        $geocoderequest = "https://maps.open-street.com/api/geocoding/?address=$address&key=$openkey";
        $response = file_get_contents($geocoderequest);
        $response = json_decode($response, true);
        // Si l'appel vers l'API est OK on met le compte en cour de création.
        if ($response["status"] == "OK") {
            $formattedaddress = $response["results"][0]["formatted_address"];
            $address = explode(",", $formattedaddress);
            $address = $address[0];
            $geocode = $response["results"][0]["geometry"]["location"]["lat"] . "," . $response["results"][0]["geometry"]["location"]["lng"];
            foreach ($_POST as $key => $variable) {
                $_SESSION[$key] = $variable;
            }
            $_SESSION["address"] = $address;
            $_SESSION["geocode"] = $geocode;
            // Si tout va bien on ne resirige pas pour laisser la validation de l'adresse postale appraître.
        } else {
            throw new MBLException("badaddress");
        }
    } catch (MBLException $e) {
        // En cas de soucis avec les données du compte on redirige vers la page de création de compte
        $status = $e->getMessage();
        $username = $_POST["username"];
        $mail = $_POST["mail"];
        $phone = $_POST["phone"];
        $address = $_POST["address"];
        header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=$status&username=$username&mail=$mail&phone=$phone&address=$address");
    } catch (Exception $e) {
        // En cas d'erreur d'un autre type on redirige avec un statut erreur en cour.
        header("Location: https://monboulangerlivreur.fr/pages/signup.php?status=special");
    }
}
include("../frags/fragHeader.php");

// Après avoir mis le compte en cour de création on affiche la validation de l'adresse postale.
?>
    <article id="firstarticle">
        <div class="title">
            <img src="../imgs/datas.svg">
            <h3>S'inscrire</h3>
        </div>
        <div class="signform" id="toreplaceform">
            <form method="post" action="signupaction.php">
                <?php
                echo "<span class='tounderline'>$formattedaddress</span><br><br>";
                ?>
                <span>Est-ce bien votre adresse ?</span><br><br>
                <select class="cityselect" name="validation">
                    <option value="yes">Oui</option>
                    <option value="no">Non</option>
                </select><br><br>
                <input type="submit" value="Valider">
            </form>
        </div>
    </article>
<?php
include("../frags/fragFooter.php");
?>