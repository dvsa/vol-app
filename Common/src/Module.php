<?php

namespace Common;

use Common\Exception\ResourceNotFoundException;
use Common\Preference\LanguageListener;
use Common\Service\Cqrs\Exception\AccessDeniedException;
use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Utils\Translation\MissingTranslationProcessor;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\RequestInterface;
use Olcs\Logging\Log\Logger;
use Laminas\EventManager\EventManager;
use Laminas\Http\Request;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\Mvc\Application;
use Laminas\Mvc\MvcEvent;
use Laminas\Http\PhpEnvironment\Response;
use Laminas\View\Model\ViewModel;
use Laminas\ModuleManager\Feature\ConfigProviderInterface;
use Laminas\ModuleManager\Feature\ServiceProviderInterface;

/**
 * ZF2 Module
 */
class Module implements ConfigProviderInterface, ServiceProviderInterface
{
    public static string $dateFormat = 'd/m/Y';

    public static string $dateTimeFormat = 'd/m/Y H:i';

    public static string $dateTimeSecFormat = 'd/m/Y H:i:s';

    public static string $dbDateFormat = 'Y-m-d';

    /**
     * Initialize module
     *
     * @param \Laminas\ModuleManager\ModuleManager $moduleManager Module manager
     */
    public function init($moduleManager): void
    {
        /** @var EventManager $events */
        $events = $moduleManager->getEventManager();
        $events->attach('loadModules.post', function (\Laminas\ModuleManager\ModuleEvent $e): void {
            $this->modulesLoaded($e);
        });
    }

    /**
     * Modules loaded event
     *
     * @param ModuleEvent $e Module event
     */
    public function modulesLoaded(ModuleEvent $e): void
    {
        $moduleManager = $e->getTarget();

        $config = $moduleManager->getModule('Olcs') ? $moduleManager->getModule('Olcs')->getConfig() : [];

        self::$dateFormat = $config['date_settings']['date_format'] ?? self::$dateFormat;
        self::$dateTimeFormat = $config['date_settings']['datetime_format'] ?? self::$dateTimeFormat;
        self::$dateTimeSecFormat = $config['date_settings']['datetimesec_format'] ?? self::$dateTimeSecFormat;
        self::$dbDateFormat = $config['date_settings']['db_date_format'] ?? self::$dbDateFormat;
    }

