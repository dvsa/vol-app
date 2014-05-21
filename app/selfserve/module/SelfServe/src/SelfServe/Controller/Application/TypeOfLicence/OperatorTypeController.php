<?php

/**
 * OperatorType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\TypeOfLicence;

/**
 * OperatorType Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatorTypeController extends TypeOfLicenceController
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
        return array('data' => $this->getLicenceData());
    }
}
