<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\FeeList as FeeListDto;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Data\Mapper\FeeList as FeeListMapper;

use Permits\View\Helper\EcmtSection;

class FeeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $genericTemplate = 'permits/fee';

    protected $toggleConfig = [
        'default' => [
            FeatureToggle::SELFSERVE_ECMT
        ],
    ];

    protected $dataSourceConfig = [
        'default' => [
            PermitAppDataSource::class => [],
            FeeListDto::class => [
                'mapper' => FeeListMapper::class
            ],
        ],
    ];

    protected $formConfig = [
        'default' => [
            'fee' => [
                'formClass' => 'FeesForm',
            ],
        ],
    ];

    public function feeAction()
    {
        if (!empty($this->postParams)) {
            $command = EcmtSubmitApplication::create(['id' => $this->routeParams['id']]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_SUBMITTED);
        }

        return $this->genericAction();
    }
}
