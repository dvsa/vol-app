<?php

/**
 * People Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * People Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends YourBusinessController
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
     * Save data
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
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
