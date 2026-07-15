<?php

/**
 * Update Business Type
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Organisation;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/organisation/single/business-type")
 * @Transfer\Method("PUT")
 */
final class UpdateBusinessType extends AbstractCommand
{
    use Identity;
    use ApplicationOptional;
    use LicenceOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $variation;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"org_t_p","org_t_pa","org_t_rc","org_t_llp","org_t_st","org_t_ir"}})
     * @Transfer\Optional
     */
    protected $businessType;

    /**
     * @Transfer\Filter("Laminas\Filter\Boolean")
     * @Transfer\Optional
     */
    protected $confirm = false;

    /**
     * @return mixed
     */
    public function getVariation()
    {
        return $this->variation;
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return mixed
     */
    public function getBusinessType()
    {
        return $this->businessType;
    }

    /**
     * @return mixed
     */
    public function getConfirm()
    {
        return $this->confirm;
    }
}
