<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/licence/exists-with-operator-admin")
 */
class ExistsWithOperatorAdmin extends AbstractQuery
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength", options={"min":9, "max":9})
     */
    protected $licNo;

    /**
     * @return string
     */
    public function getLicNo()
    {
        return $this->licNo;
    }
}
