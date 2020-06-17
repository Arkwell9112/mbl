<?php
require_once("/var/www/mbl/classes/MBLException.php");
require_once("/var/www/mbl/classes/IDManager.php");


class ConnectionManager
{
    // Temps avant que la connexion ne soit plus valide.
    public const connectionTime = 3600 * 24 * 3;

    // Permet la création d'une connexion à partir d'un nom d'utilisateur et d'un mot de passe.
    public static function connectWithPasswd(PDO $bdd, string $username, string $passwd): string
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

    // Permet la récupération d'une coonnexion à partir de son token. Si le temps n'est plus valide la connexion est supprimée.
    public static function connectWithToken(PDO $bdd, string $token): string
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

    // Permet la suppression d'une connexion par son token. Vérifie quand même le temps et renvoit une erreur si le temps est invalide.
    public static function disconnectWithToken(PDO $bdd, string $token)
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