<?php

namespace Dvsa\Olcs\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/licence/single/surrender")
 * @Transfer\Method("POST")
 */
class Create extends AbstractCommand
{
    use Identity;


    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"surr_sts_start"}})
     */
    protected $status = 'surr_sts_start';

    public function getStatus()
    {
        return $this->status;
    }
}
