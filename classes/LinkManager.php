<?php


class LinkManager
{
    public static function checkLink(PDO $bdd, String $token): String
    {
        $request = $bdd->prepare("SELECT * FROM links WHERE token=:token");
        $request->execute(array(
            "token" => $token
        ));
        $result = $request->fetchAll();
        if (count($result) == 0) {
            throw new MBLException("badtoken");
        } else {
            if ($result[0]["username"] == "") {
                throw new MBLException("notlinked");
            } else {
                return $result[0]["username"];
            }
        }
    }

    public static function makeLink(PDO $bdd, String $token, String $username)
    {
        $request = $bdd->prepare("SELECT * FROM links WHERE token=:token");
        $request->execute(array(
            "token" => $token
        ));
        $result = $request->fetchAll();
        if (count($result) == 1) {
            if ($result[0]["username"] == "") {
                $request = $bdd->prepare("UPDATE links SET username=:username WHERE token=:token");
                $request->execute(array(
                    "username" => $username,
                    "token" => $token
                ));
            } else {
                throw new MBLException("yetlinked");
            }
        } else {
            throw new MBLException("badtoken");
        }
    }
}