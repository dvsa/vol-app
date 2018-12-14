<?php

namespace Olcs\Controller\Licence\Surrender;

use Dvsa\Olcs\Transfer\Command\Surrender\Update as SurrenderUpdate;

class OperatorLicenceController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $request = $this->getRequest();

        $form = $this->hlpForm->getServiceLocator()
            ->get('FormServiceManager')
            ->get(\Common\FormService\Form\Licence\Surrender\OperatorLicence::class)
            ->getForm();

        if($this->hasClickedCurrentDiscsLink()){
            // route needs changing to redirect to current discs page (OLCS-22255)
            $this->redirect()->toRoute('lva-licence',[],[],true);
        }

        if ($request->isPost()){
            $form->setData((array) $request->getPost());
            if($form->isValid()){

//                $dtoData =
//                    [
//                        'id' => $this->params('licence'),
//                    ];
//
//
//                $response = $this->handleCommand(SurrenderUpdate::create($dtoData));


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

    private function hasClickedCurrentDiscsLink(): bool
    {
        return $this->params()->fromPost()['currentDiscsLink'] !== null;
    }


}