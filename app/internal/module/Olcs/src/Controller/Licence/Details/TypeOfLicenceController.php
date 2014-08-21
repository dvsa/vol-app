<?php

/**
 * TypeOfLicence Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * TypeOfLicence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceController extends AbstractLicenceDetailsController
{
    protected $section = 'type_of_licence';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
