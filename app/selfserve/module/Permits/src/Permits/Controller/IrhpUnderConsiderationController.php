<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Data\Mapper\IrhpApplicationFeeSummary;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpUnderConsiderationController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_UNDER_CONSIDERATION,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_UNDER_CONSIDERATION,
    ];

    protected $templateConfig = [
        'generic' => 'permits/irhp-under-consideration'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'backUri' => IrhpApplicationSection::ROUTE_PERMITS,
            'browserTitle' => 'permits.irhp.under-consideration.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
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
    }
}
