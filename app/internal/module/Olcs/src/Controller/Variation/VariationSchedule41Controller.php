<?php

namespace Olcs\Controller\Variation;

use Common\Controller\Lva\Schedule41Controller;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

class VariationSchedule41Controller extends Schedule41Controller implements ApplicationControllerInterface
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'internal';

    protected $section = 'operating_centres';

    protected StringHelperService $stringHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        FlashMessengerHelperService $flashMessengerHelper,
        StringHelperService $stringHelper,
        protected $navigation
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formHelper, $tableFactory, $flashMessengerHelper);
        $this->stringHelper = $stringHelper;
    }
}
