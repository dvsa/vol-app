<?php

namespace Dvsa\Olcs\Api;

use Olcs\Logging\Log\Logger;
use phpseclib3\Crypt;
use Laminas\EventManager\EventInterface;
use Laminas\ModuleManager\Feature\BootstrapListenerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\ResponseSender\SendResponseEvent;

/**
 * Module class
 */
class Module implements BootstrapListenerInterface
{
    /**
     * Bootstrap
     *
     * @param EventInterface $e Event
     *
     * @return void
     */
    public function onBootstrap(EventInterface $e)
    {
        $sm = $e->getApplication()->getServiceManager();

        /** @var MvcEvent $e */
        $eventManager = $e->getApplication()->getEventManager();

        // This needs to be priority 1
        $payloadValidationListener = $sm->get('PayloadValidationListener');
        $payloadValidationListener->attach($eventManager, 1);

        $eventManager->getSharedManager()->attach(
            \Laminas\Mvc\SendResponseListener::class,
            SendResponseEvent::EVENT_SEND_RESPONSE,
            function (SendResponseEvent $e) {
                $this->logResponse($e->getResponse());
            }
        );

        $this->setLoggerUser($e->getApplication()->getServiceManager());
        $this->initDoctrineEncrypterType($sm->get('config'));
    }

    /**
     * Config
     *
     * @return mixed
     */
    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Set the user ID in the log processor so that it can be included in the log files
     *
     * @param \Laminas\ServiceManager\ServiceManager $serviceManager Service Manager
     *
     * @return void
     */
    private function setLoggerUser(\Laminas\ServiceManager\ServiceManager $serviceManager)
    {
        $authService = $serviceManager->get(\LmcRbacMvc\Service\AuthorizationService::class);
        $serviceManager->get('LogProcessorManager')->get(\Olcs\Logging\Log\Processor\UserId::class)
            ->setUserId($authService->getIdentity()->getUser()->getLoginId());
    }

    /**
     * Add details of the response to the log
     *
     * @param \Laminas\Stdlib\ResponseInterface $response Response
     *
     * @return void
     */
    protected function logResponse(\Laminas\Stdlib\ResponseInterface $response)
    {
        $content = $response->getContent();
        if (strlen((string) $content) > 1000) {
            $content = substr((string) $content, 0, 1000) . '...';
        }

        Logger::logResponse(
            $response->getStatusCode(),
            'API Response Sent',
            ['status' => $response->getStatusCode(), 'content' => $content]
        );
    }

    /**
     * Initialise the Doctrine Encrypter Type with a cipher
     */
    protected function initDoctrineEncrypterType(array $config): void
    {
        if (!empty($config['olcs-doctrine']['encryption_key'])) {
            /** @var \Dvsa\Olcs\Api\Entity\Types\EncryptedStringType $encrypterType */
            $encrypterType = \Doctrine\DBAL\Types\Type::getType('encrypted_string');

            /**
             * @link https://dvsa.atlassian.net/browse/OLCS-17482
             * There was a backwards incompatible change to encryption in September 2017
             *
             * @link https://dvsa.atlassian.net/browse/VOL-6634
             * Following the upgrade to phpseclib v3 (October 2025), Crypt\AES() now requires a mode setting.
             * Under the previous phpseclib v2 the default was cbc with openssl, which we were using.
             * Therefore, we're forcing these for now in v3 so we can maintain existing behaviour
             *
             * The old code also didn't set an iv value. Under the previous phpseclib v2 this fell back to a default.
             * In phpseclib v3 this now causes an error
             *
             * For now we're implicitly using the old default iv value. For the future we need to replace this with
             * something more secure, and ideally, re-encrypt the old data
             *
             * @link https://dvsa.atlassian.net/browse/VOL-6803
             * This is the ticket to improve the code as detailed above
             */
            $cipher = new Crypt\AES('cbc'); //default from phpseclib v2, change going forward
            $cipher->setPreferredEngine('OpenSSL'); //default from phpseclib v2, move to libsodium in future

            $iv  = str_repeat("\0", 16); //default from phpseclib v2, needs to be random going forward
            $cipher->setIV($iv);
            $cipher->setKey($config['olcs-doctrine']['encryption_key']);

            $encrypterType->setEncrypter($cipher);
        }
    }
}
