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
    private IdentityProviderInterface $identityProvider;
    protected Redirect $redirectHelper;

    /**
     * SessionTimeoutController constructor.
     * @param IdentityProviderInterface $identityProvider
     * @param Redirect $redirectHelper
     */
    public function __construct(
        IdentityProviderInterface $identityProvider,
        Redirect $redirectHelper
    ) {
        $this->identityProvider = $identityProvider;
        $this->redirectHelper = $redirectHelper;
    }

    /**
     * @param Request $request
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
