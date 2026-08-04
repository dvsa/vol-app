<?php

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Lva\AbstractDeleteCompanySubsidiary;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @Transfer\RouteName("backend/application/named-single/company-subsidiary")
 * @Transfer\Method("DELETE")
 */
final class DeleteCompanySubsidiary extends AbstractDeleteCompanySubsidiary
{
    use Application;
}
