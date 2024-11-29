<?php

interface IURIScriptValidator
{
    public static function validate(string $requestURI, string $redirectURL): void;
}