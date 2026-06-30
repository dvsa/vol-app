<?php

/**
 * Update feature toggle
 */

namespace Dvsa\Olcs\Transfer\Command\FeatureToggle;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\FeatureToggleConfigName;
use Dvsa\Olcs\Transfer\FieldType\Traits\FeatureToggleFriendlyName;
use Dvsa\Olcs\Transfer\FieldType\Traits\FeatureToggleStatus;

/**
 * @Transfer\RouteName("backend/feature-toggle/single")
 * @Transfer\Method("PUT")
 */
final class Update extends AbstractCommand
{
    use Identity;
    use FeatureToggleFriendlyName;
    use FeatureToggleConfigName;
    use FeatureToggleStatus;
}
