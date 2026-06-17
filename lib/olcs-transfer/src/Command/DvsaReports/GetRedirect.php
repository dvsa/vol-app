<?php

namespace Dvsa\Olcs\Transfer\Command\DvsaReports;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/dvsa-reports/get-redirect")
 * @Transfer\Method("POST")
 */
class GetRedirect extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1})
     */
    protected $jwt;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1})
     */
    protected $refreshToken;

    /**
     * @Transfer\ArrayInput
     */
    protected $olNumbers = [];

    public function getJwt()
    {
        return $this->jwt;
    }

    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    public function getOlNumbers()
    {
        return $this->olNumbers;
    }
}
