<?php

declare(strict_types=1);

namespace Olcs\Mvc;

use Common\Rbac\JWTIdentityProvider;
use Common\Service\Helper\UrlHelperService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Http\Request as HttpRequest;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface;

class TermsAgreedListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    public const ROUTE_WELCOME = 'welcome';

    public const EXCLUDED_ROUTES = [
        self::ROUTE_WELCOME,
        'auth/logout',
        'terms-and-conditions',
    ];

    public function __construct(
        private readonly JWTIdentityProvider $identityProvider,
        private readonly UrlHelperService $urlHelper
    ) {
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, $this->onDispatch(...), $priority);
    }

    /**
     * {@inheritdoc}
     *
     * @return ResponseInterface|void
     */
    public function onDispatch(MvcEvent $e)
    {
        $request = $e->getRequest();

        if (!($request instanceof HttpRequest)) {
            return;
        }

        if (in_array($e->getRouteMatch()->getMatchedRouteName(), self::EXCLUDED_ROUTES)) {
            return;
        }

        $user = $this->identityProvider->getIdentity();

        if ($user->isAnonymous() || $user->isNotIdentified() || $user->hasAgreedTerms()) {
            return;
        }

        $redirectUrl = $this->urlHelper->fromRoute(self::ROUTE_WELCOME);
        $response = $e->getResponse();
        $responseHeaders = $response->getHeaders();

        $responseHeaders->addHeaderLine('Location', $redirectUrl);
        $response->setStatusCode(303);

        return $response;
    }
}
