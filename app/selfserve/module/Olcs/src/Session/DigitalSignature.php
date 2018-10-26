<?php

namespace Olcs\Session;

/**
 * Class DigitalSignature
 *
 *
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

    public function hasLva()
    {
        return $this->offsetExists('lva');
    }

    public function hasRole()
    {
        return $this->offsetExists('role');
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
        return (int)$this->applicationId;
    }

    /**
     * Has ContinuationDetail ID been set
     *
     * @return bool
     */
    public function hasContinuationDetailId()
    {
        return $this->offsetExists('continuationDetailId');
    }

    /**
     * Set ContinuationDetail ID
     *
     * @param int $continuationDetailId ContinuationDetail ID
     *
     * @return void
     */
    public function setContinuationDetailId($continuationDetailId)
    {
        $this->continuationDetailId = $continuationDetailId;
    }

    /**
     * Get ContinuationDetail ID
     *
     * @return int
     */
    public function getContinuationDetailId()
    {
        return (int)$this->continuationDetailId;
    }

    public function setApplication($id)
    {
        $this->setApplicationId($id);
    }

    public function setContinuationDetail($id)
    {
        $this->setContinuationDetailId($id);
    }

    public function getRole()
    {
        return $this->role;
    }

    /**
     * setRole
     *
     * @param $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    public function setTransportManagerApplicationId($transportManagerApplicationId)
    {
        $this->transportManagerApplicationId = $transportManagerApplicationId;
    }

    public function hasTransportManagerApplicationId()
    {
        return $this->offsetExists('transportManagerApplicationId');
    }


    /**
     * @return mixed
     */
    public function getTransportManagerApplicationId(): int
    {
        return (int)$this->transportManagerApplicationId;
    }

    /**
     * @return mixed
     */
    public function getLva(): ?string
    {
        return $this->lva;
    }

    /**
     * @param mixed $lva
     */
    public function setLva($lva): void
    {
        $this->lva = $lva;
    }
}
