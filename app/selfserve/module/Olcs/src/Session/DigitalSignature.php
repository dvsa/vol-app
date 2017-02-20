<?php

namespace Olcs\Session;

/**
 * Class DigitalSignature
 */
class DigitalSignature extends \Zend\Session\Container
{
    const SESSION_NAME = 'DigitalSignature';

    /**
     * DigitalSignature constructor.
     */
    public function __construct()
    {
        parent::__construct(self::SESSION_NAME);
    }

    /**
     * Has Application ID been set
     *
     * @return bool
     */
    public function hasApplicationId()
    {
        return $this->offsetExists('applicationId');
    }

    /**
     * Set Application ID
     *
     * @param int $applicationId Application ID
     *
     * @return void
     */
    public function setApplicationId($applicationId)
    {
        $this->applicationId = $applicationId;
    }

    /**
     * Get Application ID
     *
     * @return int
     */
    public function getApplicationId()
    {
        return (int) $this->applicationId;
    }
}
