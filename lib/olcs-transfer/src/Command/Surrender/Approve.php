<?php

namespace Dvsa\Olcs\Transfer\Command\Surrender;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/licence/single/surrender/approve")
 * @Transfer\Method("POST")
 */
class Approve extends AbstractCommand
{
    use Identity;

    /**
     * @var \DateTime
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d"})
     */
    protected $surrenderDate;

    /**
     * @return mixed
     */
    public function getSurrenderDate()
    {
        return $this->surrenderDate;
    }
}
