<?php

namespace Common\Controller\Lva;

use Dvsa\Olcs\Transfer\Query\Application\Review;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Review Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ReviewController extends AbstractController
{
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Review application action
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $response = $this->handleQuery(Review::create(['id' => $this->params('application')]));
        if ($response->isForbidden()) {
            return $this->notFoundAction();
        }

        $data = $response->getResult();

        $view = new ViewModel(['content' => $data['markup']]);

        $view->setTerminal(true);
        $view->setTemplate('layout/blank');

        return $view;
    }
}
