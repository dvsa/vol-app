<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class StartController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_SURRENDER_ENABLED
    ];

    protected $templateConfig = [
        'index' => 'licence/surrender-index'
    ];

    public function IndexAction()
    {
        $translateService = $this->getServiceLocator()->get('Helper\Translation');
        $view = parent::genericAction();
        $view->setVariable('pageTitle', 'licence.surrender.start.title.psv');
        $view->setVariable('body', 'markup-licence-surrender-start');
        $view->setVariable('bodyReplace', [
            $translateService->translate('licence.surrender.start.cancel.bus')
        ]);
        return $view;
    }
}
