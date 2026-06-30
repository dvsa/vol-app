<?php

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\Lva\AbstractUpdateCompanySubsidiary;
use Dvsa\Olcs\Transfer\FieldType\Traits\Application;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @author Rob Caiger <rob@clocal.co.uk>
 *
 * @Transfer\RouteName("backend/application/named-single/company-subsidiary/single")
 * @Transfer\Method("PUT")
 */
final class UpdateCompanySubsidiary extends AbstractUpdateCompanySubsidiary
{
    use Application;
}
