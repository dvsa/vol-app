<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\EnabledSectionTrait;
use Common\FeatureToggle;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Application\UpdateDeclaration;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * External Abstract Undertakings Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractUndertakingsController extends AbstractController
{
    use EnabledSectionTrait;

    protected string $location = 'external';

    protected $data = [];

    protected ScriptFactory $scriptFactory;
    protected AnnotationBuilder $transferAnnotationBuilder;
    protected CommandService $commandService;
    protected FlashMessengerHelperService $flashMessengerHelper;
    protected FormHelperService $formHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param ScriptFactory $scriptFactory
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        ScriptFactory $scriptFactory,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        FlashMessengerHelperService $flashMessengerHelper,
        FormHelperService $formHelper
    ) {
        $this->scriptFactory = $scriptFactory;
        $this->transferAnnotationBuilder = $transferAnnotationBuilder;
        $this->commandService = $commandService;
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->formHelper = $formHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action
     *
     * @return \Common\View\Model\Section|\Laminas\Http\Response
     */
    public function indexAction()
    {
        if ($this->isButtonPressed('change')) {
            return $this->goToOverview();
        }

        $request = $this->getRequest();
        $applicationData = $this->getUndertakingsData();
        $form = $this->updateForm($this->getForm(), $applicationData);

        $files = ['undertakings-interim'];
        if ($this->lva === 'application' && !$this->data['disableSignatures']) {
            $files[] = 'undertakings-verify';
        }
        $this->scriptFactory->loadFiles($files);

        $hasGovUkAccountError = $this->getFlashMessenger()->getContainer()->offsetExists('govUkAccountError');
        if ($hasGovUkAccountError) {
            $form->setMessages([
                'declarationsAndUndertakings' => [
                    'signatureOptions' => ['undertakings-sign-declaration-again']
                ],
            ]);
            $form->setOption('formErrorsParagraph', 'undertakings-govuk-account-generic-error');
        }

        if ($request->isPost()) {
            $data = (array) $request->getPost();
            $form->setData($data);
            if ($form->isValid()) {
                if ($this->isButtonPressed('submitAndPay') || $this->isButtonPressed('submit')) {
                    $shouldCompleteSection = true;
                } else {
                    $shouldCompleteSection = false;
                }

                $response = $this->save($form->getData(), $shouldCompleteSection);
                if ($response->isOk()) {
                    $this->completeSection('undertakings');
                    return $this->goToNextStep($applicationData['id']);
                }
            } else {
                // validation failed, we need to use the application data
                // for markup but use the POSTed values to render the form again
                $formData = array_replace_recursive(
                    $this->formatDataForForm($applicationData),
                    $data
                );
                // don't call setData again here or we lose validation messages
                $form->populateValues($formData);
            }
        } else {
            $data = $this->formatDataForForm($applicationData);
            $form->setData($data);
        }

        return $this->render('undertakings', $form);
    }

    /**
     * Save the form data
     *
     * @param array $formData              form data
     * @param bool  $shouldCompleteSection should complete section
     *
     * @return \Common\Service\Cqrs\Response
     */
    protected function save($formData, $shouldCompleteSection = false)
    {
        $dto = $this->createUpdateDeclarationDto($formData, $shouldCompleteSection);

        $command = $this->transferAnnotationBuilder->createCommand($dto);

        /* @var $response \Common\Service\Cqrs\Response */
        $response = $this->commandService->send($command);

        if (!$response->isOk()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        return $response;
    }

    /**
     * Go to the next step
     *
     * @return \Laminas\Http\Response
     */
    protected function goToNextStep($appId)
    {
        if ($this->isButtonPressed('submitAndPay') || $this->isButtonPressed('submit')) {
            // section completed
            return $this->redirect()->toRoute(
                'lva-' . $this->lva . '/pay-and-submit',
                [$this->getIdentifierIndex() => $this->getIdentifier(), 'redirect-back' => 'undertakings'],
                [],
                true
            );
        } elseif ($this->isButtonPressed('sign')) {
            $featureEnabled = $this->handleQuery(IsEnabledQry::create(['ids' => [FeatureToggle::GOVUK_ACCOUNT]]))->getResult()['isEnabled'];
            if (!$featureEnabled) {
                return $this->redirect()->toRoute(
                    'verify/initiate-request',
                    [$this->getIdentifierIndex() => $this->getIdentifier()]
                );
            }

            $returnUrl = $this->url()->fromRoute(
                'lva-application/undertakings',
                [
                    'application' => $appId,
                    'action' => 'index'
                ],
                [],
                true
            );

            $urlResult = $this->handleCommand(GetGovUkAccountRedirect::create([
                'journey' => RefData::JOURNEY_NEW_APPLICATION,
                'id' => $appId,
                'role' => RefData::TMA_SIGN_AS_OP,
                'returnUrl' => $returnUrl,
            ]));
            if (!$urlResult->isOk()) {
                throw new \Exception('GetGovUkAccountRedirect command returned non-OK', $urlResult->getStatusCode());
            }
            return $this->redirect()->toUrl($urlResult->getResult()['messages'][0]);
        }
    }

    /**
     * Create update declaration dto
     *
     * @param array $formData              form data
     * @param bool  $shouldCompleteSection should complete section
     *
     * @return UpdateDeclaration
     */
    protected function createUpdateDeclarationDto($formData, $shouldCompleteSection = true)
    {
        $signatureType = null;
        if ($this->lva === 'variation') {
            $signatureType = RefData::SIGNATURE_TYPE_NOT_REQUIRED;
        } elseif ($shouldCompleteSection) {
            $signatureType = RefData::SIGNATURE_TYPE_PHYSICAL_SIGNATURE;
        }

        $data = [
            'id' => $this->getIdentifier(),
            'version' => $formData['declarationsAndUndertakings']['version'],
            'declarationConfirmation' => $shouldCompleteSection ? 'Y' : 'N',
            'interimRequested' => isset($formData['interim']) ?
                $formData['interim']['goodsApplicationInterim'] : null,
            'interimReason' => isset($formData['interim']) ?
                $formData['interim']['YContent']['goodsApplicationInterimReason'] : null
        ];
        if ($signatureType) {
            $data['signatureType'] = $signatureType;
        }
        $dto = UpdateDeclaration::create($data);

        return $dto;
    }

    /**
     * Get undertakings data
     *
     * @return array|false
     */
    protected function getUndertakingsData()
    {
        $query = \Dvsa\Olcs\Transfer\Query\Application\Declaration::create(['id' => $this->getIdentifier()]);

        $response =  $this->handleQuery($query);

        if ($response->isOk()) {
            $result = $response->getResult();
            $this->data = $result;
            return $result;
        }

        $this->flashMessengerHelper->addErrorMessage('unknown-error');

        return false;
    }

    /**
     * Format data for form
     *
     * @param array $applicationData application data
     *
     * @return array
     */
    protected function formatDataForForm($applicationData)
    {
        $goodsOrPsv  = $applicationData['goodsOrPsv']['id'];

        $output = array(
            'declarationsAndUndertakings' => array(
                'version' => $applicationData['version'],
                'id' => $applicationData['id'],
            )
        );

        if ($goodsOrPsv === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            $interim = array();
            if (!is_null($applicationData['interimReason'])) {
                $interim['goodsApplicationInterim'] = "Y";
                $interim['YContent']['goodsApplicationInterimReason'] = $applicationData['interimReason'];
            }

            $output['interim'] = $interim;
        }

        return $output;
    }

    /**
     * Update submit buttons
     *
     * @param Form  $form            form
     * @param array $applicationData application data
     *
     * @return void
     */
    protected function updateSubmitButtons($form, $applicationData)
    {
        $formHelper = $this->formHelper;

        if (!$this->isReadyToSubmit($applicationData)) {
            $formHelper->remove($form, 'form-actions->submitAndPay');
            $formHelper->remove($form, 'form-actions->submit');
            $formHelper->remove($form, 'form-actions->change');
            return;
        }

        if ($this->lva === 'application') {
            $formHelper->remove($form, 'form-actions->saveAndContinue');
        }
        $formHelper->remove($form, 'form-actions->save');
        $formHelper->remove($form, 'form-actions->cancel');

        if (floatval($applicationData['outstandingFeeTotal']) > 0) {
            $formHelper->remove($form, 'form-actions->submit');
        } else {
            $formHelper->remove($form, 'form-actions->submitAndPay');
        }
    }
}
