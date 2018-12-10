<?php

namespace Olcs\Controller\Licence\Surrender;

class OperatorLicenceController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $form = $this->hlpForm->createForm(\Olcs\Form\Model\Form\Surrender\OperatorLicence::class);

        $params = [
            'title' => 'licence.surrender.operator_licence.title',
            'licNo' => $this->licence['licNo'],
            'backLink' => $this->getBackLink('lva-licence'),
            'form' =>  $form,
        ];

        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-operator']);

        return $this->renderView($params);
    }
}