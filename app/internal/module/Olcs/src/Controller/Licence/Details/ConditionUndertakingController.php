<?php

/**
 * ConditionUndertaking Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * ConditionUndertaking Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionUndertakingController extends AbstractLicenceDetailsController
{
    protected $section = 'condition_undertaking';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
