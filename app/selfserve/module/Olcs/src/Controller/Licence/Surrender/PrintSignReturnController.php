<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Zend\View\Model\ViewModel;

class PrintSignReturnController extends AbstractSurrenderController
{
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

        if ($this->updateSurrender(RefData::SURRENDER_STATUS_SUBMITTED)) {
            return $layout;
        }
    }

    protected function determineTitle()
    {
        if ($this->licence['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return "licence.surrender.print-sign-return.form.title.gv";
        }
        return "licence.surrender.print-sign-return.form.title.psv";
    }
}
