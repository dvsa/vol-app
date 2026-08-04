<?php

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Command\AbstractSaveBusinessDetails;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/organisation/business-details/licence")
 * @Transfer\Method("PUT")
 */
final class UpdateBusinessDetails extends AbstractSaveBusinessDetails
{
}
