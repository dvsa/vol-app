<?php


namespace Olcs\Controller;

use Olcs\Controller\Traits\LicenceControllerTrait;

class SurrenderController extends AbstractController
{

    use LicenceControllerTrait;

    public function indexAction()
    {
        $form = $this->getForm(\Olcs\Form\Model\Form\Licence\Surrender\Surrender::class);

        $view = $this->getViewWithLicence(['form' => $form]);

        $view->setTemplate('pages/form');

        return $this->renderView($view, 'surrender');
    }
}
