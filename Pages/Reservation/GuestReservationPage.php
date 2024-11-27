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

        if (
            isset($segments[2]) ||
            preg_match('/%22.*%22/', $requestURI) ||
            preg_match('/".*"/', urldecode($requestURI)) ||
            preg_match('/%27.*%27/', $requestURI) ||
            preg_match("/'.*'/", urldecode($requestURI)) ||
            preg_match('/%3Cscript%3E/', $requestURI) ||
            preg_match('/<script>/', urldecode($requestURI))
        ) {
            header("Location: /guest-reservation.php");
            exit;
        }
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
