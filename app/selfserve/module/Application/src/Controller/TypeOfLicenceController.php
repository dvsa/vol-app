<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\Application\AbstractTypeOfLicenceController;
use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\RestrictionHelperService;
use Common\Service\Helper\StringHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\View\Model\Section;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Form;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use ZfcRbac\Service\AuthorizationService;

/**
 * External Type Of Licence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractTypeOfLicenceController
{
    use ApplicationControllerTrait;

    protected string $location = 'external';
    protected $lva = 'application';
    protected AnnotationBuilder $transferAnnotationBuilder;
    protected CommandService $commandService;
    protected TranslationHelperService $translatorHelper;
    protected RestrictionHelperService $restrictionHelper;
    protected StringHelperService $stringHelper;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FlashMessengerHelperService $flashMessengerHelper
     * @param ScriptFactory $scriptFactory
     * @param FormServiceManager $formServiceManager
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     * @param TranslationHelperService $translatorHelper
     * @param RestrictionHelperService $restrictionHelper
     * @param StringHelperService $stringHelper
     * @param FormHelperService $formHelper
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        FormServiceManager $formServiceManager,
        AnnotationBuilder $transferAnnotationBuilder,
        CommandService $commandService,
        TranslationHelperService $translatorHelper,
        RestrictionHelperService $restrictionHelper,
        StringHelperService $stringHelper,
        FormHelperService $formHelper
    ) {
        $this->transferAnnotationBuilder = $transferAnnotationBuilder;
        $this->commandService = $commandService;
        $this->translatorHelper = $translatorHelper;
        $this->restrictionHelper = $restrictionHelper;
        $this->stringHelper = $stringHelper;

        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $flashMessengerHelper,
            $scriptFactory,
            $formServiceManager,
            $formHelper
        );
    }

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Laminas\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function renderCreateApplication($titleSuffix, Form $form = null)
    {
        return new Section(
            [
                'title' => 'lva.section.title.' . $titleSuffix, 'form' => $form,
                'stepX' => '1',
                // don't display any additional information like progress, app number, etc on create app page
                'lva' => ''
            ]
        );
    }

    /**
     * Create application action
     */
    public function createApplicationAction()
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirect()->toRouteAjax('dashboard');
        }

        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var \Common\FormService\Form\Lva\TypeOfLicence\ApplicationTypeOfLicence $tolFormManagerService */
        $tolFormManagerService = $this->formServiceManager
            ->get('lva-application-type-of-licence');
        /** @var \Common\Form\Form $form */
        $form = $tolFormManagerService->getForm();

        $organisationData = $this->getOrganisation($this->getCurrentOrganisationId());
        if (isset($organisationData['allowedOperatorLocation'])) {
            $tolFormManagerService->setAndLockOperatorLocation($form, $organisationData['allowedOperatorLocation']);
        }

        if ($request->isPost()) {
            $data = (array)$request->getPost();

            $form->setData($data);

            $tolFormManagerService->maybeAlterFormForNi($form);
            $tolFormManagerService->maybeAlterFormForGoodsStandardInternational($form);

            if ($form->isValid()) {
                $data = $form->getData();

                $typeOfLicenceData = $data['type-of-licence'];
                $licenceTypeData = $typeOfLicenceData['licence-type'];
                $operatorType = $typeOfLicenceData['operator-type'];
                $licenceType = $licenceTypeData['licence-type'];
                $vehicleType = null;
                $lgvDeclarationConfirmation = 0;
                $licenceTypeRestrictedGuidance = '';

                if ($licenceType == RefData::LICENCE_TYPE_RESTRICTED && isset($licenceTypeData['ltyp_rContent'])) {
                    $licenceTypeRestrictedGuidance = $licenceTypeData['ltyp_rContent'];
                }

                if (isset($licenceTypeData['ltyp_siContent'])) {
                    $siContentData = $licenceTypeData['ltyp_siContent'];
                    $vehicleType = $siContentData['vehicle-type'];

                    if (isset($siContentData['lgv-declaration']['lgv-declaration-confirmation'])) {
                        $lgvDeclarationConfirmation = $siContentData['lgv-declaration']['lgv-declaration-confirmation'];
                    }
                }

                $dto = CreateApplication::create(
                    [
                        'organisation' => $this->getCurrentOrganisationId(),
                        'niFlag' => $this->getOperatorLocation($organisationData, $data),
                        'operatorType' => $operatorType,
                        'licenceType' => $licenceType,
                        'vehicleType' => $vehicleType,
                        'lgvDeclarationConfirmation' => $lgvDeclarationConfirmation,
                        'licenceTypeRestrictedGuidance' => $licenceTypeRestrictedGuidance,
                    ]
                );

                $command = $this->transferAnnotationBuilder->createCommand($dto);

                /** @var \Common\Service\Cqrs\Response $response */
                $response = $this->commandService->send($command);

                if ($response->isOk()) {
                    return $this->goToOverview($response->getResult()['id']['application']);
                }

                if ($response->isClientError()) {
                    $this->mapErrors($form, $response->getResult()['messages']);
                } else {
                    $this->flashMessengerHelper->addErrorMessage('unknown-error');
                }
            }
        }

        $this->scriptFactory->loadFile('type-of-licence');

        return $this->renderCreateApplication('type_of_licence', $form);
    }

    /**
     * Get Organisation data
     *
     * @param int $id
     *
     * @return array
     * @throws \RuntimeException
     */
    protected function getOrganisation($id)
    {
        $response = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Organisation\Organisation::create(['id' => $id])
        );
        if (!$response->isOk()) {
            throw new \RuntimeException(
                $this->translatorHelper->translate('external.error-getting-organisation')
            );
        }
        return $response->getResult();
    }
}
