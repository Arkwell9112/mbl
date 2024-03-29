<?php


class WeekDay
{
    //Permet de récupérer le numérique du jour de la semaine. 0 Lundi 6 Dimanche.
    public static function getDay(): int
    {
        $day = date("l");
        switch ($day) {
            case "Monday":
                return 0;
                break;
            case "Tuesday":
                return 1;
                break;
            case "Wednesday":
                return 2;
                break;
            case "Thursday":
                return 3;
                break;
            case "Friday":
                return 4;
                break;
            case "Saturday":
                return 5;
                break;
            case "Sunday":
                return 6;
                break;
        }
    }
}