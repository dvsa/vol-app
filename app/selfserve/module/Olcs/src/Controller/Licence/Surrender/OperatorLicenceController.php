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

        if ($this->hasClickedCurrentDiscsLink()) {
            $this->redirect()->toRoute('current-discs', [], [], true);
        }

        if ($request->isPost()) {
            $formData = (array)$request->getPost();
            $form->setData($formData);
            if ($form->isValid()) {
                $this->saveFormDataAndUpdateSurrenderStatus($formData);
            }
        } else {
            $formData = Mapper::mapFromApi($this->getSurrender(), $form);
            $form->setData($formData);
            $formService->setStatus($form, $this->getSurrender());
        }

        $params = [
            'title' => 'licence.surrender.operator_licence.title',
            'licNo' => $this->licence['licNo'],
            // CHANGE ROUTE TO CURRENT DISCS
            'backLink' => $this->getBackLink('lva-licence'),
            'form' => $form,
        ];

        return $this->renderView($params);
    }

    private function hasClickedCurrentDiscsLink(): bool
    {
        return $this->params()->fromPost()['currentDiscsLink'] !== null;
    }


    /**
     * Save form data and update surrender status
     *
     * @param array $formData
     */
    private function saveFormDataAndUpdateSurrenderStatus($formData): void
    {
        $dtoData =
            [
                'id' => $this->params('licence'),
                'version' => $this->getSurrender()['version'],
            ] + Mapper::mapFromForm($formData);

        $response = $this->handleCommand(SurrenderUpdate::create($dtoData));

        if ($response->isOk()) {
            $this->handleCommand(SurrenderUpdate::create(['status' => RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE]));
            $this->redirectAfterSave();
        }
    }

    private function redirectAfterSave(): Response
    {
        $routeName = 'lva-licence'; // CHNAGE TO REVIEW YOUR DISCS AND DOCS ROUTE NAME
        if ($this->licence['licenceType']['id'] === RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL)
        {
            // CHNAGE TO COMMUNITY LICENCE PAGE ROUTE NAME
            $routeName = 'lva-licence';
        }
        return $this->redirect()->toRoute($routeName, [], [], true);
    }
}
