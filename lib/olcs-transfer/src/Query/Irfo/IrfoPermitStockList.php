<?php

namespace Dvsa\Olcs\Transfer\Query\Irfo;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * Class IrfoPermitStockList
 * @Transfer\RouteName("backend/irfo/permit-stock")
 */
class IrfoPermitStockList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irfoCountry;

    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 1900})
     */
    protected $validForYear;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"irfo_perm_s_s_ret","irfo_perm_s_s_void","irfo_perm_s_s_issued","irfo_perm_s_s_in_stock"}})
     * @Transfer\Optional
     */
    protected $status;

    /**
     * @return int
     */
    public function getIrfoCountry()
    {
        return $this->irfoCountry;
    }

    /**
     * @return int
     */
    public function getValidForYear()
    {
        return $this->validForYear;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
