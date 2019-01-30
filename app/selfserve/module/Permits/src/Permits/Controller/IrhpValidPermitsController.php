<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpValidPermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::PERMIT_IRHP_VALID,
    ];

    protected $tableConfig = [
        'generic' => TableConfig::VALID_IRHP_OVERVIEW,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-permits-overview',
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.irhp.valid.permits.title',
            'title' => 'permits.irhp.valid.permits.title'
        ]
    ];
}
