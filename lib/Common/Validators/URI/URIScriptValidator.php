<?php

class URIScriptValidator implements IURIScriptValidator
{
    public static function validate($requestURI, $redirectURL): void
    {
        $segments = explode('/', $requestURI);

        $possibleScripts = self::ValidatePossibleScripts(($requestURI));

        if (isset($segments[2]) || $possibleScripts) {
            header("Location: " . $redirectURL);
            exit;
        }
    }

    private static function validatePossibleScripts(string $requestURI): bool
    {
        return preg_match('/%22.*%22/', $requestURI) ||
               preg_match('/".*"/', urldecode($requestURI)) ||
               preg_match('/%27.*%27/', $requestURI) ||
               preg_match("/'.*'/", urldecode($requestURI)) ||
               preg_match('/%3Cscript%3E/', $requestURI) ||
               preg_match('/<script>/', urldecode($requestURI));
    }
}