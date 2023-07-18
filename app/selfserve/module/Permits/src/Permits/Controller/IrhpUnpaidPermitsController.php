<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Table\TableConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpUnpaidPermitsController extends AbstractSelfserveController
{
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
        'generic' => [
            'backUri' => IrhpApplicationSection::ROUTE_AWAITING_FEE,
            'browserTitle' => 'permits.irhp.unpaid.permits.browser.title',
            'title' => 'permits.irhp.unpaid.permits.title',
        ]
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
}
