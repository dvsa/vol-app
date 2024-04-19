<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Plugin\Redirect;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @See SessionTimeoutControllerFactory
 */
class SessionTimeoutController
{
    /**
     * SessionTimeoutController constructor.
     */
    public function __construct(private IdentityProviderInterface $identityProvider, protected Redirect $redirectHelper)
    {
    }

    /**
     * @return \Laminas\Http\Response|ViewModel
     */
    public function indexAction(Request $request)
    {
        // redirect to the login
        $identity = $this->identityProvider->getIdentity();
        if (!is_null($identity) && !$identity->isAnonymous()) {
            $response = $this->redirectHelper->refresh();
            $this->identityProvider->clearSession();
            return $response;
        }

        $view = new ViewModel(['pageTitle' => 'session-timeout.page.title']);
        $view->setTemplate('pages/auth/session-timeout');

        return $view;
    }
}
