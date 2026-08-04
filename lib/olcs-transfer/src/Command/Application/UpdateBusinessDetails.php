<?php

namespace Dvsa\Olcs\Transfer\Command\Application;

use Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails;
use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/organisation/business-details/application")
 * @Transfer\Method("PUT")
 */
final class UpdateBusinessDetails extends AbstractSaveBusinessDetails
{
    use Traits\Licence;
}
