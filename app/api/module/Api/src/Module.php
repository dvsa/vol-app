<?php

namespace Dvsa\Olcs\Api;

use Dvsa\Olcs\Api\Domain\Util\BlockCipher\PhpSecLib;
use Olcs\Logging\Log\Logger;
use phpseclib\Crypt;
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
     * Initialise the Doctrine Encrypter Type with a ciper
     *
     * @param array $config Module config array
     *
     * @return void
     */
    protected function initDoctrineEncrypterType(array $config)
    {
        if (!empty($config['olcs-doctrine']['encryption_key'])) {
            /** @var \Dvsa\Olcs\Api\Entity\Types\EncryptedStringType $encrypterType */
            $encrypterType = \Doctrine\DBAL\Types\Type::getType('encrypted_string');

            // NB OLCS-17482 caused a backwards INCOMPATIBLE change to the way the encryption works
            $cipher = new Crypt\AES();
            // Force AES 256
            $cipher->setKeyLength(256);
            $cipher->setKey($config['olcs-doctrine']['encryption_key']);

            $encrypterType->setEncrypter($cipher);
        }
    }
}
