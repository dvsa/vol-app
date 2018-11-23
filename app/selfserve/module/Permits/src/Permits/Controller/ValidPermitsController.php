<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\View\Helper\EcmtSection;

class ValidPermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
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
        'valid' => 'permits/valid-permits-overview',
        'unpaid' => 'permits/valid-permits-overview',
    ];

    public function validAction()
    {
        $view = parent::genericAction();
        $view->setVariable('rightColumn', 'markup-ecmt-permit-valid-permits-right-column');
        return $view;
    }

    public function unpaidAction()
    {
        $view = parent::genericAction();
        $view->setVariable('rightColumn', 'markup-ecmt-permit-unpaid-permits-right-column');
        $view->setVariable('returnLink', EcmtSection::ROUTE_ECMT_AWAITING_FEE);
        return $view;
    }
}
