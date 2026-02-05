<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Permits\AcceptIrhpPermits;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpAwaitingFeeController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_AWAITING_FEE,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_IS_AWAITING_FEE,
    ];

    protected $tableConfig = [
        'generic' => TableConfig::WANTED_UNPAID_IRHP_PERMITS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_ACCEPT_AND_PAY,
    ];

    protected $templateConfig = [
        'default' => 'permits/irhp-awaiting-fee',
    ];

    protected $templateVarsConfig = [
        'default' => [
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'browserTitle' => 'permits.page.irhp.awaiting-fee.browser.title',
            'heading' => 'permits.page.irhp.awaiting-fee.title',
            'backUri' => IrhpApplicationSection::ROUTE_PERMITS,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_PAYMENT_ACTION,
            'conditional' => [
                'dataKey' => 'application',
                'params' => 'id',
                'step' => [
                    'command' => AcceptIrhpPermits::class,
                    'route' => IrhpApplicationSection::ROUTE_SUBMITTED,
                ],
                'field' => 'hasOutstandingFees',
                'value' => false,
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

    #[\Override]
    public function handlePost()
    {
        if (isset($this->postParams['Submit']['DeclineButton'])) {
            return $this->nextStep(IrhpApplicationSection::ROUTE_DECLINE_APPLICATION);
        } elseif (isset($this->postParams['Submit']['RemoveUnwantedPermitButton'])) {
            return $this->nextStep(IrhpApplicationSection::ROUTE_CANDIDATE_SELECTION);
        }

        return parent::handlePost();
    }

    /**
     * @param \Common\Form\Form $form
     *
     * @return \Common\Form\Form
     */
    #[\Override]
    public function alterForm($form)
    {
        if (!$this->data[IrhpAppDataSource::DATA_KEY]['hasOutstandingFees']) {
            $form->get('Submit')->get('SubmitButton')->setLabel('permits.page.accept');
        }

        if (!$this->data[IrhpAppDataSource::DATA_KEY]['canSelectCandidatePermits']) {
            $form->get('Submit')->remove('RemoveUnwantedPermitButton');
        }

        return $form;
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        $this->data = $this->mapperManager
            ->get(IrhpApplicationFeeSummary::class)
            ->mapForDisplay($this->data);

        if ($this->data[IrhpAppDataSource::DATA_KEY]['canSelectCandidatePermits']) {
            $this->templateVarsConfig['default']['heading'] = 'permits.page.irhp.awaiting-fee.select-candidate-permits.title';
        }
    }
}
