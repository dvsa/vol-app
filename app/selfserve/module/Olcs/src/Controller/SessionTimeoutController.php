<?php
declare(strict_types=1);

namespace Olcs\Controller;

use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Dvsa\Olcs\Auth\Service\Auth\LogoutService;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;

/**
 * @See SessionTimeoutControllerFactory
 */
class SessionTimeoutController
{
    /**
     * @var CurrentUser
     */
    private $currentUser;

    /**
     * @var Redirect
     */
    protected $redirectHelper;

    /**
     * @var LogoutService
     */
    private $logoutService;

    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * SessionTimeoutController constructor.
     * @param CurrentUser $currentUser
     * @param Redirect $redirectHelper
     * @param CookieService $cookieService
     * @param LogoutService $logoutService
     */
    public function __construct(
        CurrentUser $currentUser,
        Redirect $redirectHelper,
        CookieService $cookieService,
        LogoutService $logoutService
    ) {
        $this->currentUser = $currentUser;
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
        $identity = $this->currentUser->getIdentity();
        if (!is_null($identity) && !$identity->isAnonymous()) {
            $token = $this->cookieService->getCookie($request);

            if (!empty($token)) {
                $this->logoutService->logout($token);
                $response = $this->redirectHelper->refresh();
                $this->cookieService->destroyCookie($response);
                return $response;
            }
        }

        $view = new ViewModel(['pageTitle' => 'session-timeout.page.title']);
        $view->setTemplate('pages/auth/session-timeout');

        return $view;
    }
}
