<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\Surrender\SubmitForm;
use Zend\View\Model\ViewModel;

class PrintSignReturnController extends AbstractSurrenderController
{
    use FlashMessengerTrait;

    public function printAction()
    {
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'isNi' => $this->licence['niFlag'] === 'Y',
            'licNo' => $this->licence['licNo'],
            'name' => $this->licence['organisation']['name'],
            'title' => $this->determineTitle(),
            'undertakings' => $translator->translateReplace(
                'markup-licence-surrender-declaration',
                [$this->licence['licNo']]
            )
        ];
        $view = new ViewModel($params);
        $view->setTemplate('licence/surrender-print-sign-return');

        $layout = new ViewModel();
        $layout->setTemplate('layouts/simple');
        $layout->setTerminal(true);
        $layout->addChild($view, 'content');

        $response = $this->handleCommand(SubmitForm::create(
            [
                "id" => $this->licenceId
            ]
        ));
        if ($response->isOk()) {
            return $layout;
        }

        $this->flashMessenger()->addErrorMessage('licence.surrender.print-sign-return.form.error');
        return $this->redirect()->toRoute('licence/surrender/print-sign-return/GET', [], [], true);
    }


    protected function determineTitle()
    {
        if ($this->licence['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return "licence.surrender.print-sign-return.form.title.gv";
        }
        return "licence.surrender.print-sign-return.form.title.psv";
    }
}
