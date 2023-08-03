<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccountSelfserve as UpdateDto;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as MyAccountQuery;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel;
use ZfcRbac\Service\AuthorizationService;

/**
 * My Details Controller
 */
class MyDetailsController extends AbstractController
{
    protected FlashMessengerHelperService $flashMessengerHelper;
    protected ScriptFactory $scriptFactory;
    protected FormHelperService $formHelper;

    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        FlashMessengerHelperService $flashMessengerHelper,
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper
    ) {
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->scriptFactory = $scriptFactory;
        $this->formHelper = $formHelper;

        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Edit action
     *
     * @return \Laminas\View\Model\ViewModel|\Laminas\Http\Response
     */
    public function editAction()
    {
        $formHelper = $this->getFormHelper();

        /** @var Form $form */
        $form = $formHelper->createFormWithRequest('MyDetails', $this->getRequest());

        $response = $this->handleQuery(MyAccountQuery::create([]));

        if ($response->isOk()) {
            $data = $this->formatLoadData($response->getResult());
            $form->setData($data);
        } else {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        if ($this->getRequest()->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }

            $form->setData($this->params()->fromPost());

            if ($form->isValid()) {
                $data = $this->formatSaveData($form->getData());

                $response = $this->handleCommand(
                    UpdateDto::create($data)
                );

                if ($response->isOk()) {
                    $this->flashMessengerHelper
                        ->addSuccessMessage('generic.updated.success');
                    return $this->redirectToIndex();
                }
                $this->flashMessengerHelper->addErrorMessage('unknown-error');
            }
        }

        $this->lockNameFields($form);

        $view = new ViewModel(
            [
                'form' => $form,
                'showNav' => false
            ]
        );
        $view->setTemplate('pages/my-details-page');

        $this->scriptFactory->loadFile('my-details');

        return $view;
    }

    /**
     * Formats the data from what the service gives us, to what the form needs.
     *
     * @param array $data Data
     *
     * @return array
     */
    private function formatLoadData($data)
    {
        return [
            'main' => [
                'id' => $data['id'],
                'version' => $data['version'],
                'loginId' => $data['loginId'],
                'translateToWelsh' => $data['translateToWelsh'],
                'emailAddress' => $data['contactDetails']['emailAddress'],
                'emailConfirm' => $data['contactDetails']['emailAddress'],
                'familyName' => $data['contactDetails']['person']['familyName'],
                'forename' => $data['contactDetails']['person']['forename'],
            ]
        ];
    }

    /**
     * Formats the data from what's in the form to what the service needs.
     *
     * @param array $data Data
     *
     * @return array
     */
    private function formatSaveData($data)
    {
        return [
            'id' => $data['main']['id'],
            'version' => $data['main']['version'],
            'loginId' => $data['main']['loginId'],
            'translateToWelsh' => $data['main']['translateToWelsh'],
            'contactDetails' => [
                'emailAddress' => $data['main']['emailAddress']
            ]
        ];
    }

    /**
     * Redirects to index
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToIndex()
    {
        return $this->redirect()->toRoute('your-account', ['action' => 'edit'], array(), false);
    }

    /**
     * @return FormHelperService
     */
    private function getFormHelper()
    {
        /** @var FormHelperService $formHelper */
        $formHelper = $this->formHelper;
        return $formHelper;
    }

    /**
     * @param Form $form
     */
    private function lockNameFields(Form $form)
    {
        $fieldSet = $form->get('main');

        $this->getFormHelper()->lockElement($fieldSet->get('forename'), 'name-change.locked.tooltip.message');
        $this->getFormHelper()->lockElement($fieldSet->get('familyName'), 'name-change.locked.tooltip.message');
    }
}
