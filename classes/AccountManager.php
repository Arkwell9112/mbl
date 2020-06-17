<?php
require_once("/var/www/mbl/classes/IDManager.php");
require_once("/var/www/mbl/classes/MBLException.php");


class AccountManager
{
    // Temps avant qu'un reset ne soit invalide.
    public const resetTime = 2 * 3600;
    // Temps avant qu'un compte non activé ne soit invalide.
    public const activeTime = 24 * 3600;

    // Permet de vérifier que les informations fournies pour la création d'un compte sont valables.
    public static function checkErrors(PDO $bdd, string $username, string $passwd, string $passwd2, string $mail, string $phone, string $city)
    {
        $request = $bdd->prepare("SELECT username FROM accounts WHERE username=:name");
        $request->execute(array(
            "name" => $username
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            throw new MBLException("usernameexists");
        }
        $request = $bdd->prepare("SELECT username FROM accounts WHERE mail=:email");
        $request->execute(array(
            "email" => $mail
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            throw new MBLException("mailexists");
        }
        $request = $bdd->prepare("SELECT username FROM users WHERE phone=:tel");
        $request->execute(array(
            "tel" => $phone
        ));
        if (count($result) == 1) {
            throw new MBLException("phoneexists");
        }
        $mail = htmlspecialchars($mail);
        $error = "";
        if (!preg_match("#^[A-Za-z0-9]{4,}$#", $username)) {
            $error = $error . "badusername";
        }
        if (strlen($passwd) < 8 || !preg_match("#[0-9]#", $passwd) || !preg_match("#[A-Z]#", $passwd) || !preg_match("#[a-z]#", $passwd) || !preg_match("#[^A-Za-z0-9]#", $passwd)) {
            $error = $error . "badpasswd";
        }
        if ($passwd != $passwd2) {
            $error = $error . "diffpasswd";
        }
        if ($mail == filter_var($mail, FILTER_FLAG_EMAIL_UNICODE)) {
            $error = $error . "badmail";
        }
        if (!preg_match("#^0[0-9]{9}$#", $phone)) {
            $error = $error . "badphone";
        }
        $request = $bdd->prepare("SELECT name FROM cities WHERE name=:city");
        $request->execute(array(
            "city" => $city
        ));
        $result = $request->fetchAll();
        if (!count($result) == 1) {
            $error = $error . "badcity";
        }
        if ($error != "") {
            throw new MBLException($error);
        }
    }

    // Permet la création d'un compte utilisateur. Envoit en même temps le mail de vérification de l'adresse mail.
    public static function createAccount(PDO $bdd, string $username, string $passwd, string $passwd2, string $mail, string $phone, string $city, string $address, string $geocode)
    {
        try {
            $bdd->beginTransaction();
            $request = $bdd->prepare("INSERT INTO users (username, city, phone, address, geocode) VALUES (:username, :city, :phone, :address, :geocode)");
            $request->execute(array(
                "username" => $username,
                "city" => $city,
                "phone" => $phone,
                "address" => $address,
                "geocode" => $geocode
            ));
            $ownid = random_int(PHP_INT_MIN, PHP_INT_MAX);
            $safeid = IDManager::getSafeID($bdd);
            $verificationid = password_hash($safeid, PASSWORD_DEFAULT);
            $request = $bdd->prepare("INSERT INTO accounts (username, hashword, verificationid, safeid, ownid, creationdate, mail) VALUES (:username, :hashword, :verificationid, :safeid, :ownid, :creationdate, :mail)");
            $request->execute(array(
                "username" => $username,
                "hashword" => password_hash($passwd, PASSWORD_DEFAULT),
                "verificationid" => $verificationid,
                "safeid" => $safeid,
                "ownid" => $ownid,
                "creationdate" => time(),
                "mail" => $mail
            ));
            $header = array(
                "MIME-Version" => "1.0",
                "Content-type" => "text/html; charset=UTF-8",
                "From" => "MonBoulangerLivreur.fr <no-reply@monboulangerlivreur.fr>",
                "Reply-To" => "contact@monboulangerlivreur.fr",
                "X-Mailer" => "PHP/" . phpversion()
            );
            $subject = "Activation de votre compte MonBoulangerLivreur.fr";
            $message = file_get_contents("../frags/fragMailActivation+.html", false);
            $message = str_replace("&amp;username&amp;", $username, $message);
            $message = str_replace("+token+", $verificationid, $message);
            $message = wordwrap($message, 70, "\r\n");
            mail($mail, $subject, $message, $header);
            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw new MBLException("special");
        }
    }

    // Permet la création d'une entrée resets dans la base de donnée. Crée le token et envoit le mail de réinitialisation du mot de passe.
    public static function setPasswdReset(PDO $bdd, string $username)
    {
        $request = $bdd->prepare("SELECT * FROM accounts WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            $mail = $result[0]["mail"];
            $currentid = IDManager::getOwnID($bdd, $username) . $result[0]["safeid"];
            $token = password_hash($currentid, PASSWORD_DEFAULT);
            $request = $bdd->prepare("SELECT * FROM resets WHERE username=:username");
            $request->execute(array(
                "username" => $username
            ));
            $result = $request->fetchAll();
            if (count($result) == 1) {
                $request = $bdd->prepare("UPDATE resets SET token=:token, creationdate=:creationdate WHERE username=:username");
                $request->execute(array(
                    "token" => $token,
                    "creationdate" => time(),
                    "username" => $username
                ));
            } else {
                $request = $bdd->prepare("INSERT INTO resets (username, token, creationdate) VALUES (:username, :token, :creationdate)");
                $request->execute(array(
                    "username" => $username,
                    "token" => $token,
                    "creationdate" => time()
                ));
            }
            $header = array(
                "MIME-Version" => "1.0",
                "Content-type" => "text/html; charset=UTF-8",
                "From" => "MonBoulangerLivreur.fr <no-reply@monboulangerlivreur.fr>",
                "Reply-To" => "contact@monboulangerlivreur.fr",
                "X-Mailer" => "PHP/" . phpversion()
            );
            $subject = "Réinitialisation de votre mot de passe";
            $message = file_get_contents("../frags/fragMailReset.html");
            $message = str_replace("+token+", $token, $message);
            $message = wordwrap($message, 70, "\r\n");
            mail($mail, $subject, $message, $header);
        } else {
            throw new MBLException("badusername");
        }
    }

    //Permet le remplacement du mot de passe. Vérifie la cohérence des informations et la validité du token ainsi que la validité de temps de l'entrée resets.
    public static function resetPasswd(PDO $bdd, string $token, string $passwd, string $passwd2)
    {
        if ($passwd == $passwd2) {
            if (strlen($passwd) < 8 || !preg_match("#[0-9]#", $passwd) || !preg_match("#[A-Z]#", $passwd) || !preg_match("#[a-z]#", $passwd) || !preg_match("#[^A-Za-z0-9]#", $passwd)) {
                throw new MBLException("badpasswd");
            } else {
                $request = $bdd->prepare("SELECT * FROM resets WHERE token=:token");
                $request->execute(array(
                    "token" => $token
                ));
                $result = $request->fetchAll();
                $timeok = false;
                if (isset($result[0]["creationdate"])) {
                    if (time() - $result[0]["creationdate"] < self::resetTime) {
                        $timeok = true;
                    }
                }
                if (count($result) == 1 && $timeok) {
                    $request = $bdd->prepare("UPDATE accounts SET hashword=:hash WHERE username=:username");
                    $request->execute(array(
                        "hash" => password_hash($passwd, PASSWORD_DEFAULT),
                        "username" => $result[0]["username"]
                    ));
                    $request = $bdd->prepare("DELETE FROM resets WHERE token=:token");
                    $request->execute(array(
                        "token" => $token
                    ));
                } else {
                    $request = $bdd->prepare("DELETE FROM resets WHERE token=:token");
                    $request->execute(array(
                        "token" => $token
                    ));
                    throw new MBLException("badtoken");
                }
            }
        } else {
            throw new MBLException("diffpasswd");
        }
    }

    // Permet l'activation d'un compte à partir de son verificationid.
    public static function activeAccount(PDO $bdd, string $verificationid)
    {
        $request = $bdd->prepare("SELECT username FROM accounts WHERE verificationid=:id");
        $request->execute(array(
            "id" => $verificationid
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            $request = $bdd->prepare("UPDATE accounts SET active=1 WHERE verificationid=:id");
            $request->execute(array(
                "id" => $verificationid
            ));
        } else {
            throw new MBLException("badtoken");
        }
    }
}
