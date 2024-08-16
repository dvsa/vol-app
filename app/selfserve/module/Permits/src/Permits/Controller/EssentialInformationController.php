<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\BackToOverview;
use Permits\View\Helper\IrhpApplicationSection;

class EssentialInformationController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_ESSENTIAL_INFORMATION,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_VIEW_ESSENTIAL_INFORMATION,
    ];

    protected $templateConfig = [
        'default' => 'permits/essential-information',
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.page.bilateral.countries.essential.heading',
            'continueUri' => IrhpApplicationSection::ROUTE_PERIOD,
            'continueUriLabel' => 'permits.button.continue',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'backUriLabel' => BackToOverview::STANDARD_BACK_LINK_LABEL,
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
}
