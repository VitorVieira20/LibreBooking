<?php

class ParamsValidatorMethods implements IParamsValidatorMethods
{
    public static function numericalValidator($param, $requestURI): bool
    {
        $pattern = "/[?&]" . preg_quote($param, '/') . "=([0-9]+)/";

        if (preg_match($pattern, $requestURI, $matches)) {
            return is_numeric($matches[1]);
        }

        return false;
    }

    public static function existsInURLValidator(string $param, string $requestURI): bool
    {
        $pattern = "/(?:\?|&)" . preg_quote($param, '/') . "=([^&]*)/";

        return preg_match($pattern, $requestURI) === 1; 
    }


    public static function dateValidator($param, $requestURI): bool
    {
        $pattern = "/[?&]" . preg_quote($param, '/') . "=([^&]*)/";

        if (preg_match($pattern, $requestURI, $matches)) {
            $value = htmlspecialchars(urldecode($matches[1]), ENT_QUOTES, 'UTF-8');
            return preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $value) === 1;
        }

        return false;
    }

    public static function simpleDateTimeValidator($param, $requestURI): bool
    {
        $pattern = "/[?&]" . preg_quote($param, '/') . "=([^&]*)/";

        if (preg_match($pattern, $requestURI, $matches)) {
            $value = htmlspecialchars(urldecode($matches[1]), ENT_QUOTES, 'UTF-8');
            return preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):(\d|[1-5]\d)$/', $value) === 1;
        }

        return false;
    }

    public static function complexDateTimedateValidator($param, $requestURI): bool
    {
        $pattern = "/[?&]" . preg_quote($param, '/') . "=([^&]*)/";

        if (preg_match($pattern, $requestURI, $matches)) {
            $value = htmlspecialchars(urldecode($matches[1]), ENT_QUOTES, 'UTF-8');
            return preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $value) === 1;
        }

        return false;
    }
}
