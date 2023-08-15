<?php

declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Plugin\Redirect;
use Common\Rbac\PidIdentityProvider;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Laminas\Http\Request;
use Laminas\View\Model\ViewModel;
use ZfcRbac\Identity\IdentityProviderInterface;

/**
 * @See SessionTimeoutControllerFactory
 */
class SessionTimeoutController
{
    private IdentityProviderInterface $identityProvider;
    protected Redirect $redirectHelper;
    private LogoutService $logoutService;
    private CookieService $cookieService;

    /**
     * SessionTimeoutController constructor.
     * @param IdentityProviderInterface $identityProvider
     * @param Redirect $redirectHelper
     * @param CookieService $cookieService
     * @param LogoutService $logoutService
     */
    public function __construct(
        IdentityProviderInterface $identityProvider,
        Redirect $redirectHelper,
        CookieService $cookieService,
        LogoutService $logoutService
    ) {
        $this->identityProvider = $identityProvider;
        $this->redirectHelper = $redirectHelper;
        $this->cookieService = $cookieService;
        $this->logoutService = $logoutService;
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
            if ($this->identityProvider instanceof PidIdentityProvider) {
                $token = $this->cookieService->getCookie($request);

                if (!empty($token)) {
                    $this->identityProvider->clearSession();
                    $this->logoutService->logout($token);
                    $response = $this->redirectHelper->refresh();
                    $this->cookieService->destroyCookie($response);
                    return $response;
                }
            } else {
                $response = $this->redirectHelper->refresh();
                $this->identityProvider->clearSession();
                return $response;
            }
        }

        $view = new ViewModel(['pageTitle' => 'session-timeout.page.title']);
        $view->setTemplate('pages/auth/session-timeout');

        return $view;
    }
}
