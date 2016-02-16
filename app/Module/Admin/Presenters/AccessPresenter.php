<?php

namespace MP\Module\Admin\Presenters;

use MP\Module\Admin\Component\LoginControl\ILoginControlFactory;
use MP\Module\Admin\Component\LoginControl\LoginControl;
use MP\Module\Admin\Component\PasswordChangeControl\IPasswordChangeControlFactory;
use MP\Module\Admin\Component\PasswordChangeControl\PasswordChangeControl;
use MP\Module\Admin\Component\PasswordResetControl\IPasswordResetControlFactory;
use MP\Module\Admin\Component\PasswordResetControl\PasswordResetControl;
use MP\Module\Admin\Component\RegistrationControl\IRegistrationControlFactory;
use MP\Module\Admin\Component\RegistrationControl\RegistrationControl;

/**
 * Prihlaseni, registrace a reset hesla uzivatele.
 *
 * @author Martin Odstrcilik <martin.odstrcilik@gmail.com>
 */
class AccessPresenter extends AbstractAdminPresenter
{
    /** @const string */
    const PARAM_RESTORE = 'restore';

    /** @const Nazev komponenty s prihlasovacim formularem */
    const COMPONENT_LOGIN = 'login';

    public function actionLogin()
    {
        $this[self::COMPONENT_LOGIN][LoginControl::COMPONENT_FORM];
    }

    /**
     * @User(loggedIn)
     */
    public function actionChange()
    {

    }

    /**
     * @override Na strankach pro neprihlaseneho nechci dashboard v zahlavi
     */
    protected function beforeRender()
    {
        parent::beforeRender();

        $this->template->preventDashboard = true;
    }

    /**
     * @param IPasswordChangeControlFactory $factory
     *
     * @return PasswordChangeControl
     */
    protected function createComponentChange(IPasswordChangeControlFactory $factory)
    {
        $control = $factory->create();

        return $control;
    }

    /**
     * @param IPasswordResetControlFactory $factory
     *
     * @return PasswordResetControl
     */
    protected function createComponentReset(IPasswordResetControlFactory $factory)
    {
        $control = $factory->create();

        return $control;
    }

    /**
     * @param ILoginControlFactory $factory
     *
     * @return LoginControl
     */
    protected function createComponentLogin(ILoginControlFactory $factory)
    {
        $control = $factory->create();

        return $control;
    }

    /**
     * @param IRegistrationControlFactory $factory
     *
     * @return RegistrationControl
     */
    protected function createComponentRegistration(IRegistrationControlFactory $factory)
    {
        $control = $factory->create();

        return $control;
    }
}
