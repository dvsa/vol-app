<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_OVERVIEW,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_OVERVIEW_ACCESSIBLE,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_IRHP_OVERVIEW_SUBMIT,
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

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_PERMITS,
            'conditional' => [
                'dataKey' => 'application',
                'params' => 'id',
                'step' => [
                    'command' => SubmitApplication::class,
                    'route' => IrhpApplicationSection::ROUTE_SUBMITTED,
                ],
                'field' => 'hasOutstandingFees',
                'value' => false
            ]
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
     * Retrieve data for the specified DTOs
     *
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        if (isset($this->data['questionAnswer']['countries'])) {
            $this->templateConfig['default'] = 'permits/irhp-application-overview-bilateral';

            // If the declaration has been completed, the status of the submitAndPay section also needs to be specified
            // by the backend as 'completed' so that the conditional display requirements of the fee page are met.
            // However, we need to display the status of the submitAndPay section on the overview page as 'not started'
            // if the fee is not fully paid. This seems like a sensible way to do this without drastic changes
            // elsewhere.
            $reviewAndSubmit = $this->data['questionAnswer']['reviewAndSubmit'];
            $countriesStatus = $reviewAndSubmit['countries'];
            $declarationStatus = $reviewAndSubmit['declaration'];
            $submitAndPayStatus = $reviewAndSubmit['submitAndPay'];
            $hasOutstandingFees = $this->data['application']['hasOutstandingFees'];

            if ($hasOutstandingFees) {
                // outstanding fees
                if ($submitAndPayStatus == IrhpApplicationSection::SECTION_COMPLETION_COMPLETED) {
                    // declaration completed
                    $submitAndPayStatus = IrhpApplicationSection::SECTION_COMPLETION_NOT_STARTED;
                }
            } else {
                // no outstanding fees
                if ($countriesStatus == IrhpApplicationSection::SECTION_COMPLETION_COMPLETED) {
                    // all countries completed
                    $submitAndPayStatus = IrhpApplicationSection::SECTION_COMPLETION_COMPLETED;
                }
            }

            $this->data['questionAnswer']['reviewAndSubmit']['submitAndPay'] = $submitAndPayStatus;

            // possibly display SubmitApplication button
            $this->data['displaySubmitApplicationButton'] = false;

            if (
                $declarationStatus == IrhpApplicationSection::SECTION_COMPLETION_COMPLETED
                && $submitAndPayStatus == IrhpApplicationSection::SECTION_COMPLETION_COMPLETED
            ) {
                $this->data['displaySubmitApplicationButton'] = true;
            }
        }
    }
}
