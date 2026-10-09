<?php

namespace Dvsa\Olcs\Api\Domain\Command\Fee;

use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

class UpdateFeeStatus extends AbstractCommand
{
    use Identity;

    #[Transfer\Filter("Laminas\Filter\StringTrim")]
    #[Transfer\Validator("Laminas\Validator\StringLength", options: ["min" => 1])]
    #[Transfer\Validator("Laminas\Validator\InArray", options: ["haystack" => [Fee::STATUS_OUTSTANDING,Fee::STATUS_PAID,Fee::STATUS_CANCELLED, Fee::STATUS_REFUND_PENDING, Fee::STATUS_REFUND_FAILED, Fee::STATUS_REFUNDED]])]
    protected $status;

    public function getStatus()
    {
        return $this->status;
    }
}
