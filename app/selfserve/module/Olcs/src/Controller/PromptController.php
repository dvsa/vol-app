<?php

namespace Olcs\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Permits\Data\Mapper\MapperManager;

class PromptController extends AbstractSelfserveController
{
    protected $templateConfig = [
        'default' => 'pages/prompt',
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
     * @return \Laminas\Http\Response|null
     */
    #[\Override]
    public function checkConditionalDisplay()
    {
        if (!$this->currentUser()->getUserData()['eligibleForPrompt']) {
            return $this->conditionalDisplayNotMet('dashboard');
        }
    }
}
