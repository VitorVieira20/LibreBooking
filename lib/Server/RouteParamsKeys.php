<?php

class RouteParamsKeys
{
    private function __construct()
    {
    }

    public const GUEST_RESERVATION_FROM_SCHEDULE = [
        QueryStringKeys::REPORT_ID => ParamsValidatorKeys::NUMERICAL,
        QueryStringKeys::SCHEDULE_ID => ParamsValidatorKeys::NUMERICAL,
        QueryStringKeys::RESERVATION_DATE => ParamsValidatorKeys::DATE,
        QueryStringKeys::START_DATE => ParamsValidatorKeys::COMPLEX_DATETIME,
        QueryStringKeys::END_DATE => ParamsValidatorKeys::COMPLEX_DATETIME
    ];

    public const GUEST_RESERVATION_FROM_CALENDAR = [
        QueryStringKeys::SCHEDULE_ID => ParamsValidatorKeys::EXISTS,
        QueryStringKeys::REPORT_ID => ParamsValidatorKeys::EXISTS,
        QueryStringKeys::START_DATE => ParamsValidatorKeys::SIMPLE_DATETIME,
        QueryStringKeys::END_DATE => ParamsValidatorKeys::SIMPLE_DATETIME,
        QueryStringKeys::REDIRECT => ParamsValidatorKeys::REDIRECT_GUEST_RESERVATION
    ];
}
