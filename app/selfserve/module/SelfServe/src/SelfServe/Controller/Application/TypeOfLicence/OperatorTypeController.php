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
     * Process the form
     *
     * @param array $data
     */
    public function processForm($data)
    {
        return $this->goToNextStep();
    }
}
