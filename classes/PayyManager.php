<?php
require_once("/var/www/mbl/classes/MBLException.php");
require_once("/var/www/mbl/classes/VallManager.php");

class PayyManager
{
    public static function initPayy(PDO $bdd, string $sessionid, string $username, float $value)
    {
        $request = $bdd->prepare("INSERT INTO payments VALUES (:sessionid, :username, :money, :timing)");
        $request->execute(array(
            "sessionid" => $sessionid,
            "username" => $username,
            "money" => $value,
            "timing" => time()
        ));
    }

    public static function validatePayy(PDO $bdd, string $sessionid)
    {
        $request = $bdd->prepare("SELECT * FROM payments WHERE sessionid=:sessionid");
        $request->execute(array(
            "sessionid" => $sessionid
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            try {
                $content = array(
                    "title" => "Rechargement en ligne",
                    "content" => array(
                        "Montant" => $result[0]["value"]
                    )
                );
                VallManager::editValue($bdd, $result[0]["value"], $result[0]["username"], $content, $result[0]["sessionid"]);
                $request = $bdd->prepare("DELETE FROM payments WHERE sessionid=:sessionid");
                $request->execute(array(
                    "sessionid" => $sessionid
                ));
            } catch (Exception $e) {
                throw new MBLException("special");
            }
        } else {
            throw new MBLException("badsession");
        }
    }

    public static function rejectPayy(PDO $bdd, string $sessionid)
    {
        $request = $bdd->prepare("SELECT * FROM payments WHERE sessionid=:sessionid");
        $request->execute(array(
            "sessionid" => $sessionid
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            try {
                $bdd->beginTransaction();
                $content = array(
                    "title" => "Rechargement en ligne : échec",
                    "content" => array(
                        "Montant" => $result[0]["value"]
                    )
                );
                $request = $bdd->prepare("INSERT INTO operations VALUES (0, :username, :content, :secret, :creationdate)");
                $request->execute(array(
                    "username" => $result[0]["username"],
                    "content" => json_encode($content),
                    "secret" => $sessionid,
                    "creationdate" => time()
                ));
                $request = $bdd->prepare("DELETE FROM payments WHERE sessionid=:sessionid");
                $request->execute(array(
                    "sessionid" => $sessionid
                ));
                $bdd->commit();
            } catch (Exception $e) {
                $bdd->rollBack();
                throw new MBLException("special");
            }
        } else {
            throw new MBLException("badsession");
        }
    }
}