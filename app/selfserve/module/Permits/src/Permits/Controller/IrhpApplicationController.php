<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\IrhpApplication;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class IrhpApplicationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_OVERVIEW,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_OVERVIEW_ACCESSIBLE,
    ];

    protected $templateConfig = [
        'default' => 'permits/irhp-application-overview'
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.application.overview.browser.title',
            'prependTitleDataKey' => IrhpApplication::DATA_KEY,
        ]
    ];

    /**
     * Retrieve data for the specified DTOs
     */
    public function retrieveData()
    {
        parent::retrieveData();

        if (isset($this->data['questionAnswer']['countries'])) {
            $this->data['fromDashboard'] = isset($this->queryParams['fromDashboard']);
            $this->templateConfig['default'] = 'permits/irhp-application-overview-bilateral';
        }
    }
}
