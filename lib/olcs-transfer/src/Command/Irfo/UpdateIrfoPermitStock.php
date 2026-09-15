<?php

/**
 * Update IrfoPermitStock
 */

namespace Dvsa\Olcs\Transfer\Command\Irfo;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irfo/permit-stock")
 * @Transfer\Method("PUT")
 */
final class UpdateIrfoPermitStock extends AbstractCommand
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $ids = [];

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *     options={
     *         "haystack": {"irfo_perm_s_s_ret","irfo_perm_s_s_void","irfo_perm_s_s_issued","irfo_perm_s_s_in_stock"}
     *     }
     * )
     */
    protected $status;

    /**
     * @return array
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