    /**
     * Bootstrap
     *
     * @param MvcEvent $e MVC Event
     */
    public function onBootstrap(MvcEvent $e): void
    {
        $app = $e->getApplication();
        $sm = $app->getServiceManager();
        $events = $app->getEventManager();

        $this->setUpTranslator($sm, $events);

        //  Navigation:Check ability to access an item
        $listener = $sm->get(\Common\Rbac\Navigation\IsAllowedListener::class);

        $events->getSharedManager()->attach(
            \Laminas\View\Helper\Navigation\AbstractHelper::class,
            'isAllowed',
            [$listener, 'accept']
        );

        //  RBAC behaviour if user not authorised
        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$sm->get(\LmcRbacMvc\View\Strategy\RedirectStrategy::class), 'onError']);
        //  CSRF token check
        $events->attach(MvcEvent::EVENT_DISPATCH, function (\Laminas\Mvc\MvcEvent $e): void {
            $this->validateCsrfToken($e);
        }, 100);

        // On dispatch error ot certain CQRS exceptions then change page to a 404
        $events->attach(
            MvcEvent::EVENT_DISPATCH_ERROR,
            static function (MvcEvent $e) {
                // If Backend Not found or access denied then display error as a 404 not found
                if (
                    $e->getParam('exception') instanceof NotFoundException
                    || $e->getParam('exception') instanceof AccessDeniedException
                    || $e->getParam('exception') instanceof ResourceNotFoundException
                ) {
                    $e->setError(Application::ERROR_CONTROLLER_INVALID);
                    $e->setParam('exceptionNoLog', true);
                }
            },
            100
        );

        $this->setupRequestForProxyHost($app->getRequest());

        $this->setLoggerUser($sm);

        $identifier = $sm->get(\Olcs\Logging\Log\Processor\RequestId::class)->getIdentifier();

        $this->onFatalError($identifier);

        $events->attach(
            MvcEvent::EVENT_RENDER,
            static function (MvcEvent $e) use ($identifier) {
                // Inject the log correlation ID into the view
                if ($e->getResult() instanceof ViewModel) {
                    $e->getResult()->setVariable('correlationId', $identifier);
                }
            },
            -100
        );

        /** @var Response $response */
        $response = $e->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaders(
            ['X-XSS-Protection: 1; mode=block', 'X-Content-Type-Options: nosniff']
        );
    }

    /**
     * Catch fatal error
     *
     * @param string $identifier Identifier
     *
     * @return void;
     */
    public function onFatalError($identifier): void
    {
        // Handle fatal errors //
        register_shutdown_function(
            static function () use ($identifier) {
                // get error
                $error = error_get_last();
                // E_USER_ERROR included: vendor __toString fallbacks (laminas-view,
                // guzzle) were silently swallowed pre-monolog. Tolerate and rely on
                // monolog's fatal handler logging so user journeys continue.
                $minorErrors = [
                    E_WARNING, E_NOTICE, E_USER_NOTICE, E_DEPRECATED, E_USER_DEPRECATED, E_USER_ERROR
                ];
                if (null === $error || (isset($error['type']) && in_array($error['type'], $minorErrors))) {
                    return null;
                }
                // check and allow only errors
                // clean any previous output from buffer
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                /** @var Response $response */
                $response = new Response();
                $response->getHeaders()
                    ->addHeaderLine('Location', '/error?correlationId=' . $identifier . '&src=shutdown');
                $response->setStatusCode(Response::STATUS_CODE_302);
                $response->sendHeaders();
                return $response;
            }
        );
    }


    /**
     * Validate the CSRF token
     *
     * @param MvcEvent $e MVC event
     */
    public function validateCsrfToken(MvcEvent $e): void
    {
        /** @var \Laminas\Http\PhpEnvironment\Request $request */
        $request = $e->getRequest();
        if ($request->isPost() === false) {
            return;
        }

        $sm = $e->getApplication()->getServiceManager();

        //  whitelisted paths: allow POST without CSRF check
        $cfg = $sm->get('config');
        if (in_array($request->getUri()->getPath(), $cfg['csrf']['whitelist'], true)) {
            return;
        }

        $postDataCnt = $request->getPost()->count();
        if ($postDataCnt === 0) {
            return;
        }

        $name = 'security';
        $token = $request->getPost($name);

        $validator = new \Laminas\Validator\Csrf(['name' => $name]);
        if ($validator->isValid($token)) {
            return;
        }

        $hlpFlashMsgr = $sm->get('Helper\FlashMessenger');
        $hlpFlashMsgr->addErrorMessage('csrf-message');

        /** @var \Laminas\Http\Response $resp */
        $resp = $e->getResponse();
        $resp->getHeaders()->addHeaderLine('X-CSRF-error', '1');

        $request->setMethod(Request::METHOD_GET);
    }

    /**
     * Get config
     *
     * @return array
     */
    #[\Override]
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Setup the translator service
     *
     * @param \Laminas\EventManager\EventManager $eventManager Event manager
     *
     * @return void
     */
    protected function setUpTranslator(ServiceManager $sm, $eventManager)
    {
        $cache = $sm->get('default-cache');
        $translator = $sm->get('translator');
        $translator->setCache($cache);

        /** @var LanguageListener $languagePrefListener */
        $languagePrefListener = $sm->get('LanguageListener');
        $languagePrefListener->attach($eventManager, 1);

        /** @var  MissingTranslationProcessor $missingTranslationProcessor */
        $missingTranslationProcessor = $sm->get('Utils\MissingTranslationProcessor');
        $missingTranslationProcessor->attach($eventManager);

        $translator->enableEventManager();
        $translator->setEventManager($eventManager);
    }

    /**
     * If the request is coming through a proxy then update the host name on the request
     */
    private function setupRequestForProxyHost(RequestInterface $request): void
    {
        if (!$request instanceof \Laminas\Http\PhpEnvironment\Request) {
            // if request is not \Laminas\Http\PhpEnvironment\Request we must be running from CLI so do nothing
            return;
        }

        if ($request->getHeaders()->get('x-forwarded-host')) {
            $host = $request->getHeaders()->get('x-forwarded-host')->getFieldValue();

            $hosts = explode(',', $host);
            if (!empty($hosts)) {
                $host = trim($hosts[0]);
            }

            Logger::debug(
                sprintf(
                    'Request host set from x-forwarded-host header to %s setting host to %s',
                    $request->getHeaders()->get('x-forwarded-host')->getFieldValue(),
                    $host
                )
            );
            $request->getUri()->setHost($host);
        }

        // if X-Forwarded-Proto Header exists (ie from AWS ELB) then set the request as this so that route
        // generated URLS will have the correct scheme
        if ($request->getHeaders()->get('x-forwarded-proto')) {
            $proto = $request->getHeaders()->get('x-forwarded-proto')->getFieldValue();

            Logger::debug(
                sprintf(
                    'Request scheme set from xforwardedproto header to %s',
                    $proto
                )
            );
            $request->getUri()->setScheme($proto);
        }
    }

    /**
     * Set the user ID in the log processor so that it can be included in the log files
     */
    private function setLoggerUser(ServiceManager $serviceManager): void
    {
        $authService = $serviceManager->get(\LmcRbacMvc\Service\AuthorizationService::class);
        $serviceManager->get(\Olcs\Logging\Log\Processor\UserId::class)
            ->setUserId($authService->getIdentity()->getUsername());
    }

    #[\Override]
    public function getServiceConfig()
    {
        return [];
    }
}
