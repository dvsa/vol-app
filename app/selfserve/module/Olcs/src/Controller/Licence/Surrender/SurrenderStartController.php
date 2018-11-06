<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Controller\Lva\AbstractController;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class SurrenderStartController extends AbstractController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    public function indexAction()
    {
        // to be completed
    }
}
