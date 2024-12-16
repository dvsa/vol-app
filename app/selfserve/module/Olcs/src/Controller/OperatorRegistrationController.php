<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Mapper\CreateAccountMapper;
use Olcs\Form\Model\Form\RegisterOperatorAccount;

class OperatorRegistrationController extends AbstractController
{
    public function __construct(
        NiTextTranslation                     $niTextTranslationUtil,
        AuthorizationService                  $authService,
        protected FormHelperService           $formHelper,
        protected ScriptFactory               $scriptFactory,
        protected TranslationHelperService    $translationHelper,
        protected UrlHelperService            $urlHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected CreateAccountMapper         $formatDataMapper
    )
    {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    public function addAction(): Response|ViewModel
    {
        $form = $this->formHelper->createFormWithRequest(RegisterOperatorAccount::class, $this->getRequest());
        if ($this->getRequest()->isPost()) {
            $postData = $this->formatDataMapper->formatPostData($this->params()->fromPost());
            $form->setData($postData);
            if ($form->isValid()) {
                $formattedOperatorData = $this->formatDataMapper->formatSaveData($form->getData());
                $response = $this->handleCommand(
                    RegisterUserSelfserve::create($formattedOperatorData)
                );

                if ($response->isOk()) {
                    return $this->prepareView('olcs/user-registration/check-email', [
                        'emailAddress' => $formattedOperatorData['contactDetails']['emailAddress'],
                        'pageTitle' => 'user-registration.page.check-email.title'
                    ]);
                }

                $this->flashMessengerHelper->addErrorMessage('There was an error registering your account. Please try again.');
            }
        }
        return $this->prepareView('olcs/user-registration/index', [
            'form' => $this->alterForm($form),
            'pageTitle' => 'operator-registration.page.title'
        ]);
    }

    private function alterForm($form)
    {
        // inject link into terms agreed label
        $termsAgreed = $form->get('fields')->get('termsAgreed');

        $label = $this->translationHelper->translateReplace(
            $termsAgreed->getLabel(),
            [
                $this->urlHelper->fromRoute('terms-and-conditions')
            ]
        );

        $termsAgreed->setLabel($label);
        return $form;
    }

    private function prepareView(string $template, array $variables = []): ViewModel
    {
        $view = new ViewModel($variables);
        $view->setTemplate($template);

        if (isset($variables['pageTitle'])) {
            $this->placeholder()->setPlaceholder('pageTitle', $variables['pageTitle']);
        }

        return $view;
    }
}
