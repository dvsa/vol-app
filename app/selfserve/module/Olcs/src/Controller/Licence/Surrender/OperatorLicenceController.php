<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as SurrenderUpdate;
use Common\Data\Mapper\Licence\Surrender\OperatorLicence as Mapper;
use Zend\Http\Response;

class OperatorLicenceController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $request = $this->getRequest();

        $formService = $this->hlpForm->getServiceLocator()
            ->get('FormServiceManager')
            ->get(\Common\FormService\Form\Licence\Surrender\OperatorLicence::class);

        $form = $formService->getForm();

        if ($request->isPost()) {
            $formData = (array)$request->getPost();
            $form->setData($formData);
            if ($form->isValid()) {
                $this->saveFormDataAndUpdateSurrenderStatus($formData);
            }
        } elseif ($this->doesFormDataExist()) {
            $formData = Mapper::mapFromApi($this->getSurrender(), $form);
            $form->setData($formData);
            $formService->setStatus($form, $this->getSurrender());
        }

        $params = [
            'title' => 'licence.surrender.operator_licence.title',
            'licNo' => $this->licence['licNo'],
            'backLink' => $this->getBackLink('licence/surrender/current-discs/GET'),
            'form' => $form,
            'bottomText' => 'licence.surrender.operator_licence.return_to_current_discs.link',
            'bottomLink' => $this->getBackLink('licence/surrender/current-discs/GET'),
        ];

        return $this->renderView($params);
    }

    /**
     * Save form data and update surrender status
     *
     * @param array $formData
     *
     */
    private function saveFormDataAndUpdateSurrenderStatus($formData)
    {
        $data = Mapper::mapFromForm($formData);

        if ($this->updateSurrender(RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE, $data)) {
            $this->redirectAfterSave();
        }
        $this->addErrorMessage('unknown-error');
    }

    private function redirectAfterSave(): Response
    {
        $routeName = 'licence/surrender/review';
        if ($this->licence['licenceType']['id'] === RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            $routeName = 'licence/surrender/community-licence';
        }
        return $this->redirect()->toRoute($routeName, [], [], true);
    }

    private function doesFormDataExist()
    {
        return isset($this->getSurrender()["licenceDocumentStatus"]["id"]);
    }
}
