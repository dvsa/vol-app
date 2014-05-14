<?php

/**
 * BusinessType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

/**
 * BusinessType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends YourBusinessController
{
    /**
     * Render the section form
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }

    /**
     * Save data
     *
     * @param array $data
     */
    protected function save($data)
    {
        parent::save($data);
    }

    /**
     * Load data from id
     *
     * @param int $id
     */
    protected function load($id)
    {
        return array('data' => $this->getOrgnisationData(array('organisationType')));
    }
}
