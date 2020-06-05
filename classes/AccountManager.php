<?php
include("MBLException.php");
include("IDManager.php");

class AccountManager
{
    public const resetTime = 2 * 3600;

    public static function createAccount(PDO $bdd, String $username, String $passwd, String $passwd2, String $mail, String $phone, String $city) {
        $request = $bdd->prepare("SELECT username FROM accounts WHERE username=:name");
        $request->execute(array(
            "name" => $username
        ));
        $result = $request->fetchAll();
        if(count($result) == 1) {
            throw new MBLException("usernameexists");
        }
        $request = $bdd->prepare("SELECT username FROM accounts WHERE mail=:email");
        $request->execute(array(
            "email" => $mail
        ));
        $result = $request->fetchAll();
        if(count($result) == 1) {
            throw new MBLException("mailexists");
        }
        $request = $bdd->prepare("SELECT username FROM users WHERE phone=:tel");
        $request->execute(array(
            "tel" => $phone
        ));
        if(count($result) == 1) {
            throw new MBLException("phoneexists");
        }
        $mail = htmlspecialchars($mail);
        $error = "";
        if(!preg_match("#^[A-Za-z0-9]{4,}$#", $username)) {
            $error = $error . "badusername";
        }
        if(strlen($passwd) < 8 || !preg_match("#[0-9]#", $passwd) || !preg_match("#[A-Z]#", $passwd) || !preg_match("#[a-z]#", $passwd) || !preg_match("#[^A-Za-z0-9]#", $passwd)) {
            $error = $error . "badpasswd";
        }
        if($passwd != $passwd2) {
            $error = $error . "diffpasswd";
        }
        if($mail == filter_var($mail, FILTER_FLAG_EMAIL_UNICODE)) {
            $error = $error . "badmail";
        }
        if(!preg_match("#^0[0-9]{9}$#", $phone)) {
            $error = $error . "badphone";
        }
        $request = $bdd->prepare("SELECT name FROM cities WHERE name=:city");
        $request->execute(array(
            "city" => $city
        ));
        $result = $request->fetchAll();
        if(!count($result) == 1) {
            $error = $error . "badcity";
        }
        if($error != "") {
            throw new MBLException($error);
        }
        try {
            $bdd->beginTransaction();
            $request = $bdd->prepare("INSERT INTO users (username, city, phone) VALUES (:username, :city, :phone)");
            $request->execute(array(
                "username" => $username,
                "city" => $city,
                "phone" => $phone
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
            //Add Mail verification needed !
            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw new MBLException("special");
        }
    }

    public static function setPasswdReset(PDO $bdd, String $username) {
        $request = $bdd->prepare("SELECT * FROM accounts WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        if(count($result) == 1) {
            $currentid = IDManager::getOwnID($bdd, $username) . $result[0]["safeid"];
            $token = password_hash($currentid, PASSWORD_DEFAULT);
            $request = $bdd->prepare("SELECT * FROM resets WHERE username=:username");
            $request->execute(array(
                "username" => $username
            ));
            $result = $request->fetchAll();
            if(count($result) == 1) {
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
            //Add mail sending
            }
        } else {
            throw new MBLException("badusername");
        }
    }

    public static function resetPasswd(PDO $bdd, String $token, String $passwd, String $passwd2) {
        if($passwd == $passwd2) {
            if(strlen($passwd) < 8 || !preg_match("#[0-9]#", $passwd) || !preg_match("#[A-Z]#", $passwd) || !preg_match("#[a-z]#", $passwd) || !preg_match("#[^A-Za-z0-9]#", $passwd)) {
                throw new MBLException("badpasswd");
            } else {
                $request = $bdd->prepare("SELECT * FROM resets WHERE token=:token");
                $request->execute(array(
                    "token" => $token
                ));
                $result = $request->fetchAll();
                $timeok = false;
                if(isset($result[0]["creationdate"])) {
                    if(time() - $result[0]["creationdate"] < self::resetTime) {
                        $timeok = true;
                    }
                }
                if(count($result) == 1 && $timeok) {
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

    public static function activeAccount(PDO $bdd, String $verificationid) {
        $request = $bdd->prepare("SELECT username FROM accounts WHERE verificationid=:id");
        $request->execute(array(
            "id" => $verificationid
        ));
        $result = $request->fetchAll();
        if(count($result) == 1) {
            $request = $bdd->prepare("UPDATE accounts SET active=1 WHERE verificationid=:id");
            $request->execute(array(
                "id" => $verificationid
            ));
        } else {
            throw new MBLException("badtoken");
        }
    }
}