<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCheckAnswers;
use Dvsa\Olcs\Transfer\Query\Permits\ById as PermitApplicationDto;
use Olcs\Controller\AbstractSelfserveController;

use Permits\View\Helper\EcmtSection;

class CheckAnswersController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::SELFSERVE_ECMT
        ],
    ];

    const APP_DATA_SOURCE = 'application';

    protected $dataSourceConfig = [
        'default' => [
            self::APP_DATA_SOURCE => [
                'dto' => PermitApplicationDto::class,
                'params' => ['id'],
            ],
        ],
    ];

    protected $formConfig = [
        'default' => [
            'checkAnswers' => [
                'formClass' => 'CheckAnswersForm',
            ],
        ],
    ];

    protected $conditionalDisplayConfig = [
        'default' => [
            self::APP_DATA_SOURCE => [
                'key' => 'canCheckAnswers',
                'value' => true
            ],
        ],
    ];

    public function checkAnswersAction()
    {
        if (!empty($this->params()->fromPost())) {
            $command = UpdateEcmtCheckAnswers::create(['id' => $this->params['id']]);
            $this->handleCommand($command);
            $this->redirect()->toRoute('permits/' . EcmtSection::ROUTE_ECMT_DECLARATION, [], [], true);
            //$this->nextStep(EcmtSection::ROUTE_ECMT_DECLARATION);
        }

        return $this->genericAction();
    }
}
