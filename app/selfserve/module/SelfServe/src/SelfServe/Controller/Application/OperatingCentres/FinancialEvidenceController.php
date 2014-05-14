<?php

/**
 * FinancialEvidence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\OperatingCentres;

/**
 * FinancialEvidence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialEvidenceController extends OperatingCentresController
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
