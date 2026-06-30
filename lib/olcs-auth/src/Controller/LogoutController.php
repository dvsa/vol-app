<?php

namespace Dvsa\Olcs\Auth\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\Container;

/**
 * Class LogoutController
 */
class LogoutController extends AbstractActionController
{
    /**
     * LogoutController constructor.
     *
     * @param bool          $isSelfServe          Is the current user selfserve?
     * @param string        $selfServeRedirectUrl URL to redirect self serve user
     */
    public function __construct(private $isSelfServe, private $selfServeRedirectUrl, private Container $session)
    {
    }

    /**
     * Logout the user, and redirect to index or Gov site
     *
     * @return \Laminas\Http\Response
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    #[\Override]
    public function indexAction()
    {
        $this->session->exchangeArray([]);

        if ($this->isSelfServe) {
            // No need to add to config is it is only used once.
            return $this->redirect()->toUrl(
                $this->selfServeRedirectUrl
            );
        }

        return $this->redirect()->toRoute('auth/login/GET');
    }
}
