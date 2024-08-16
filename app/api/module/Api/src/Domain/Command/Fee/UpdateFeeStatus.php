<?php

namespace Dvsa\Olcs\Api\Domain\Command\Fee;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

class UpdateFeeStatus extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min": 1})
     * @Transfer\Validator("Laminas\Validator\InArray", options={
     *         "haystack": {"lfs_ot","lfs_pd","lfs_cn", "lfs_refund_pending", "lfs_refund_failed", "lfs_refunded"}
     *     }
     * )
     */
    protected $status;

    public function getStatus()
    {
        return $this->status;
    }
}
