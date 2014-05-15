<?php

/**
 * Placeholder Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TransportManagers;

/**
 * Placeholder Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PlaceholderController extends TransportManagersController
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
