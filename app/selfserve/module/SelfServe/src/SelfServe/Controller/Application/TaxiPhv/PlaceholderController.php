<?php

/**
 * Placeholder Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TaxiPhv;

/**
 * Placeholder Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PlaceholderController extends TaxiPhvController
{
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        $view = $this->getViewModel();
        $view->setTemplate('self-serve/journey/placeholder');
        return $this->renderSection($view);
    }
}
