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
     * Placeholder save method
     *
     * @param array $data
     * @parem string $service
     */
    protected function save($data, $service = null)
    {
    }
}
