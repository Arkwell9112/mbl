<?php
require_once("/var/www/mbl/classes/MBLException.php");
require_once("/var/www/mbl/classes/VallManager.php");

class PayyManager
{
    // Permet l'insertion d'un nouveau paiement dans payments.
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

    // Permet la validation d'un paiement. Avec insertion de l'opération et modification de la value de l'utilisateur. Puis supprime le paiement en question.
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
                        "Montant" => number_format($result[0]["value"], 2) . "€"
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

    // Permet de rejeter un paiement (Quand non validé depuis plus de 24H). Effectue la suppression et l'insertion de l'opération d'échec.
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
                        "Montant" => number_format($result[0]["value"], 2) . "€"
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