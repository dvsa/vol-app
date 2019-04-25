<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpPermitType;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpValidPermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::IRHP_VALID,
    ];

    protected $tableConfig = [
        'generic' => TableConfig::VALID_IRHP_PERMITS,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-valid-permits',
    ];

    protected $templateVarsConfig = [
        'generic' => []
    ];

    public function mergeTemplateVars()
    {
        // overwrite default page title
        $title = $this->data[IrhpPermitType::DATA_KEY]['name']['description'];

        $this->templateVarsConfig['generic']['browserTitle'] = $title;
        $this->templateVarsConfig['generic']['title'] = $title;

        parent::mergeTemplateVars();
    }

    public function retrieveTables()
    {
        if ($this->data[IrhpPermitType::DATA_KEY]['isBilateral']) {
            $this->tableConfig = [
                'generic' => TableConfig::VALID_IRHP_PERMITS_BILATERAL,
            ];
        }
        parent::retrieveTables();
    }
}
