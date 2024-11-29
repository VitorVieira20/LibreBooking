<?php

class ParamsValidator
{
    public static function validate(array $params, string $requestURI, string $redirctURL): void
    {
        $segments = explode('?', $requestURI);

        $valid = true;

        if (!isset($segments[1]) || $segments[1] === "") {
            $valid = false;
        }

        foreach ($params as $key => $validationType) {
            if (!self::runValidation($key, $validationType, $requestURI)) {
                $valid = false;
            }
        }
        
        if (!$valid) {
            header("Location: " . $redirctURL);
            exit;
        }
    }


    private static function runValidation(string $value, string $validationType, string $requestURI): bool
    {
        switch ($validationType) {
            case ParamsValidatorKeys::NUMERICAL:
                return ParamsValidatorMethods::numericalValidator($value, $requestURI);

            case ParamsValidatorKeys::DATE:
                return ParamsValidatorMethods::dateValidator($value, $requestURI);

            case ParamsValidatorKeys::SIMPLE_DATETIME:
                return ParamsValidatorMethods::simpleDateTimeValidator($value, $requestURI);

            case ParamsValidatorKeys::COMPLEX_DATETIME:
                return ParamsValidatorMethods::complexDateTimedateValidator($value, $requestURI);
            
            case ParamsValidatorKeys::EXISTS:
                return ParamsValidatorMethods::existsInURLValidator($value, $requestURI);

            default:
                return false;
        }
    }
}