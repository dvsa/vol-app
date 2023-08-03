<?php

/**
 * External Variation Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Variation;

use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\AbstractSummaryController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Variation Summary Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SummaryController extends AbstractSummaryController
{
    use VariationControllerTrait;

    protected $lva = 'variation';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     */
    public function __construct(NiTextTranslation $niTextTranslationUtil, AuthorizationService $authService)
    {
        parent::__construct(
            $niTextTranslationUtil,
            $authService
        );
    }
}
