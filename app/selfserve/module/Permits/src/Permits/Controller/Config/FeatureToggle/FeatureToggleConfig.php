<?php

namespace Permits\Controller\Config\FeatureToggle;

use Common\FeatureToggle;

/**
 * Holds feature toggle configs that are used regularly
 */
class FeatureToggleConfig
{
    public const SELFSERVE_SURRENDER_ENABLED = [
        FeatureToggle::SELFSERVE_SURRENDER
    ];
}
