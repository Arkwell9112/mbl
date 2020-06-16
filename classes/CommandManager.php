<?php
require_once("../classes/WeekDay.php");
require_once("../classes/MBLException.php");


class CommandManager
{
    public static function editCommand(PDO $bdd, string $product, array $amounts, string $username)
    {
        $goods = 0;
        for ($i = 0; $i <= 6; $i++) {
            if (isset($amounts[$i])) {
                if (preg_match("#^[0-9]+$#", $amounts[$i])) {
                    $goods++;
                }
            }
        }
        if ($goods != 7) {
            throw new MBLException("badamounts");
        }
        $request = $bdd->prepare("SELECT command,city FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        $command = json_decode($result[0]["command"], true);
        /* 24h Avance Check
        if (isset($command[$product])) {
            if ($command[$product][(WeekDay::getDay() + 1) % 7] != $amounts[(WeekDay::getDay() + 1) % 7]) {
                throw new MBLException("24h");
            }
        }
        */
        if (isset($command[$product])) {
            if ($command[$product][(WeekDay::getDay()) % 7] != $amounts[(WeekDay::getDay()) % 7]) {
                throw new MBLException("24h");
            }
        }
        $city = $result[0]["city"];
        $request = $bdd->prepare("SELECT name FROM products");
        $request->execute();
        $result = $request->fetchAll();
        $found = false;
        foreach ($result as $value) {
            if ($value["name"] == $product) {
                $found = true;
            }
        }
        $request = $bdd->prepare("SELECT * FROM cities WHERE name=:name");
        $request->execute(array(
            "name" => $city
        ));
        $result = $request->fetchAll();
        if ($found) {
            if (!isset($command)) {
                $command = array();
            }
            if (!isset($command[$product])) {
                $command[$product] = array();
            }
            for ($i = 0; $i <= 6; $i++) {
                if ($result[0][$i . "delivery"] != "0") {
                    $command[$product][$i] = $amounts[$i];
                } else {
                    $command[$product][$i] = 0;
                }

            }
            try {
                $bdd->beginTransaction();
                $request = $bdd->prepare("UPDATE users SET command=:command WHERE username=:username");
                $request->execute(array(
                    "username" => $username,
                    "command" => json_encode($command)
                ));
                self::updateValue($bdd, $command, $username);
                $bdd->commit();
            } catch (Exception $e) {
                $bdd->rollBack();
                throw new MBLException("special");
            }
        } else {
            throw new MBLException("badproduct");
        }
    }

    public static function updateValue(PDO $bdd, array $command, string $username)
    {
        $value = array(0, 0, 0, 0, 0, 0, 0);
        foreach ($command as $key => $item) {
            $request = $bdd->prepare("SELECT price FROM products WHERE name=:name");
            $request->execute(array(
                "name" => $key
            ));
            $result = $request->fetchAll();
            for ($i = 0; $i <= 6; $i++) {
                $value[$i] = $value[$i] + $item[$i] * $result[0]["price"];
            }
        }
        for ($i = 0; $i <= 6; $i++) {
            $valuename = $i . "value";
            $request = $bdd->prepare("UPDATE users SET $valuename=:value WHERE username=:username");
            $request->execute(array(
                "value" => $value[$i],
                "username" => $username
            ));
        }
    }

    public static function deleteProductFromAll(PDO $bdd, string $product)
    {
        $request = $bdd->prepare("SELECT * FROM users");
        $request->execute();
        $result = $request->fetchAll();
        try {
            $bdd->beginTransaction();
            foreach ($result as $user) {
                $command = json_decode($user["command"], true);
                if (array_key_exists($product, $command)) {
                    self::deleteProduct($bdd, $product, $user["username"], false);
                    //add mail sending
                }
            }
            $request = $bdd->prepare("DELETE FROM products WHERE name=:name");
            $request->execute(array(
                "name" => $product
            ));
            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw new MBLException("special");
        }
    }

    public static function deleteProduct(PDO $bdd, string $product, string $username, bool $errored)
    {
        $request = $bdd->prepare("SELECT command FROM users WHERE username=:username");
        $request->execute(array(
            "username" => $username
        ));
        $result = $request->fetchAll();
        $command = $result[0]["command"];
        $command = json_decode($command, true);
        if (isset($command[$product])) {
            if ($errored) {
                if ($command[$product][(WeekDay::getDay() + 1) % 7] != 0) {
                    throw new MBLException("24h");
                }
                if ($command[$product][(WeekDay::getDay()) % 7] != 0) {
                    throw new MBLException("24h");
                }
            }
            unset($command[$product]);
            $request = $bdd->prepare("UPDATE users SET command=:command WHERE username=:username");
            $request->execute(array(
                "username" => $username,
                "command" => json_encode($command)
            ));
            self::updateValue($bdd, $command, $username);
        } else {
            throw new MBLException("badproduct");
        }
    }

    public static function updateValueFromAll(PDO $bdd, string $product, float $price)
    {
        $request = $bdd->prepare("SELECT * FROM users");
        $request->execute();
        $result = $request->fetchAll();
        try {
            $bdd->beginTransaction();
            $request = $bdd->prepare("UPDATE products SET price=:price WHERE name=:name");
            $request->execute(array(
                "price" => $price,
                "name" => $product
            ));
            foreach ($result as $user) {
                $command = json_decode($user["command"], true);
                if (!isset($command)) {
                    $command = array();
                }
                self::updateValue($bdd, $command, $user["username"]);
                //add mail notif
            }
            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw new MBLException("special");
        }
    }

    public static function addProduct(PDO $bdd, string $product)
    {
        $request = $bdd->prepare("INSERT INTO products VALUES(:name, 0)");
        $request->execute(array(
            "name" => $product
        ));
    }

    public static function deleteDayFromAll(PDO $bdd, array $days, bool $new, string $name)
    {
        $request = $bdd->prepare("SELECT * FROM users WHERE city=:city");
        $request->execute(array(
            "city" => $name
        ));
        $result = $request->fetchAll();
        try {
            $bdd->beginTransaction();
            foreach ($result as $user) {
                $command = json_decode($user["command"], true);
                if (!isset($command)) {
                    $command = array();
                }
                for ($i = 0; $i <= 6; $i++) {
                    if ($days[$i] == "0") {
                        foreach ($command as $key => $product) {
                            $command[$key][$i] = 0;
                        }
                    }
                }
                $request = $bdd->prepare("UPDATE users SET command=:command WHERE username=:username");
                $request->execute(array(
                    "command" => json_encode($command),
                    "username" => $user["username"]
                ));
                self::updateValue($bdd, $command, $user["username"]);
            }
            if ($new) {
                $request = $bdd->prepare("INSERT INTO cities VALUES (:name, 0, 0, 0, 0, 0, 0, 0)");
                $request->execute(array(
                    "name" => $name
                ));
            } else {
                $request = $bdd->prepare("UPDATE cities SET 0delivery=:0delivery,1delivery=:1delivery,2delivery=:2delivery,3delivery=:3delivery,4delivery=:4delivery,5delivery=:5delivery,6delivery=:6delivery WHERE name=:city");
                $request->execute(array(
                    "0delivery" => $days[0],
                    "1delivery" => $days[1],
                    "2delivery" => $days[2],
                    "3delivery" => $days[3],
                    "4delivery" => $days[4],
                    "5delivery" => $days[5],
                    "6delivery" => $days[6],
                    "city" => $name
                ));
            }
            $bdd->commit();
        } catch (Exception $e) {
            $bdd->rollBack();
            throw new MBLException("special");
        }
    }
}