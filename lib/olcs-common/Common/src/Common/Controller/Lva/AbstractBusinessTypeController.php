<?php

namespace Common\Controller\Lva;

use Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter;
use Common\Data\Mapper\Lva\BusinessType;
use Common\FormService\FormServiceManager;
use Common\Service\Cqrs\Query\QueryService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\Organisation\UpdateBusinessType;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Identity\IdentityProviderInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Shared logic between Business type controllers
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractBusinessTypeController extends AbstractController
{
    /**
     * @param GenericBusinessTypeAdapter $lvaAdapter
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected FlashMessengerHelperService $flashMessengerHelper,
        protected FormServiceManager $formServiceManager,
        protected ScriptFactory $scriptFactory,
        protected IdentityProviderInterface $identityProvider,
        protected TranslationHelperService $translationHelper,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected QueryService $queryService,
        protected GenericBusinessTypeAdapter $lvaAdapter
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Business type section
     */
    #[\Override]
    public function indexAction()
    {
        $orgId = $this->getCurrentOrganisationId();
        $response = $this->getBusinessType($orgId);

        if (!$response->isOk()) {
            if ($response->isClientError() || $response->isServerError()) {
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }

            return $this->notFoundAction();
        }

        $result = $response->getResult();

        $hasInForceLicences = $result['hasInforceLicences'];

        $hasOrganisationSubmittedLicenceApplication = $this->identityProvider->getIdentity()->getUserData()['hasOrganisationSubmittedLicenceApplication'] ?? false;

        /** @var \Laminas\Form\Form $form */
        $form = $this->formServiceManager
            ->get('lva-' . $this->lva . '-business_type')
            ->getForm($hasInForceLicences, $hasOrganisationSubmittedLicenceApplication);

        // If we haven't posted
        if ($this->getRequest()->isPost() === false) {
            $data = BusinessType::mapFromResult($result);

            $form->setData($data);

            return $this->render('business_type', $form);
        }

        $postData = (array)$this->getRequest()->getPost();
        if ($hasOrganisationSubmittedLicenceApplication) {
            $postData['data']['type'] = BusinessType::mapFromResult($result)['data']['type'];
        }

        // If this is set, then we have confirmed
        if (isset($postData['custom'])) {
            $dtoData = json_decode($postData['custom'], true);
            $dtoData['confirm'] = true;
        } else {
            $form->setData($postData);

            if (!$form->isValid()) {
                return $this->render('business_type', $form);
            }

            $data = $form->getData();

            $dtoData = [
                'id' => $orgId,
                'version' => $data['version'],
                $this->lva => $this->getIdentifier(),
                'confirm' => false
            ];

            if (isset($data['data']['type'])) {
                $dtoData['businessType'] = $data['data']['type'];
            }
        }

        $dto = UpdateBusinessType::create($dtoData);

        $response = $this->handleCommand(UpdateBusinessType::create($dtoData));

        if ($response->isOk()) {
            return $this->completeSection('business_type', $postData);
        }

        $messages = $response->getResult()['messages'];

        if (isset($messages['BUS_TYP_REQ_CONF'])) {
            $transitions = json_decode($messages['BUS_TYP_REQ_CONF']);

            $labels = [];

            foreach ($transitions as $transition) {
                $labels[] = $this->translationHelper->translate($transition);
            }

            $label = $this->translationHelper->translateReplace('BUS_TYP_REQ_CONF', [implode('', $labels)]);

            $view = $this->confirm($label, $this->getRequest()->isXmlHttpRequest(), json_encode($dto->getArrayCopy()));
            $view->setTerminal(false);

            $this->placeholder()->setPlaceholder('contentTitle', 'Business type change');
            return $this->viewBuilder()->buildView($view);
        }

        $this->flashMessengerHelper->addErrorMessage('unknown-error');

        // We may have a disabled business type element, so we need to fill in the values
        if (empty($data['data']['type'])) {
            $data['data']['type'] = $result['type']['id'];
            $form->setData($data);
        }

        return $this->render('business_type', $form);
    }

    public function getForm($form)
    {
        $formHelper = $this->formHelper;

        return $formHelper->createFormWithRequest($form, $this->getRequest());
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    private function getBusinessType($orgId)
    {
        $query = $this->transferAnnotationBuilder
            ->createQuery(Organisation::create(['id' => $orgId]));

        return $this->queryService->send($query);
    }
}
