<?php

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\Lva\AbstractCreateCompanySubsidiary;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @Transfer\RouteName("backend/licence/named-single/company-subsidiary")
 * @Transfer\Method("POST")
 */
final class CreateCompanySubsidiary extends AbstractCreateCompanySubsidiary
{
    use Licence;
}
