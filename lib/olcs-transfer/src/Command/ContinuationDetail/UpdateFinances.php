<?php

namespace Dvsa\Olcs\Transfer\Command\ContinuationDetail;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\Version;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/continuation-detail/single/finances")
 * @Transfer\Method("PUT")
 */
final class UpdateFinances extends AbstractCommand
{
    use Identity;
    use Version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Money", options={"allow_negative": true})
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": -9999999999, "max": 9999999999})
     * @Transfer\Optional
     */
    protected $averageBalanceAmount;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $hasOverdraft;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Money")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 9999999999})
     * @Transfer\Optional
     */
    protected $overdraftAmount;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $hasFactoring;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Money")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 9999999999})
     * @Transfer\Optional
     */
    protected $factoringAmount;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\YesNo")
     * @Transfer\Optional
     */
    protected $hasOtherFinances;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Dvsa\Olcs\Transfer\Validators\Money")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 9999999999})
     * @Transfer\Optional
     */
    protected $otherFinancesAmount;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1,"max":200})
     * @Transfer\Optional
     */
    protected $otherFinancesDetails;

    /**
     * @return float
     */
    public function getAverageBalanceAmount()
    {
        return $this->averageBalanceAmount;
    }

    /**
     * @return string Y or N
     */
    public function getHasOverdraft()
    {
        return $this->hasOverdraft;
    }

    /**
     * @return float
     */
    public function getOverdraftAmount()
    {
        return $this->overdraftAmount;
    }

    /**
     * @return string Y or N
     */
    public function getHasOtherFinances()
    {
        return $this->hasOtherFinances;
    }

    /**
     * @return float
     */
    public function getOtherFinancesAmount()
    {
        return $this->otherFinancesAmount;
    }

    /**
     * @return string
     */
    public function getOtherFinancesDetails()
    {
        return $this->otherFinancesDetails;
    }

    /**
     * @return string Y or N
     */
    public function getHasFactoring()
    {
        return $this->hasFactoring;
    }

    /**
     * @return float
     */
    public function getFactoringAmount()
    {
        return $this->factoringAmount;
    }
}
