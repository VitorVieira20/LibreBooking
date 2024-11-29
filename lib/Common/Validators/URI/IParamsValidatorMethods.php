<?php

interface IParamsValidatorMethods
{
    public static function numericalValidator($param, $requestURI): bool;

    public static function existsInURLValidator(string $param, string $requestURI): bool;

    public static function dateValidator($param, $requestURI): bool;

    public static function simpleDateTimeValidator($param, $requestURI): bool;

    public static function complexDateTimedateValidator($param, $requestURI): bool;
}