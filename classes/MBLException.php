<?php

// Permet l'utilisation d'une Exception spéciale pour le projet MBL.
class MBLException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}