<?php
include("../classes/IDManager.php");
include("../classes/MBLException.php");

class ConnectionManager
{
    public const connectionTime = 3600 * 24 * 3;

    public static function connectWithPasswd(PDO $bdd, String $username, String $passwd): String
    {
        $request = $bdd->prepare("SELECT username,hashword,safeid,active FROM accounts WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            if ($result[0]["active"]) {
                if (password_verify($passwd, $result[0]["hashword"])) {
                    $currentid = IDManager::getOwnID($bdd, $username) . $result[0]["safeid"];
                    $token = password_hash($currentid, PASSWORD_DEFAULT);
                    $request = $bdd->prepare("SELECT * FROM connections WHERE username=:username");
                    $request->execute(array(
                        "username" => $username
                    ));
                    $result = $request->fetchAll();
                    if (count($result) == 1) {
                        $request = $bdd->prepare("UPDATE connections SET token=:token, creationdate=:creationdate WHERE username=:username");
                        $request->execute(array(
                            "token" => $token,
                            "creationdate" => time(),
                            "username" => $username
                        ));
                    } else {
                        $request = $bdd->prepare("INSERT INTO connections (token, username, creationdate) VALUES (:token, :username, :creationdate)");
                        $request->execute(array(
                            "token" => $token,
                            "username" => $username,
                            "creationdate" => time()
                        ));
                    }
                    return $token;
                } else {
                    throw new MBLException("badpasswd");
                }
            } else {
                throw new MBLException("notactive");
            }
        } else {
            throw new MBLException("badusername");
        }
    }

    public static function connectWithToken(PDO $bdd, String $token): String
    {
        $request = $bdd->prepare("SELECT * FROM connections WHERE token=:token");
        $request->execute(array(
            "token" => $token
        ));
        $result = $request->fetchAll();
        $timeok = false;
        if (isset($result[0]["creationdate"])) {
            if (time() - $result[0]["creationdate"] < self::connectionTime) {
                $timeok = true;
            }
        }
        if (count($result) == 1 && $timeok) {
            return $result[0]["username"];
        } else {
            $request = $bdd->prepare("DELETE FROM connections WHERE token=:token");
            $request->execute(array(
                "token" => $token
            ));
            throw new MBLException("badtoken");
        }
    }

    public static function disconnectWithToken(PDO $bdd, String $token)
    {
        $request = $bdd->prepare("SELECT * FROM connections WHERE token=:token");
        $request->execute(array(
            "token" => $token
        ));
        $result = $request->fetchAll();
        $timeok = false;
        if (isset($result[0]["creationdate"])) {
            if (time() - $result[0]["creationdate"] < self::connectionTime) {
                $timeok = true;
            }
        }
        if (count($result) == 1 && $timeok) {
            $request = $bdd->prepare("DELETE FROM connections WHERE token=:token");
            $request->execute(array(
                "token" => $token
            ));
        } else {
            $request = $bdd->prepare("DELETE FROM connections WHERE token=:token");
            $request->execute(array(
                "token" => $token
            ));
            throw new MBLException("badtoken");
        }
    }
}