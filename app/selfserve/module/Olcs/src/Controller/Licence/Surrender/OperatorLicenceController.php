<?php

namespace Olcs\Controller\Licence\Surrender;

class OperatorLicenceController extends AbstractSurrenderController
{
    public function indexAction()
    {

        $request = $this->getRequest();

        $form = $this->hlpForm->getServiceLocator()
            ->get('FormServiceManager')
            ->get(\Common\FormService\Form\Licence\Surrender\OperatorLicence::class)
            ->getForm("some data");

        if ($request->isPost()){
            $form->setData((array) $request->getPost());
            if($form->isValid()){
                echo "this is valid";
            }
        }

        $params = [
            'title' => 'licence.surrender.operator_licence.title',
            'licNo' => $this->licence['licNo'],
            'backLink' => $this->getBackLink('lva-licence'),
            'form' =>  $form,
        ];

        return $this->renderView($params);
    }
}