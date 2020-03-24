<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdatePeriod;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\AvailableBilateralStocks;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationPeriodController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_PERIODS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_BILATERAL_STOCK,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_BILATERAL_APP_NOT_SUBMITTED,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question-bilateral'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.bilateral.which-period-required',
            'question' => 'permits.page.bilateral.which-period-required',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    protected $postConfig = [
        'question' => [
            'mapperClass' => AvailableBilateralStocks::class,
            'retrieveData' => true,
            'command' => UpdatePeriod::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_IPA_QUESTION
        ],
    ];
}
