<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\View\Helper\IrhpApplicationSection;

class YearController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_YEAR,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_SELECT_YEAR,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_YEAR,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.year.browser.title.one-year-available',
            'question' => 'permits.page.year.question.one-year-available',
            'hint' => 'permits.page.year.hint.one-year-available',
            'guidance' => [
                'value' => 'permits.page.year.ecmt-short-term.guidance',
                'disableHtmlEscape' => true,
            ],
            'backUri' => IrhpApplicationSection::ROUTE_TYPE,
            'cancelUri' => IrhpApplicationSection::ROUTE_PERMITS,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'params' => [
                'route' => [
                    'type',
                ]
            ],
            'step' => IrhpApplicationSection::ROUTE_ADD_LICENCE,
        ],
    ];

    /**
     * @param array $config
     * @param array $params
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handlePostCommand(array &$config, array $params)
    {
        $this->redirectParams = [
            'year' => $params['year'],
            'type' => $params['type']
        ];
    }
}
