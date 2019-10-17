<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpNotEligibleController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $templateConfig = [
        'generic' => 'permits/not-eligible'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'title' => 'permits.page.irhp-not-eligible.title',
            'browserTitle' => 'permits.page.irhp-not-eligible.browser.title',
        ]
    ];
}
