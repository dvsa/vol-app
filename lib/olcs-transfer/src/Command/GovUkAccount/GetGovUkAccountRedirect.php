<?php

namespace Dvsa\Olcs\Transfer\Command\GovUkAccount;

use Dvsa\Olcs\Transfer\FieldType\Traits\GovUkAccountJourney;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\TmVerifyRole;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/govuk-account/get-govuk-account-redirect")
 * @Transfer\Method("POST")
 */
class GetGovUkAccountRedirect extends AbstractCommand
{
    use Identity;
    use TmVerifyRole;
    use GovUkAccountJourney;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":1})
     */
    protected $returnUrl;

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $returnUrlOnError;

    public function getReturnUrlOnError()
    {
        return $this->returnUrlOnError;
    }
}
