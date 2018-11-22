<?php

namespace Olcs\Controller\Licence\Surrender;

class DeclarationController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $params = [
            'title' => '',
            'licNo' => $this->licence['licNo'],
            'content' => '',
            'backLink' => $this->getBackLink('lva-licence'),
        ];

        return $this->renderView($params);
    }
}
