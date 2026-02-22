<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateDeclaration;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationDeclarationController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_DECLARATION
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_MAKE_DECLARATION,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_IRHP_DECLARATION,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'browserTitle' => 'permits.page.declaration.browser.title',
            'question' => 'permits.page.declaration.question',
            'bulletList' => [
                'title' => 'permits.page.declaration.bullet.list.title',
                'type' => 'medium',
            ],
            'additionalWarning' => 'permits.page.declaration.additional.warning',
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
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

    protected $postConfig = [
        'default' => [
            'command' => UpdateDeclaration::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_FEE,
            'saveAndReturnStep' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'conditional' => [
                'dataKey' => 'application',
                'params' => 'id',
                'step' => [
                    'command' => SubmitApplication::class,
                    'route' => IrhpApplicationSection::ROUTE_SUBMITTED,
                ],
                'saveAndReturnStep' => [
                    'route' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
                ],
                'field' => 'hasOutstandingFees',
                'value' => false
            ]
        ],
    ];

    /**
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        // declaration text is permit type specific
        $irhpPermitTypeId = $this->data[IrhpAppDataSource::DATA_KEY]['irhpPermitType']['id'];

        $this->templateVarsConfig['question']['bulletList']['list'] = 'markup-irhp-declaration-' . $irhpPermitTypeId;

        parent::mergeTemplateVars();
    }
}
