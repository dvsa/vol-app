<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\RefData;
use Zend\View\Model\ViewModel;

class DeclarationFormController extends AbstractSurrenderController
{
    public function indexAction()
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
        $view->setTemplate('licence/declarations');

        $layout = new ViewModel();
        $layout->setTemplate('layouts/simple');
        $layout->setTerminal(true);
        $layout->addChild($view, 'content');

        return $layout;
    }

    protected function determineTitle()
    {
        if ($this->licence['goodsOrPsv']['id'] === RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return "licence.surrender.declaration.form.title.gv";
        }
        return "licence.surrender.declaration.form.title.psv";
    }
}
