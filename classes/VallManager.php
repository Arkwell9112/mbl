<?php
require_once("/var/www/mbl/classes/MBLException.php");

class VallManager
{
    // Permet de modifier la value d'un utilisateur. Effectue aussi l'insertion de l'opération en fonction de content et de secret. Valide aussi la livraison si delivering est passé à true.
    public static function editValue(PDO $bdd, float $amount, string $username, array $content, string $secret, bool $delivering = false)
    {
        $request = $bdd->prepare("LOCK TABLES users WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT value FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        if ($amount < 0 && $result[0]["value"] < -$amount) {
            $request = $bdd->prepare("UNLOCK TABLES");
            $request->execute();
            throw new MBLException("badamount");
        } else {
            $value = $result[0]["value"] + $amount;
            try {
                $bdd->beginTransaction();
                $request = $bdd->prepare("UPDATE users SET value=:value WHERE username=:username");
                $request->execute(array(
                    "username" => $username,
                    "value" => $value
                ));
                $request = $bdd->prepare("INSERT INTO operations (id, username, content, creationdate, secret) VALUES (0, :username, :content, :creationdate, :secret)");
                $request->execute(array(
                    "username" => $username,
                    "content" => json_encode($content),
                    "creationdate" => time(),
                    "secret" => $secret
                ));
                if ($delivering) {
                    $request = $bdd->prepare("UPDATE users SET delivered=1 WHERE username=:username");
                    $request->execute(array(
                        "username" => $username
                    ));
                }
                $bdd->commit();
            } catch (Exception $e) {
                $bdd->rollBack();
                throw new MBLException("special");
            }
            $request = $bdd->prepare("UNLOCK TABLES");
            $request->execute();
        }
    }
}