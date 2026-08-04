<?php

/**
 * Complete Transaction
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Command\Transaction;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/transaction/by-reference")
 * @Transfer\Method("POST")
 */
class CompleteTransaction extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":1})
     */
    protected $reference;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"fpm_card_online", "fpm_card_offline"}})
     */
    protected $paymentMethod;

    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     */
    protected $cpmsData = [];

    /**
     * If payment is for an application submission, supply the application id here
     * and it will be submitted if payment succeeds
     *
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $submitApplicationId;


    /**
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @return string
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * @return array
     */
    public function getCpmsData()
    {
        return $this->cpmsData;
    }

    /**
     * @return int
     */
    public function getSubmitApplicationId()
    {
        return $this->submitApplicationId;
    }
}
