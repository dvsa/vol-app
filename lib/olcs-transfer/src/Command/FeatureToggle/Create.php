<?php

/**
 * Create feature toggle
 */

namespace Dvsa\Olcs\Transfer\Command\FeatureToggle;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType\Traits\FeatureToggleConfigName;
use Dvsa\Olcs\Transfer\FieldType\Traits\FeatureToggleFriendlyName;
use Dvsa\Olcs\Transfer\FieldType\Traits\FeatureToggleStatus;

/**
 * @Transfer\RouteName("backend/feature-toggle")
 * @Transfer\Method("POST")
 */
final class Create extends AbstractCommand
{
    use FeatureToggleFriendlyName;
    use FeatureToggleConfigName;
    use FeatureToggleStatus;
}
