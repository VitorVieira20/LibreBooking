<?php

class ParamsValidator
{
    public static function validate(array $params, string $requestURI, string $redirectURL, bool $optional): void
    {
        $segments = explode('?', $requestURI);

        // Se não há parâmetros e a validação não é obrigatória, retorna sem problemas
        if (empty($segments[1])) {
            if (!$optional) {
                header("Location: " . $redirectURL);
                exit;
            }
            return;
        }

        $valid = true;

        foreach ($params as $key => $validationType) {
            // Validação para parâmetros definidos como arrays de validações
            if (is_array($validationType)) {
                $allFailed = true;
                foreach ($validationType as $validation) {
                    if (self::runValidation($key, $validation, $requestURI)) {
                        $allFailed = false;
                        break;
                    }
                }

                if ($allFailed) {
                    $valid = false;
                }
            } else {
                // Validação para parâmetros com apenas um tipo de validação
                if (!self::runValidation($key, $validationType, $requestURI)) {
                    $valid = false;
                }
            }
        }

        if (!$valid) {
            header("Location: " . $redirectURL);
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

            case ParamsValidatorKeys::SIMPLE_DATE:
                return ParamsValidatorMethods::simpleDateValidatorList($value, $requestURI);

            case ParamsValidatorKeys::SIMPLE_DATETIME:
                return ParamsValidatorMethods::simpleDateTimeValidator($value, $requestURI);

            case ParamsValidatorKeys::COMPLEX_DATETIME:
                return ParamsValidatorMethods::complexDateTimedateValidator($value, $requestURI);

            case ParamsValidatorKeys::EXISTS:
                return ParamsValidatorMethods::existsInURLValidator($value, $requestURI);

            case ParamsValidatorKeys::REDIRECT_GUEST_RESERVATION:
                return ParamsValidatorMethods::redirectGuestReservationValidator($requestURI);

            default:
                return false;
        }
    }
}
