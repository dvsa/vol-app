<?php

namespace Dvsa\Olcs\Transfer\Command\User;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/user/selfserve/register/register-consultant-operator")
 * @Transfer\Method("POST")
 */
final class RegisterConsultantAndOperator extends AbstractCommand
{
    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve")
     */
    protected $operatorDetails;

    /**
     * @Transfer\Partial("Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve")
     */
    protected $consultantDetails;

    public function getOperatorDetails()
    {
        return $this->operatorDetails;
    }

    public function getConsultantDetails()
    {
        return $this->consultantDetails;
    }
}
