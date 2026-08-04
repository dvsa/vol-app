<?php

/**
 * Confirm goods disc printing
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\GoodsDisc;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/goods-disc/confirm-printing")
 * @Transfer\Method("POST")
 */
final class ConfirmPrinting extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    public $niFlag;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}})
     * @Transfer\Optional
     */
    protected $licenceType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $endNumber;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $startNumber;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $discSequence;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $isSuccessfull;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $queueId;

    /**
     * Get a NI flag
     *
     * @return string
     */
    public function getNiFlag()
    {
        return $this->niFlag;
    }

    /**
     * Get a licence type
     *
     * @return string
     */
    public function getLicenceType()
    {
        return $this->licenceType;
    }

    /**
     * Get a start number
     *
     * @return int
     */
    public function getStartNumber()
    {
        return $this->startNumber;
    }

    /**
     * Get an end number
     *
     * @return int
     */
    public function getEndNumber()
    {
        return $this->endNumber;
    }

    /**
     * Get a disc sequence
     *
     * @return int
     */
    public function getDiscSequence()
    {
        return $this->discSequence;
    }

    /**
     * Get isSuccessfull flag
     *
     * @return bool
     */
    public function getIsSuccessfull()
    {
        return $this->isSuccessfull;
    }

    /**
     * Get queue id
     *
     * @return int
     */
    public function getQueueId()
    {
        return $this->queueId;
    }
}
