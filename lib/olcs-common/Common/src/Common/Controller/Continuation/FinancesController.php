<?php

namespace Common\Controller\Continuation;

use Common\Data\Mapper\Continuation\Finances;
use Common\Form\Form;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\UpdateFinances;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * FinancesController
 */
class FinancesController extends AbstractContinuationController
{
    protected $currentStep = self::STEP_FINANCE;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper,
        protected FormHelperService $formHelper,
        protected GuidanceHelperService $guidanceHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper);
    }

    /**
     * Index page
     */
    #[\Override]
    public function indexAction()
    {
        $continuationDetail = $this->getContinuationDetailData();

        $this->setGuidanceMessage($continuationDetail);

        $form = $this->getFinancesForm();

        $form->setData(Finances::mapFromResult($continuationDetail));

        if ($this->getRequest()->isPost()) {
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $dtoData = array_merge(
                    Finances::mapFromForm($form->getData()),
                    ['id' => $continuationDetail['id']]
                );
                $response = $this->handleCommand(UpdateFinances::create($dtoData));
                if ($response->isOk()) {
                    $totalFunds = (float)$dtoData['averageBalanceAmount']
                        + (float)$dtoData['overdraftAmount']
                        + (float)$dtoData['factoringAmount'];
                    if ($totalFunds >= (float)$continuationDetail['financeRequired']) {
                        return $this->redirect()->toRoute('continuation/declaration', [], [], true);
                    }

                    return $this->redirect()->toRoute('continuation/other-finances', [], [], true);
                }

                $this->addErrorMessage('unknown-error');
            }
        }

        $vars = [
            'backRoute' => 'continuation/checklist',
        ];
        return $this->getViewModel($continuationDetail['licence']['licNo'], $form, $vars);
    }

    /**
     * Get form
     *
     * @return Form
     */
    protected function getFinancesForm()
    {
        return $this->formHelper->createForm(
            \Common\Form\Model\Form\Continuation\Finances::class
        );
    }

    /**
     * Set the guidance message
     *
     * @param array $continuationDetail Continuation Detail data
     */
    private function setGuidanceMessage($continuationDetail): void
    {
        $financeRequired = number_format((int)$continuationDetail['financeRequired']);
        $guideMessage = $this->translationHelper->translateReplace('continuations.finances.hint', [$financeRequired]);
        $this->guidanceHelper->append($guideMessage);
    }
}
