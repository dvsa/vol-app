<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Common\Util\FlashMessengerTrait;
use Dvsa\Olcs\Transfer\Command\Surrender\SubmitForm;
use Zend\View\Model\ViewModel;

/**
 * Class PrintSignReturnController
 *
 * @package Olcs\Controller\Licence\Surrender
 */
class PrintSignReturnController extends AbstractSurrenderController
{

    use FlashMessengerTrait;

    protected $templateConfig = [
        'index' => 'licence/surrender-print-sign-return'
    ];

    public function indexAction()
    {
        return $this->createView();
    }


    public function printAction()
    {
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'isNi' => $this->data['surrender']['licence']['niFlag'] === 'Y',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'name' => $this->data['surrender']['licence']['organisation']['name'],
            'title' => $this->determineTitle(),
            'undertakings' => $translator->translateReplace(
                'markup-licence-surrender-declaration',
                [$this->data['surrender']['licence']['licNo']]
            )
        ];
        $view = new ViewModel($params);
        $view->setTemplate('licence/surrender-print-sign-return-form');

        $layout = new ViewModel();
        $layout->setTemplate('layouts/simple');
        $layout->setTerminal(true);
        $layout->addChild($view, 'content');

        $response = $this->handleCommand(SubmitForm::create(
            [
                "id" => $this->licenceId,
                "version"=>1
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
        if ($this->data['surrender']['licence']['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return "licence.surrender.print-sign-return.form.title.gv";
        }
        return "licence.surrender.print-sign-return.form.title.psv";
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {

        return
            [
                'pageTitle' => 'licence.surrender.print-sign-return.page.title',
                'returnLinkText' => 'return-home-button-text',
                'returnLink' => $this->getLink('dashboard'),
                'printLink' => $this->getLink('licence/surrender/print-sign-return-print/GET'),
            ];
    }
}
