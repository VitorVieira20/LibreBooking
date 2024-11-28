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

        $this->ValidateURI();

        if (Configuration::Instance()->GetSectionKey(ConfigSection::PRIVACY, ConfigKeys::PRIVACY_ALLOW_GUEST_BOOKING, new BooleanConverter())) {
            $this->presenter = $this->GetPresenter();
            $this->presenter->PageLoad();
            $this->Set('ReturnUrl', Pages::SCHEDULE);
            $this->Display($this->GetTemplateName());
        } else {
            $this->RedirectToError(ErrorMessages::INSUFFICIENT_PERMISSIONS);
        }
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

    protected function ValidateURI()
    {
        $requestURI = $_SERVER['REQUEST_URI'];
        $segments = explode('/', $requestURI);

        $possibleScripts = $this->ValidatePossibleScripts(($requestURI));

        if (isset($segments[2]) || $possibleScripts) {
            header("Location: /dashboard.php");
            exit;
        }

        if (preg_match('/(?:\?|&)(redirect)=([^&]+)/', $requestURI, $matches)) {
            $this->RetrieveURIParamsFromCalendar($requestURI, urldecode($matches[2]));
        } else {
            $this->RetrieveURIParamsFromSchedule($requestURI);
        }
    }

    protected function ValidatePossibleScripts($requestURI)
    {
        if (
            preg_match('/%22.*%22/', $requestURI) ||
            preg_match('/".*"/', urldecode($requestURI)) ||
            preg_match('/%27.*%27/', $requestURI) ||
            preg_match("/'.*'/", urldecode($requestURI)) ||
            preg_match('/%3Cscript%3E/', $requestURI) ||
            preg_match('/<script>/', urldecode($requestURI))
        ) {
            return true;
        }

        return false;
    }

    protected function RetrieveURIParamsFromSchedule($requestURI)
    {
        $segments = explode('?', $requestURI);

        if (!isset($segments[1]) || $segments[1] === "") {
            header("Location: /view-schedule.php");
            exit;
        }

        $rid = $sid = $rd = $sd = $ed = null;

        preg_match('/rid=(\d+)&/', $requestURI, $rid);
        preg_match('/sid=(\d+)&/', $requestURI, $sid);
        preg_match('/rd=([^&]*)/', $requestURI, $rd);
        preg_match('/sd=([^&]*)/', $requestURI, $sd);
        preg_match('/ed=([^&]*)/', $requestURI, $ed);

        if (empty($rid[1]) || empty($sid[1]) || empty($rd[1]) || empty($sd[1]) || empty($ed[1])) {
            header("Location: /view-schedule.php");
            exit;
        }

        $rd = htmlspecialchars(urldecode($rd[1]), ENT_QUOTES, 'UTF-8');
        $sd = htmlspecialchars(urldecode($sd[1]), ENT_QUOTES, 'UTF-8');
        $ed = htmlspecialchars(urldecode($ed[1]), ENT_QUOTES, 'UTF-8');

        $validParams = $this->ValidateURIParamsFromSchedule($rid[1], $sid[1], $rd, $sd, $ed);

        if (!$validParams) {
            header("Location: /view-schedule.php");
            exit;
        }
    }

    protected function ValidateURIParamsFromSchedule($rid, $sid, $rd, $sd, $ed)
    {
        $validRd = preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $rd, $rdMatches);
        $validSd = preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $sd, $sdMatches);
        $validEd = preg_match('/^\d{4}-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01]) (\d|1\d|2[0-3]):([0-5]\d):([0-5]\d)$/', $ed, $edMatches);

        return (is_numeric($rid) && is_numeric($sid) && $validRd && $validSd && $validEd);
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
