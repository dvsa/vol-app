<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\FeatureToggle;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\FeeList as FeeListDto;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Data\Mapper\FeeList as FeeListMapper;

class OverviewController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $genericTemplate = 'permits/application-overview';

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

    protected $conditionalDisplayConfig = [
        'default' => [
            PermitAppDataSource::DATA_KEY => [
                'key' => 'isNotYetSubmitted',
                'value' => true
            ],
        ],
    ];
}
