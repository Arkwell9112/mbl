<?php


class IDManager
{
    public static function getSafeID(PDO $bdd): int
    {
        $request = $bdd->prepare("LOCK TABLES global WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT value FROM global WHERE label='id'");
        $request->execute();
        $result = $request->fetchAll();
        $id = $result[0]["value"];
        if ($id == PHP_INT_MAX) {
            $id = PHP_INT_MIN;
        } else {
            $id++;
        }
        $request = $bdd->prepare("UPDATE global SET value=:id WHERE label='id'");
        $request->execute(array(
            "id" => $id
        ));
        $request = $bdd->prepare("UNLOCK TABLES");
        $request->execute();
        return $id;
    }

    public static function getOwnID(PDO $bdd, string $username): int
    {
        $request = $bdd->prepare("LOCK TABLES accounts WRITE");
        $request->execute();
        $request = $bdd->prepare("SELECT ownid FROM accounts WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        $id = $result[0]["ownid"];
        if ($id == PHP_INT_MAX) {
            $id = PHP_INT_MIN;
        } else {
            $id++;
        }
        $request = $bdd->prepare("UPDATE accounts SET ownid=:idy WHERE username=:username");
        $request->execute(array(
            "idy" => $id,
            "username" => $username
        ));
        $request = $bdd->prepare("UNLOCK TABLES");
        $request->execute();
        return $id;
    }
}