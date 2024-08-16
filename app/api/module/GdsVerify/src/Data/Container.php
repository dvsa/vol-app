<?php

namespace Dvsa\Olcs\GdsVerify\Data;

use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class Container
 * @package Dvsa\Olcs\GdsVerify\Data
 */
class Container extends \SAML2\Compat\AbstractContainer
{
    /**
     * @var LoggerInterface
     */
    private $debugLogger;

    /**
     * Container constructor.
     *
     * @param LoggerInterface $logger Logger
     */
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    /**
     * Provide logger to SAML2
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * SAML show debug message
     *
     * @param string|object $message Message to log
     * @param string $type    Type of message
     *
     * @return void
     */
    public function debugMessage($message, $type)
    {
        if ($this->debugLogger instanceof LoggerInterface) {
            if (is_object($message)) {
                $message = $message::class;
            }
            $this->debugLogger->debug($type . ' - ' . $message);
        }
    }

    /**
     * Generate unique ID
     *
     * @return string
     */
    public function generateId()
    {
        return '_' . Uuid::uuid4()->toString();
    }

    /**
     * No op
     *
     * @param string $url  URL
     * @param array  $data Date
     *
     * @return void
     */
    public function postRedirect($url, $data = [])
    {
    }

    /**
     * No op
     *
     * @param string $url  URL
     * @param array  $data Date
     *
     * @return void
     */
    public function redirect($url, $data = [])
    {
    }

    /**
     * Set the debug logger
     *
     * @param LoggerInterface $logger Logger
     *
     * @return void
     */
    public function setDebugLog(LoggerInterface $logger)
    {
        $this->debugLogger = $logger;
    }
}
