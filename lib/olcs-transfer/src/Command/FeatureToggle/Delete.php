<?php

/**
 * Delete feature toggle
 */

namespace Dvsa\Olcs\Transfer\Command\FeatureToggle;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractDeleteCommand;

/**
 * @Transfer\RouteName("backend/feature-toggle/single")
 * @Transfer\Method("DELETE")
 */
final class Delete extends AbstractDeleteCommand
{
}
