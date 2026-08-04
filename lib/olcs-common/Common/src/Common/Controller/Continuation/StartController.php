<?php

namespace Common\Controller\Continuation;

use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * StartController
 */
class StartController extends AbstractContinuationController
{
    public const BACK_ROUTE = 'lva-licence';

    /** @var string  */
    protected $layout = 'pages/continuation-start';

    protected $currentStep = self::STEP_START;

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
        $data = $this->getContinuationDetailData(
            $this->getContinuationDetailId()
        );
        $licenceData = $data['licence'];

        $form = $this->getForm('continuations-start');

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $this->redirect()->toRoute('continuation/checklist', [], [], true);
            }
        }

        return $this->getViewModel(
            $licenceData['licNo'],
            $form,
            ['backRoute' => self::BACK_ROUTE, 'backRouteParams' => ['licence' => $licenceData['id']]]
        );
    }
}
