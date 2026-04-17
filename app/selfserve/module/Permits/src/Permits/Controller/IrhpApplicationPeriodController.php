<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdatePeriod;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\AvailableBilateralStocks;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationPeriodController extends AbstractSelfserveController
{
    public const SELECTION_CHANGED_WARNING_KEY = 'permits.page.bilateral.which-period-required.warning';

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_PERIODS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_BILATERAL_STOCK,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_SELECT_BILATERAL_PERIOD,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question-bilateral'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.bilateral.which-period-required',
            'question' => 'permits.page.bilateral.which-period-required',
            'backUri' => IrhpApplicationSection::ROUTE_ESSENTIAL_INFORMATION,
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

    /** @var bool */
    private $allowFormValidationSuccess = true;

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
     * {@inheritdoc}
     *
     * @return void
     */
    #[\Override]
    public function handlePost()
    {
        if (
            isset($this->data[IrhpApplicationDataSource::DATA_KEY]['selectedStockId']) &&
            isset($this->postParams['fields']['irhpPermitStock']) &&
            isset($this->postParams['fields']['previousIrhpPermitStock'])
        ) {
            $previouslySubmittedStockId = $this->postParams['fields']['previousIrhpPermitStock'];
            $submittedStockId = $this->postParams['fields']['irhpPermitStock'];
            $storedStockId = $this->data[IrhpApplicationDataSource::DATA_KEY]['selectedStockId'];

            $this->postParams['fields']['previousIrhpPermitStock'] = $this->postParams['fields']['irhpPermitStock'];

            if ($storedStockId != $submittedStockId && $submittedStockId != $previouslySubmittedStockId) {
                $this->data['warning'] = self::SELECTION_CHANGED_WARNING_KEY;

                $this->postParams['fields']['warningVisible'] = 1;
                $this->allowFormValidationSuccess = false;
            }
        }

        parent::handlePost();
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    public function formIsValid()
    {
        return parent::formIsValid() && $this->allowFormValidationSuccess;
    }
}
