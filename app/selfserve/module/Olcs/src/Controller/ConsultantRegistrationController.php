<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\ExistsWithOperatorAdmin;
use Dvsa\Olcs\Transfer\Command\User\RegisterConsultantAndOperator;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Http\Response;
use Laminas\View\Model\ViewModel;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Mapper\CreateAccountMapper;
use Olcs\Form\Model\Form\ExistingOperatorLicence;
use Olcs\Form\Model\Form\OperatorRepresentation;
use Olcs\Form\Model\Form\RegisterConsultantAccount;
use Olcs\Form\Model\Form\RegisterForOperator;
use Olcs\Session\ConsultantRegistration;

/**
 * Consultant Registration Controller
 */
class ConsultantRegistrationController extends AbstractController
{
    public function __construct(
        NiTextTranslation                     $niTextTranslationUtil,
        AuthorizationService                  $authService,
        protected FormHelperService           $formHelper,
        protected ScriptFactory               $scriptFactory,
        protected TranslationHelperService    $translationHelper,
        protected UrlHelperService            $urlHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected ConsultantRegistration      $consultantRegistrationSession,
        protected CreateAccountMapper         $formatDataMapper
    )
    {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Transport consultant journey
     *
     * @return ViewModel|\Laminas\Http\Response|null
     */
    public function addAction()
    {
        $form = $this->formHelper->createFormWithRequest(ExistingOperatorLicence::class, $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $postData = $this->formatDataMapper->formatPostData($this->params()->fromPost());
            $form->setData($postData);

            if ($form->isValid()) {
                $formData = $form->getData();
                if (($formData['fields']['existingOperatorLicence'] ?? null) === 'Y') {
                    $licenceNumber = $formData['fields']['licenceContent']['licenceNumber'];
                    $checks = $this->licenseHasAdmin($licenceNumber);

                    if (!$checks['licenceExists'] ?? false) {
                        $form->setMessages(['fields' => ['licenceContent'=>['licenceNumber' => ['record-not-found']]]]);
                    } elseif (!$checks['hasOperatorAdmin'] ?? false) {
                        $this->redirect()->toRoute('user-registration/operator');
                    } else {
                        $this->redirect()->toRoute('user-registration/contact-your-administrator');
                    }

                } elseif (($formData['fields']['existingOperatorLicence'] ?? null) === 'N') {
                    $this->redirect()->toRoute('user-registration/operator-representation');
                }
            }
        }

        return $this->prepareView('olcs/user-registration/operator-registration', [
            'form' => $form,
            'pageTitle' => 'user-registration.page.title'

        ]);
    }

    private function licenseHasAdmin(string $licenceNumber): array
    {
        $response = $this->handleQuery(ExistsWithOperatorAdmin::create(['licNo' => $licenceNumber]));
        if ($response->isOk()) {
           return $response->getResult();
        }
        return [];
    }

    /**
     * @return Response|ViewModel
     */
    public function operatorRepresentationAction()
    {
        $form = $this->formHelper->createFormWithRequest(OperatorRepresentation::class, $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $postData = $this->formatDataMapper->formatPostData($this->params()->fromPost());
            $form->setData($postData);

            if ($form->isValid()) {
                if ($postData['fields']['actingOnOperatorsBehalf'] == 'Y') {
                    // Move on to capture details for an operator, then consultant account
                    return $this->redirect()->toRoute('user-registration/register-for-operator');
                } else {
                    // Show the original operator registration form
                    return $this->redirect()->toRoute('user-registration/operator');
                }
            }
        }

        return $this->prepareView('olcs/user-registration/index', [
            'form' => $form,
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function contactYourAdministratorAction()
    {
        return $this->prepareView('olcs/user-registration/contact-your-administrator');
    }


    /**
     * @return Response|ViewModel
     */
    public function registerForOperatorAction()
    {
        $form = $this->formHelper->createFormWithRequest(RegisterForOperator::class, $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $postData = $this->formatDataMapper->formatPostData($this->params()->fromPost());
            $form->setData($postData);

            if ($form->isValid()) {
                // Save the operator details in session container and move on to consultant account registration
                $this->consultantRegistrationSession->setOperatorDetails($form->getData());
                return $this->redirect()->toRoute('user-registration/register-consultant-account');
            }
        }

        return $this->prepareView('olcs/user-registration/index', [
            'form' => $form,
            'pageTitle' => 'register-for-operator.form.label'
        ]);
    }

    /**
     * @return Response|ViewModel
     */
    public function registerConsultantAccountAction()
    {
        $form = $this->formHelper->createFormWithRequest(RegisterConsultantAccount::class, $this->getRequest());

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToHome();
            }

            $form->setData($this->formatDataMapper->formatPostData($this->params()->fromPost()));

            if ($form->isValid()) {
                $result = $this->registerConsultantAndOperator($form->getData());
                if ($result === null) {
                    return $this->prepareView('olcs/user-registration/check-email-consultant', [
                        'consultantEmailAddress' => $form->get('fields')->get('emailAddress')->getValue(),
                        'operatorEmailAddress' => $this->consultantRegistrationSession->getOperatorDetails()['fields']['emailAddress'],
                        'pageTitle' => 'user-registration.page.check-email.title'
                    ]);
                }
                return $result;
            }
        }

        return $this->prepareView('olcs/user-registration/index', [
            'form' => $this->alterForm($form),
            'pageTitle' => 'register-consultant-account.form.label'
        ]);
    }

    private function registerConsultantAndOperator($consultantFormData)
    {
        $operatorData = $this->consultantRegistrationSession->getOperatorDetails();
        $formattedOperatorData = $this->formatDataMapper->formatSaveData($operatorData);
        $formattedConsultantData = $this->formatDataMapper->formatSaveData($consultantFormData);

        $response = $this->handleCommand(
            RegisterConsultantAndOperator::create(
                [
                    'operatorDetails' => $formattedOperatorData,
                    'consultantDetails' => $formattedConsultantData,
                ]
            )
        );

        if ($response->isOk()) {
            return null;
        }

        $this->flashMessengerHelper->addErrorMessage('unknown-error');

        $this->redirect()->toRoute('user-registration');
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

    /**
     * @param string $template
     * @param array $variables
     * @return ViewModel
     */
    private function prepareView(string $template, array $variables = []): ViewModel
    {
        $view = new ViewModel($variables);
        $view->setTemplate($template);

        if (isset($variables['pageTitle'])) {
            $this->placeholder()->setPlaceholder('pageTitle', $variables['pageTitle']);
        }

        return $view;
    }

    /**
     * Redirects to home
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToHome()
    {
        return $this->redirect()->toRoute('index');
    }
}
