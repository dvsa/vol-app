<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpUnpaidPermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::IRHP_UNPAID_PERMITS,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_VIEW_CANDIDATE_PERMITS,
    ];

    protected $tableConfig = [
        'generic' => TableConfig::UNPAID_IRHP_PERMITS,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-unpaid-permits',
    ];

    protected $templateVarsConfig = [
        'generic' => []
    ];

    public function mergeTemplateVars()
    {
        // overwrite default page title
        $title = $this->data[IrhpApplication::DATA_KEY]['irhpPermitType']['name']['description'];

        $this->templateVarsConfig['generic']['browserTitle'] = $title;
        $this->templateVarsConfig['generic']['title'] = $title;

        parent::mergeTemplateVars();
    }
}
