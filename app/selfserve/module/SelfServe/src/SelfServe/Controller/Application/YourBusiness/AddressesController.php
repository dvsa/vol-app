<?php

/**
 * Addresses Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * Addresses Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressesController extends YourBusinessController
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

    /**
     * Placeholder save method
     *
     * @param array $data
     */
    protected function save($data)
    {
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    protected function load($id)
    {
        return array('data' => array());
    }
}
