<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCheckAnswers;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;

use Permits\View\Helper\EcmtSection;

class CheckAnswersController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => [
            FeatureToggle::SELFSERVE_ECMT
        ],
    ];

    protected $dataSourceConfig = [
        'default' => [
            PermitAppDataSource::class,
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
            PermitAppDataSource::DATA_KEY => [
                'key' => 'canCheckAnswers',
                'value' => true
            ],
        ],
    ];

    public function checkAnswersAction()
    {
        if (!empty($this->postParams)) {
            $command = UpdateEcmtCheckAnswers::create(['id' => $this->routeParams['id']]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_DECLARATION);
        }

        return $this->genericAction();
    }
}
