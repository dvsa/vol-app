<?php

namespace Olcs\Controller\Licence\Surrender;

class DeclarationController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $params = [
            'title' => 'licence.surrender.declaration.title',
            'licNo' => $this->licence['licNo'],
            'content' => [
                'markup' => 'markup-licence-surrender-declaration',
                'data' => [$this->licence['licNo']]
            ],
            'backLink' => $this->getBackLink('lva-licence'),
        ];

        return $this->renderView($params);
    }
}
