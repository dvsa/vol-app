<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCountries;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\RestrictedCountries;
use Permits\View\Helper\EcmtSection;

class RestrictedCountriesController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_RESTRICTED_COUNTRIES
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_RESTRICTED_COUNTRIES,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.restricted-countries.title',
            'question' => 'permits.page.restricted-countries.question',
            'guidance' => [
                'permits.page.restricted-countries.guidance.line.1',
            ],
        ],
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => false,
            'command' => UpdateEcmtCountries::class,
            'mapperClass' => RestrictedCountries::class,
            'preprocessMethod' => 'preprocessFormData',
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
            'saveAndReturnStep' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];
}
