<?php

interface IParamsValidatorMethods
{
    public static function numericalValidator(string $param, string $requestURI): bool;

    public static function existsInURLValidator(string $param, string $requestURI): bool;

    public static function dateValidator(string $param, string $requestURI): bool;

    public static function simpleDateTimeValidator(string $param, string $requestURI): bool;

    public static function complexDateTimedateValidator(string $param, string $requestURI): bool;

    public static function redirectGuestReservationValidator(string $requestURI): bool;
}