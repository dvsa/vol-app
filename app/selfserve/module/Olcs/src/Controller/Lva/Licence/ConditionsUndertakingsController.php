<?php

/**
 * External Licence Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Review\LicenceConditionsUndertakingsReviewService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Licence Conditions Undertakings Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsController extends Lva\AbstractController
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected string $location = 'external';
    protected $lvaAdapter;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param LicenceConditionsUndertakingsReviewService $licenceConditionsUndertakingsReviewSvc
     * @param GuidanceHelperService $guidanceHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected LicenceConditionsUndertakingsReviewService $licenceConditionsUndertakingsReviewSvc,
        protected GuidanceHelperService $guidanceHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    #[\Override]
    public function indexAction()
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Licence\ConditionUndertaking::create(['id' => $this->getIdentifier()])
        );
        if ($response->isForbidden()) {
            return $this->notFoundAction();
        }
        if (!$response->isOk()) {
            throw new \RuntimeException('Error get conditionUndertaking');
        }
        $data = $response->getResult();

        $config = $this->licenceConditionsUndertakingsReviewSvc
            ->getConfigFromData($data);

        $this->guidanceHelper->append('cannot-change-conditions-undertakings-guidance');

        $view = new ViewModel($config);
        $view->setTemplate('partials/read-only/subSections');

        $section = new ViewModel(['title' => 'section.name.conditions_undertakings']);
        $section->setTemplate('pages/licence-page');
        $section->addChild($view, 'content');

        return $this->renderView($section);
    }
}
