<?php

require_once(ROOT_DIR . 'Pages/Reservation/NewReservationPage.php');
require_once(ROOT_DIR . 'Presenters/Reservation/GuestReservationPresenter.php');

interface IGuestReservationPage extends INewReservationPage
{
    /**
     * @return bool
     */
    public function GuestInformationCollected();

    /**
     * @return string
     */
    public function GetEmail();

    /**
     * @return bool
     */
    public function IsCreatingAccount();

    /**
     * @return bool
     */
    public function GetTermsOfServiceAcknowledgement();
}

class GuestReservationPage extends NewReservationPage implements IGuestReservationPage
{
    public function PageLoad()
    {

        URIScriptValidator::validate($_SERVER['REQUEST_URI'], '/dashboard.php');

        if (preg_match('/(?:\?|&)(redirect)=([^&]+)/', $_SERVER['REQUEST_URI'], $matches)) {
            ParamsValidator::validate(RouteParamsKeys::GUEST_RESERVATION_FROM_CALENDAR, $_SERVER['REQUEST_URI'], '/view-calendar.php');
        } else {
            ParamsValidator::validate(RouteParamsKeys::GUEST_RESERVATION_FROM_SCHEDULE, $_SERVER['REQUEST_URI'], '/view-schedule.php');
        }

        if (Configuration::Instance()->GetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_ALLOW_GUEST_BOOKING, new BooleanConverter())) {
            $this->presenter = $this->GetPresenter();
            $this->presenter->PageLoad();
            $this->Set('ReturnUrl', Pages::SCHEDULE);
            $this->Display($this->GetTemplateName());
        } else {
            $this->RedirectToError(ErrorMessages::INSUFFICIENT_PERMISSIONS);
        }
    }

    protected function RetrieveURIParamsFromCalendar($requestURI, $redirectValue)
    {
        $segments = explode('?', $requestURI);

        if (!isset($segments[1]) || $segments[1] === "") {
            header("Location: /view-calendar.php");
            exit;
        }

        $sd = $ed = null;

        preg_match('/sd=([^&]*)/', $requestURI, $sd);
        preg_match('/ed=([^&]*)/', $requestURI, $ed);

        if (empty($sd[1]) || empty($ed[1]) || !(preg_match('/(?:\?|&)sid=(&|$)/', $requestURI) || preg_match('/(?:\?|&)rid=(&|$)/', $requestURI))) {
            header("Location: /view-calendar.php");
            exit;
        }

        $sd = htmlspecialchars(urldecode($sd[1]), ENT_QUOTES, 'UTF-8');
        $ed = htmlspecialchars(urldecode($ed[1]), ENT_QUOTES, 'UTF-8');

        $validParams = $this->ValidateURIParamsFromCalendar($sd, $ed, $redirectValue);

        if (!$validParams) {
            header("Location: /view-calendar.php");
            exit;
        }
    }

    protected function ValidateURIParamsFromCalendar($sd, $ed, $redirectValue)
    {
        $validSd = preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):(\d|[1-5]\d)$/', $sd);
        $validEd = preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):(\d|[1-5]\d)$/', $ed);
        $validStart = preg_match('/[?&]start=(\d{4})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])/', $redirectValue);

        preg_match('/[?&]ct=([a-zA-Z0-9_]+)/', $redirectValue, $ct);

        $validCt = in_array($ct[1], [
            CalendarTypes::Day,
            CalendarTypes::Week,
            CalendarTypes::Month
        ]);


        return ($validSd && $validEd && $validCt && $validStart);
    }

    protected function GetPresenter()
    {
        return new GuestReservationPresenter(
            $this,
            new GuestRegistration(new PasswordEncryption(), new UserRepository(), new GuestRegistrationNotificationStrategy(), new GuestReservationPermissionStrategy($this)),
            new WebAuthentication(PluginManager::Instance()->LoadAuthentication()),
            $this->LoadInitializerFactory(),
            new NewReservationPreconditionService()
        );
    }

    protected function GetTemplateName()
    {
        if ($this->GuestInformationCollected()) {
            return parent::GetTemplateName();
        }

        return 'Reservation/collect-guest.tpl';
    }

    public function GuestInformationCollected()
    {
        return !ServiceLocator::GetServer()->GetUserSession()->IsGuest();
    }

    public function GetEmail()
    {
        return $this->GetForm(FormKeys::EMAIL);
    }

    public function IsCreatingAccount()
    {
        return $this->IsPostBack() && !$this->GuestInformationCollected();
    }

    public function GetTermsOfServiceAcknowledgement()
    {
        return $this->GetCheckbox(FormKeys::TOS_ACKNOWLEDGEMENT);
    }
}
