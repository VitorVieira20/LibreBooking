<?php

class ParamsValidatorMethods implements IParamsValidatorMethods
{
    public static function numericalValidator(string $param, string $requestURI): bool
    {
        return self::validateParam($param, $requestURI, fn($value) => is_numeric($value));
    }

    public static function existsInURLValidator(string $param, string $requestURI): bool
    {
        return self::validateParam($param, $requestURI, fn($value) => !empty($value));
    }

    public static function dateValidator(string $param, string $requestURI): bool
    {
        return self::validateParam($param, $requestURI, fn($value) => preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $value));
    }

    public static function simpleDateTimeValidator(string $param, string $requestURI): bool
    {
        return self::validateParam($param, $requestURI, fn($value) => preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):([0-5]\d)$/', $value));
    }

    public static function complexDateTimedateValidator(string $param, string $requestURI): bool
    {
        return self::validateParam($param, $requestURI, fn($value) => preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $value));
    }

    public static function redirectGuestReservationValidator(string $requestURI): bool
    {
        return self::validateParam('redirect', $requestURI, function ($redirectURL) {
            $startValid = preg_match('/[?&]start=(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])/', $redirectURL);
            preg_match('/[?&]ct=([a-zA-Z0-9_]+)/', $redirectURL, $ct);
            $ctValid = isset($ct[1]) && in_array($ct[1], [
                CalendarTypes::Day,
                CalendarTypes::Week,
                CalendarTypes::Month
            ]);
            return $startValid && $ctValid;
        });
    }

    private static function validateParam(string $param, string $requestURI, callable $validationCallback): bool
    {
        if (self::containsMaliciousContent($requestURI)) {
            return false;
        }

        $pattern = "/[?&]" . preg_quote($param, '/') . "=([^&]*)/";
        if (preg_match($pattern, $requestURI, $matches)) {
            $value = htmlspecialchars(urldecode($matches[1]), ENT_QUOTES, 'UTF-8');
            return $validationCallback($value);
        }

        return false;
    }

    private static function containsMaliciousContent(string $requestURI): bool
    {
        $decodedURI = urldecode($requestURI);

        $patterns = [
            '/%22.*%22/',
            '/".*"/',
            '/%27.*%27/',
            "/'.*'/",
            '/%3Cscript%3E/',
            '/<script>/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $requestURI) || preg_match($pattern, $decodedURI)) {
                return true;
            }
        }

        return false;
    }
}
