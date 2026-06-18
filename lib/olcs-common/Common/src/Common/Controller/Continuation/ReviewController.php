<?php

namespace Common\Controller\Continuation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\Review as ReviewQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Review controller
 */
class ReviewController extends AbstractContinuationController
{
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper);
    }

    /**
     * Index page
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $reviewData = $this->getReviewData($this->getContinuationDetailId());
        $view = new ViewModel(['content' => $reviewData]);

        $view->setTerminal(true);
        $view->setTemplate('layout/blank');
        return $view;
    }

    /**
     * Get review data
     *
     * @param int $continuationDetailId continuation detail id
     *
     * @return array
     */
    protected function getReviewData($continuationDetailId)
    {
        $dto = ReviewQuery::create(['id' => $continuationDetailId]);
        $response = $this->handleQuery($dto);
        if (!$response->isOk()) {
            $this->addErrorMessage('unknown-error');
        }

        return $response->getResult()['markup'];
    }
}
