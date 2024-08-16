<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCandidatePermitSelection;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\Data\Mapper\SelectedCandidatePermits;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpCandidatePermitSelectionController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'generic' => DataSourceConfig::IRHP_UNPAGINATED_UNPAID_PERMITS,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_SELECT_CANDIDATE_PERMITS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_CANDIDATE_PERMIT_SELECTION,
    ];

    protected $templateConfig = [
        'default' => 'permits/irhp-candidate-permit-selection',
    ];

    protected $templateVarsConfig = [
        'default' => [
            'browserTitle' => 'permits.page.irhp.candidate-permit-selection.browser.title',
        ]
    ];

    protected $postConfig = [
        'default' => [
            'mapperClass' => SelectedCandidatePermits::class,
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => UpdateCandidatePermitSelection::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_AWAITING_FEE,
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
