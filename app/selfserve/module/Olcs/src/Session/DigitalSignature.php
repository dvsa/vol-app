<?php

namespace Olcs\Session;

/**
 * Class DigitalSignature
 */
class DigitalSignature extends \Zend\Session\Container
{


    /**
     * @param mixed $transportManagerApplication
     */
    public function setTransportManagerApplication($transportManagerApplication): void
    {
        $this->transportManagerApplication = $transportManagerApplication;
    }

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
        return (int)$this->applicationId;
    }

    public function getSignatureType(): int
    {
        return (int)$this->signatureType();
    }

    public function setSignatureType($signatureType): void
    {
        $this->signatureType = $signatureType;
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

    public function hasTransportManagerApplicationId()
    {
        return $this->offsetExists('transportManagerApplicationId');
    }

    /**
     * getTransportManagerApplication
     *
     * @return int
     */
    public function getTransportManagerApplication(): int
    {
        return (int)$this->transportManagerApplication;
    }
}
