<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpNoLicencesController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $templateConfig = [
        'generic' => 'permits/no-licences'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'title' => 'permits.page.irhp-no-licences.title',
            'browserTitle' => 'permits.page.irhp-no-licences.browser.title',
        ]
    ];
}
