<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Data\Mapper\MapperManager;

class IrhpPermitsExhaustedController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'generic' => 'permits/exhausted'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.irhp-window-closed.browser.title',
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
