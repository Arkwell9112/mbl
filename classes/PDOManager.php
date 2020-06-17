<?php


class PDOManager
{
    //Informations pour la connexion à la base de donnée.
    private const username = "admin";
    private const passwd = "LilPump9112Zero";
    private const dsn = "mysql:host=localhost;port=3306;dbname=mbl";
    //Pseudo de l'utilisateur administrateur.
    private const admins = "Edouard";

    // Permet l'obtention d'un PDO à partir les informations de connexion.
    public static function getPDO(): PDO
    {
        $bdd = new PDO(self::dsn, self::username, self::passwd);
        $bdd->setAttribute(PDo::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $bdd;
    }

    // Permet de tester si un utilisateur est administrateur.
    public static function checkAdmin(string $isadmin): bool
    {
        if (preg_match("#^$isadmin$#", self::admins)) {
            return true;
        } else {
            return false;
        }
    }
}