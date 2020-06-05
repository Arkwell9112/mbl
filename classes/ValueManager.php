<?php

class ValueManager
{
    public static function editValue(PDO $bdd, float $amount, String $username, array $content, String $secret)
    {
        $request = $bdd->prepare("LOCK TABLES users WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT value FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        if ($amount < 0 && $result[0]["value"] < $amount) {
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
                $request = $bdd->prepare("INSERT INTO operations (id, username, content, creationdate) VALUES (0, :username, :content, :creationdate)");
                $request->execute(array(
                    "username" => $username,
                    "content" => json_encode($content),
                    "creationdate" => time()
                ));
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