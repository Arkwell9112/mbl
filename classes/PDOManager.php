<?php


class PDOManager
{
    private const username = "root";
    private const passwd = "11022000";
    private const dsn = "mysql:host=localhost;port=3308;dbname=mbl";
    private const admins = "Arkwell9112";

    public static function getPDO() : PDO {
        $bdd = new PDO(self::dsn, self::username, self::passwd);
        $bdd->setAttribute(PDo::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $bdd;
    }

    public static function checkAdmin(String $isadmin) : bool {
        if(preg_match("#^$isadmin$#", self::admins)) {
            return true;
        } else {
            return false;
        }
    }
}