<?php

/**
 * Grant Variation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Variation;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/variation/single/grant")
 * @Transfer\Method("PUT")
 */
final class Grant extends AbstractCommand
{
    use Identity;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"grant_authority_dl", "grant_authority_tc", "grant_authority_tr"}})
     */
    protected $grantAuthority;

    /**
     * @return string
     */
    public function getGrantAuthority()
    {
        return $this->grantAuthority;
    }
}
