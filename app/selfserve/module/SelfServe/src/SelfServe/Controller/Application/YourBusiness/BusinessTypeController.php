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
     * Load data from id
     *
     * @param int $id
     */
    protected function load($id)
    {
        return array('data' => $this->getOrganisationData(array('organisationType')));
    }
}
