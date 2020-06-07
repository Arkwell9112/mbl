<?php

include("../classes/WeekDay.php");

try {
    $prediction = array();
    $request = $bdd->prepare("SELECT * FROM cities");
    $request->execute();
    $cities = $request->fetchAll();
    foreach ($cities as $city) {
        try {
            $cityname = $city["name"];
            $prediction[$cityname] = array();
            $request = $bdd->prepare("SELECT * FROM users WHERE city=:city");
            $request->execute(array(
                "city" => $city["name"]
            ));
            $users = $request->fetchAll();
        } catch (Exception $e) {
            header("Refresh:0");
        }
        for ($i = WeekDay::getDay() + 1; $i <= WeekDay::getDay() + 7; $i++) {
            $day = $i % 7;
            foreach ($users as $user) {
                $amount = 0;
                for ($j = WeekDay::getDay() + 1; $j <= $i; $j++) {
                    $currentday = $j % 7;
                    $amount = $amount + $user[$currentday . "value"];
                }
                if ($user["value"] >= $amount) {
                    $command = json_decode($user["command"], true);
                    foreach ($command as $key => $product) {
                        if (!isset($prediction[$cityname])) {
                            $prediction[$cityname] = array();
                        }
                        if (!isset($prediction[$cityname][$key])) {
                            $prediction[$cityname][$key] = array();
                        }
                        if (!isset($prediction[$cityname][$key][$day])) {
                            $prediction[$cityname][$key][$day] = 0;
                        }
                        $prediction[$cityname][$key][$day] = $prediction[$cityname][$key][$day] + $product[$day];
                    }
                }
            }
        }
    }
} catch (Exception $e) {
    header("Refresh:0");
}
$first = true;
foreach ($prediction as $key => $products) {
    include("../frags/fragAccountInnerCommands.php");
    $first = false;
}