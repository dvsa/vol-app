<?php

namespace Common\Controller\Continuation;

use Common\FormService\Form\Continuation\ConditionsUndertakings as ConditionsUndertakingsFormService;
use Common\FormService\FormServiceManager;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Conditions & undertakings controller controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 *
 */
class ConditionsUndertakingsController extends AbstractContinuationController
{
    public const NEXT_STEP = 'continuation/finances';

    protected $layout = 'pages/continuation-conditions-undertakings';

    protected $currentStep = self::STEP_CU;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FormServiceManager $formServiceManager,
        TranslationHelperService $translationHelper
    ) {
        parent::__construct($niTextTranslationUtil, $authService, $formServiceManager, $translationHelper);
    }

    /**
     * Index action
     *
     * @return ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $data = $this->getContinuationDetailData();
        $form = $this->getForm(ConditionsUndertakingsFormService::class, $data);

        if ($this->isPsvRestricted($data['licence'])) {
            $data = $this->addExtraConditionsUndertakings($data);
        } else {
            $form->remove('confirmation');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData((array)$request->getPost());
            if ($form->isValid()) {
                $this->redirect()->toRoute(self::NEXT_STEP, [], [], true);
            }
        }

        $params = [
            'backRoute' => 'continuation/checklist',
            'conditionsUndertakings' => $data['conditionsUndertakings'],
        ];
        $this->placeholder()->setPlaceholder('pageTitle', 'continuation.conditions-undertakings.page-title');
        return $this->getViewModel($data['licence']['licNo'], $form, $params);
    }

    protected function addExtraConditionsUndertakings($data): array
    {
        $data['conditionsUndertakings']['licence']['psv_restricted']['comment'] = $this->translationHelper->translate('markup-continuation-psv-restricted-comment');
        $data['conditionsUndertakings']['licence']['psv_restricted']['undertakings'] = $this->translationHelper->translate('markup-continuation-psv-restricted-required-undertaking');

        return $data;
    }
}
