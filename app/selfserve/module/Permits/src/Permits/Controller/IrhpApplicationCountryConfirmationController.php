<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCountries;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\AvailableCountries;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\ConfirmedUpdatedCountries;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationCountryConfirmationController extends AbstractSelfserveController
{
    public const REMOVED_COUNTRY_CODES_KEY = 'removedCountryCodes';
    public const VALIDATED_SELECTED_COUNTRY_CODES_CSV_KEY = 'validatedSelectedCountryCodesCsv';

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_COUNTRIES,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_READY_FOR_COUNTRIES,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_COUNTRIES_CONFIRMATION,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.page.bilateral.countries-confirmation.browser.title',
            'question' => 'permits.page.bilateral.countries-confirmation.question',
            'backUri' => IrhpApplicationSection::ROUTE_COUNTRIES,
            'additionalGuidance' => [
                'disableHtmlEscape' => true,
                'value' => 'permits.page.bilateral.countries-confirmation.additional-guidance',
            ]
        ]
    ];

    protected $postConfig = [
        'default' => [
            'mapperClass' => ConfirmedUpdatedCountries::class,
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => UpdateCountries::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'saveAndReturnStep' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * Extend method to generate lists of removed and selected country codes from input passed in via querystring
     * or post data
     *
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        $countryCodesCsv = '';

        if (isset($this->postParams['fields']['countries']) && is_string($this->postParams['fields']['countries'])) {
            $countryCodesCsv = $this->postParams['fields']['countries'];
        } elseif (isset($this->queryParams['countries']) && is_string($this->queryParams['countries'])) {
            $countryCodesCsv = $this->queryParams['countries'];
        }

        $selectedCountryCodes = explode(',', $countryCodesCsv);
        $availableCountryCodes = array_column($this->data[AvailableCountries::DATA_KEY]['countries'], 'id');
        $validatedSelectedCountryCodes = array_intersect($availableCountryCodes, $selectedCountryCodes);
        $linkedCountryCodes = array_column($this->data[IrhpApplication::DATA_KEY]['countrys'], 'id');
        $this->data[self::REMOVED_COUNTRY_CODES_KEY] = array_diff($linkedCountryCodes, $validatedSelectedCountryCodes);
        $this->data[self::VALIDATED_SELECTED_COUNTRY_CODES_CSV_KEY] = implode(',', $validatedSelectedCountryCodes);

        $canUpdateCountries = $this->data[IrhpApplication::DATA_KEY]['canUpdateCountries'];
        $canShowPage = (
            !empty($this->data[self::VALIDATED_SELECTED_COUNTRY_CODES_CSV_KEY]) &&
            !empty($this->data[self::REMOVED_COUNTRY_CODES_KEY])
        );

        $this->data[IrhpApplication::DATA_KEY]['canUpdateCountries'] = $canUpdateCountries && $canShowPage;
    }

    /**
     * Extend method to add the list of selected countries to the querystring on the back button
     *
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        $this->templateVarsConfig['default']['backUriOptions'] = [
            'query' => [
                'countries' => $this->data[self::VALIDATED_SELECTED_COUNTRY_CODES_CSV_KEY]
            ]
        ];

        parent::mergeTemplateVars();
    }

    /**
     * Extend method to handle the cancel button if pressed, and replace any submitted post data with the filtered
     * version of that data
     */
    #[\Override]
    public function handlePost()
    {
        if (isset($this->postParams['Submit']['CancelButton'])) {
            return $this->redirect()->toRoute(
                IrhpApplicationSection::ROUTE_COUNTRIES,
                [],
                [
                    'query' => [
                        'countries' => $this->data[self::VALIDATED_SELECTED_COUNTRY_CODES_CSV_KEY]
                    ]
                ],
                true
            );
        }

        if (isset($this->postParams['fields']['countries'])) {
            $this->postParams['fields']['countries'] = $this->data[self::VALIDATED_SELECTED_COUNTRY_CODES_CSV_KEY];
        }

        return parent::handlePost();
    }
}
