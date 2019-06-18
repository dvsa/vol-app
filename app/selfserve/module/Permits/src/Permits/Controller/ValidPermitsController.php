<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class ValidPermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'valid' => DataSourceConfig::PERMIT_ECMT_VALID,
        'unpaid' => DataSourceConfig::PERMIT_ECMT_UNPAID,
    ];

    protected $tableConfig = [
        'valid' => TableConfig::VALID_APP_OVERVIEW,
        'unpaid' => TableConfig::UNPAID_APP_OVERVIEW,
    ];

    protected $templateConfig = [
        'valid' => 'permits/ecmt-valid-permits',
        'unpaid' => 'permits/ecmt-unpaid-permits',
    ];

    public function validAction()
    {
        return parent::genericAction();
    }

    public function unpaidAction()
    {
        return parent::genericAction();
    }
}
